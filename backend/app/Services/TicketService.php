<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Registration;
use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketService
{
    /**
     * Issue a ticket for a confirmed registration.
     *
     * @return array{0: Ticket, 1: string|null} The ticket and the plain token.
     */
    public function issue(Registration $registration): array
    {
        if ($registration->ticket()->exists()) {
            return [$registration->ticket()->firstOrFail(), null];
        }

        $ticketCode = (string) Str::uuid();
        $plainToken = $this->tokenForCode($ticketCode);

        $ticket = Ticket::create([
            'registration_id' => $registration->id,
            'ticket_code' => $ticketCode,
            'token_hash' => $this->hashToken($plainToken),
            'status' => TicketStatus::ACTIVE,
        ]);

        return [$ticket, $plainToken];
    }

    /**
     * Rebuild the plain token for an issued ticket so the owner can display
     * the QR code again. The token itself is never stored in the database.
     */
    public function tokenFor(Ticket $ticket): string
    {
        return $this->tokenForCode($ticket->ticket_code);
    }

    public function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function findByToken(string $token): ?Ticket
    {
        return Ticket::query()
            ->with(['registration.event.organization', 'registration.user', 'attendance'])
            ->where('token_hash', $this->hashToken($token))
            ->first();
    }

    private function tokenForCode(string $ticketCode): string
    {
        return hash_hmac('sha256', $ticketCode, (string) config('app.key'));
    }
}
