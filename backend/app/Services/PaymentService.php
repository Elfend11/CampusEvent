<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private readonly TicketService $tickets) {}

    /**
     * Create the single payment record for a paid registration when missing.
     */
    public function ensureExists(Registration $registration, Event $event): Payment
    {
        return Payment::firstOrCreate(
            ['registration_id' => $registration->id],
            [
                'amount' => $event->ticket_price,
                'status' => PaymentStatus::PENDING,
                'paid_at' => null,
            ],
        );
    }

    /**
     * Reset the existing payment record for a new reservation cycle.
     */
    public function resetToPending(Registration $registration, Event $event): Payment
    {
        return Payment::updateOrCreate(
            ['registration_id' => $registration->id],
            [
                'amount' => $event->ticket_price,
                'status' => PaymentStatus::PENDING,
                'paid_at' => null,
            ],
        );
    }

    /**
     * Simulate a payment attempt.
     *
     * A failed simulation keeps the reservation active so the same payment
     * record can be retried. Expired or inactive reservations never issue
     * a ticket.
     *
     * @return array{
     *     payment: Payment,
     *     registration: Registration,
     *     ticket_token: string|null,
     *     error: string|null,
     *     status: int
     * }
     */
    public function simulate(Payment $payment, bool $successful = true): array
    {
        return DB::transaction(function () use ($payment, $successful) {
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var Registration $registration */
            $registration = Registration::query()
                ->whereKey($lockedPayment->registration_id)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var Event $event */
            $event = Event::query()
                ->whereKey($registration->event_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status === PaymentStatus::PAID) {
                if (! $registration->ticket()->exists()) {
                    return $this->result(
                        $lockedPayment,
                        $registration,
                        'Pembayaran sudah lunas tetapi tiket tidak ditemukan.',
                        409,
                    );
                }

                return $this->result($lockedPayment, $registration);
            }

            if ($registration->status !== RegistrationStatus::PENDING) {
                return $this->result(
                    $lockedPayment,
                    $registration,
                    'Reservasi tidak aktif dan tidak dapat dibayar.',
                    409,
                );
            }

            if ($registration->expires_at === null || $registration->expires_at->isPast()) {
                $registration->update(['status' => RegistrationStatus::EXPIRED]);
                $this->releaseReservedQuota($event);
                $lockedPayment->update(['status' => PaymentStatus::FAILED]);

                return $this->result(
                    $lockedPayment,
                    $registration,
                    'Reservasi sudah kedaluwarsa.',
                    409,
                );
            }

            if (! $successful) {
                $lockedPayment->update([
                    'status' => PaymentStatus::FAILED,
                    'paid_at' => null,
                ]);

                return $this->result($lockedPayment, $registration);
            }

            if ($lockedPayment->status === PaymentStatus::FAILED) {
                $lockedPayment->update(['status' => PaymentStatus::PENDING]);
            }

            $lockedPayment->update([
                'amount' => $event->ticket_price,
                'status' => PaymentStatus::PAID,
                'paid_at' => now(),
            ]);

            $registration->update([
                'status' => RegistrationStatus::CONFIRMED,
                'expires_at' => null,
            ]);

            $this->releaseReservedQuota($event);
            $event->increment('sold_count');

            [$ticket, $ticketToken] = $this->tickets->issue($registration);

            return $this->result($lockedPayment, $registration, null, 200, $ticketToken);
        }, 3);
    }

    /**
     * @return array{
     *     payment: Payment,
     *     registration: Registration,
     *     ticket_token: string|null,
     *     error: string|null,
     *     status: int
     * }
     */
    private function result(
        Payment $payment,
        Registration $registration,
        ?string $error = null,
        int $status = 200,
        ?string $ticketToken = null,
    ): array {
        $relations = ['registration.event.organization', 'registration.ticket'];

        return [
            'payment' => $payment->fresh($relations),
            'registration' => $registration->fresh(['event.organization', 'payment', 'ticket']),
            'ticket_token' => $ticketToken,
            'error' => $error,
            'status' => $status,
        ];
    }

    private function releaseReservedQuota(Event $event): void
    {
        if ($event->reserved_count > 0) {
            $event->decrement('reserved_count');
        }
    }
}
