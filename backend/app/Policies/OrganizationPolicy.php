<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::SUPER_ADMIN,
            UserRole::ORGANIZATION_ADMIN,
        ]);
    }

    public function view(User $user, Organization $organization): bool
    {
        return $this->isSuperAdmin($user) || $this->isOrganizationAdminOf($user, $organization);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $this->isSuperAdmin($user) || $this->isOrganizationAdminOf($user, $organization);
    }

    public function manageMembers(User $user, Organization $organization): bool
    {
        return $this->isSuperAdmin($user) || $this->isOrganizationAdminOf($user, $organization);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN);
    }

    private function isOrganizationAdminOf(User $user, Organization $organization): bool
    {
        if (! $user->hasRole(UserRole::ORGANIZATION_ADMIN)) {
            return false;
        }

        return $organization->members()
            ->where('user_id', $user->id)
            ->exists();
    }
}
