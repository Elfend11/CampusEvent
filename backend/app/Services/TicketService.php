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

        $plainToken = Str::random(64);

        $ticket = Ticket::create([
            'registration_id' => $registration->id,
            'ticket_code' => (string) Str::uuid(),
            'token_hash' => hash('sha256', $plainToken),
            'status' => TicketStatus::ACTIVE,
        ]);

        return [$ticket, $plainToken];
    }
}
