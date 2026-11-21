<?php

namespace App\Policies;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;

class EventPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Event $event): bool
    {
        if ($event->status === EventStatus::PUBLISHED) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $this->isSuperAdmin($user)
            || $this->isOrganizationAdminOf($user, $event)
            || $this->isAssignedOfficer($user, $event);
    }

    public function create(User $user, Organization $organization): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isOrganizationAdminOfOrganization($user, $organization);
    }

    public function update(User $user, Event $event): bool
    {
        return $this->isSuperAdmin($user) || $this->isOrganizationAdminOf($user, $event);
    }

    public function manageOfficers(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->isSuperAdmin($user) || $this->isOrganizationAdminOf($user, $event);
    }

    public function viewReport(User $user, Event $event): bool
    {
        return $this->isSuperAdmin($user) || $this->isOrganizationAdminOf($user, $event);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN);
    }

    private function isOrganizationAdminOf(User $user, Event $event): bool
    {
        if (! $user->hasRole(UserRole::ORGANIZATION_ADMIN)) {
            return false;
        }

        return $event->organization()
            ->whereHas('members', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }

    private function isOrganizationAdminOfOrganization(User $user, Organization $organization): bool
    {
        if (! $user->hasRole(UserRole::ORGANIZATION_ADMIN)) {
            return false;
        }

        return $organization->members()
            ->where('user_id', $user->id)
            ->exists();
    }

    private function isAssignedOfficer(User $user, Event $event): bool
    {
        return $event->eventOfficers()
            ->where('user_id', $user->id)
            ->exists();
    }
}
