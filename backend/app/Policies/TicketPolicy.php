<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->is($ticket->registration->user)
            || $user->hasRole(UserRole::SUPER_ADMIN);
    }
}
