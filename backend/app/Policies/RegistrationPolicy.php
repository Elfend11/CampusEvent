<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    public function view(User $user, Registration $registration): bool
    {
        return $user->is($registration->user)
            || $user->hasRole(UserRole::SUPER_ADMIN)
            || $this->isOrganizationAdminOf($user, $registration);
    }

    public function cancel(User $user, Registration $registration): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN)
            || $this->isOrganizationAdminOf($user, $registration);
    }

    private function isOrganizationAdminOf(User $user, Registration $registration): bool
    {
        if (! $user->hasRole(UserRole::ORGANIZATION_ADMIN)) {
            return false;
        }

        return $registration->event
            ->organization()
            ->whereHas('members', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }
}
