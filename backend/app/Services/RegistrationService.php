<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        private readonly TicketService $tickets,
        private readonly PaymentService $payments,
    ) {}

    /**
     * Create a reservation (paid event) or an immediate confirmation (free event).
     *
     * @return array{registration: Registration, created: bool, ticket_token: string|null}
     */
    public function register(User $user, Event $event): array
    {
        return DB::transaction(function () use ($user, $event) {
            /** @var Event $lockedEvent */
            $lockedEvent = Event::query()
                ->whereKey($event->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertEventAcceptsRegistration($lockedEvent);

            /** @var Registration|null $registration */
            $registration = Registration::query()
                ->where('user_id', $user->id)
                ->where('event_id', $lockedEvent->id)
                ->first();

            if ($registration?->status === RegistrationStatus::CONFIRMED) {
                abort(409, 'Anda sudah terdaftar pada event ini.');
            }

            if ($registration !== null && $registration->isReservationExpired()) {
                $registration->update(['status' => RegistrationStatus::EXPIRED]);
                $registration->payment()->update(['status' => PaymentStatus::FAILED]);
                $this->releaseReservedQuota($lockedEvent);
                $registration->refresh();
            }

            if ($registration?->status === RegistrationStatus::PENDING) {
                if (! $lockedEvent->isFree()) {
                    $this->payments->ensureExists($registration, $lockedEvent);
                }

                return [
                    'registration' => $registration->fresh(['event.organization', 'payment', 'ticket']),
                    'created' => false,
                    'ticket_token' => null,
                ];
            }

            if ($lockedEvent->availableQuota() <= 0) {
                abort(409, 'Kuota tiket sudah habis.');
            }

            $created = $registration === null;

            if ($registration === null) {
                $registration = new Registration([
                    'user_id' => $user->id,
                    'event_id' => $lockedEvent->id,
                ]);
            }

            $ticketToken = null;

            if ($lockedEvent->isFree()) {
                $registration->fill([
                    'status' => RegistrationStatus::CONFIRMED,
                    'expires_at' => null,
                ])->save();

                $lockedEvent->increment('sold_count');

                [, $ticketToken] = $this->tickets->issue($registration);
            } else {
                $registration->fill([
                    'status' => RegistrationStatus::PENDING,
                    'expires_at' => now()->addMinutes(Registration::RESERVATION_MINUTES),
                ])->save();

                $lockedEvent->increment('reserved_count');
                $this->payments->resetToPending($registration, $lockedEvent);
            }

            return [
                'registration' => $registration->fresh(['event.organization', 'payment', 'ticket']),
                'created' => $created,
                'ticket_token' => $ticketToken,
            ];
        }, 3);
    }

    /**
     * Release quota for an expired reservation exactly once.
     */
    public function expire(Registration $registration): bool
    {
        return DB::transaction(function () use ($registration) {
            /** @var Registration|null $locked */
            $locked = Registration::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null || ! $locked->isReservationExpired()) {
                return false;
            }

            /** @var Event $event */
            $event = Event::query()
                ->whereKey($locked->event_id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked->update(['status' => RegistrationStatus::EXPIRED]);
            $this->releaseReservedQuota($event);

            return true;
        }, 3);
    }

    /**
     * Administrative cancellation. Safe to call repeatedly.
     */
    public function cancel(Registration $registration): Registration
    {
        return DB::transaction(function () use ($registration) {
            /** @var Registration $locked */
            $locked = Registration::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($locked->status, [RegistrationStatus::EXPIRED, RegistrationStatus::CANCELLED], true)) {
                return $locked->fresh(['event.organization', 'payment', 'ticket']);
            }

            /** @var Event $event */
            $event = Event::query()
                ->whereKey($locked->event_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === RegistrationStatus::PENDING) {
                $locked->update(['status' => RegistrationStatus::CANCELLED]);
                $this->releaseReservedQuota($event);
            } else {
                $locked->update(['status' => RegistrationStatus::CANCELLED]);

                if ($event->sold_count > 0) {
                    $event->decrement('sold_count');
                }

                if ($locked->ticket()->exists()) {
                    $locked->ticket()->update(['status' => TicketStatus::VOID]);
                }
            }

            return $locked->fresh(['event.organization', 'payment', 'ticket']);
        }, 3);
    }

    private function assertEventAcceptsRegistration(Event $event): void
    {
        if ($event->status !== EventStatus::PUBLISHED) {
            abort(422, 'Event tidak tersedia untuk pendaftaran.');
        }

        $now = now();

        if ($now->lt($event->registration_start)) {
            abort(422, 'Pendaftaran belum dibuka.');
        }

        if ($now->gt($event->registration_end)) {
            abort(422, 'Pendaftaran sudah ditutup.');
        }
    }

    private function releaseReservedQuota(Event $event): void
    {
        if ($event->reserved_count > 0) {
            $event->decrement('reserved_count');
        }
    }
}
