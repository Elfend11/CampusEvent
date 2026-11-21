<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    public function __construct(private readonly TicketService $tickets) {}

    /**
     * Validate a ticket token and record attendance exactly once.
     *
     * @return array{ticket: Ticket|null, error: string|null, status: int}
     */
    public function checkIn(User $officer, string $token, ?int $eventId = null): array
    {
        return DB::transaction(function () use ($officer, $token, $eventId) {
            /** @var Ticket|null $ticket */
            $ticket = Ticket::query()
                ->where('token_hash', $this->tickets->hashToken($token))
                ->lockForUpdate()
                ->first();

            if ($ticket === null) {
                return ['ticket' => null, 'error' => 'Tiket tidak ditemukan.', 'status' => 404];
            }

            $ticket->load(['registration.event', 'registration.user', 'attendance.officer']);

            $event = $ticket->registration->event;

            if ($eventId !== null && $event->id !== $eventId) {
                return [
                    'ticket' => $ticket,
                    'error' => 'Tiket bukan untuk event ini.',
                    'status' => 422,
                ];
            }

            $isAssigned = $event->eventOfficers()
                ->where('user_id', $officer->id)
                ->exists();

            if (! $isAssigned && ! $officer->hasRole(UserRole::SUPER_ADMIN)) {
                return [
                    'ticket' => $ticket,
                    'error' => 'Anda tidak ditugaskan pada event ini.',
                    'status' => 403,
                ];
            }

            if ($ticket->status === TicketStatus::USED || $ticket->attendance()->exists()) {
                return [
                    'ticket' => $ticket->fresh(['registration.event.organization', 'registration.user', 'attendance.officer']),
                    'error' => 'Tiket sudah digunakan untuk check-in.',
                    'status' => 409,
                ];
            }

            if ($ticket->status !== TicketStatus::ACTIVE) {
                return [
                    'ticket' => $ticket,
                    'error' => 'Tiket tidak aktif.',
                    'status' => 409,
                ];
            }

            Attendance::create([
                'ticket_id' => $ticket->id,
                'officer_id' => $officer->id,
                'checked_in_at' => now(),
            ]);

            $ticket->update(['status' => TicketStatus::USED]);

            return [
                'ticket' => $ticket->fresh([
                    'registration.event.organization',
                    'registration.user',
                    'attendance.officer',
                ]),
                'error' => null,
                'status' => 200,
            ];
        }, 3);
    }
}
