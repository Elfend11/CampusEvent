<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Http\Resources\EventResource;
use App\Http\Resources\RegistrationResource;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(User $user): array
    {
        return [
            'roles' => $user->roles()
                ->pluck('role')
                ->map(fn ($role) => $role instanceof UserRole ? $role->value : $role)
                ->values(),
            'platform' => $user->hasRole(UserRole::SUPER_ADMIN)
                ? $this->platformSummary()
                : null,
            'organizations' => $user->hasRole(UserRole::ORGANIZATION_ADMIN)
                ? $this->organizationSummary($user)
                : null,
            'officer' => $user->hasRole(UserRole::CHECK_IN_OFFICER)
                ? $this->officerSummary($user)
                : null,
            'student' => $this->studentSummary($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function eventReport(Event $event, array $filters = []): array
    {
        $event->load('organization');

        $query = Registration::query()
            ->where('event_id', $event->id)
            ->with(['user', 'payment', 'ticket.attendance'])
            ->latest()
            ->latest('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('ticket', function ($query) use ($search) {
                    $query->where('ticket_code', 'like', "%{$search}%");
                });
            });
        }

        $registrations = $query->paginate($filters['per_page'] ?? 20);

        return [
            'event' => (new EventResource($event))->resolve(),
            'summary' => $this->eventSummary($event),
            'registrations' => RegistrationResource::collection($registrations)->resolve(),
            'meta' => $this->paginationMeta($registrations),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function attendanceReport(Event $event, array $filters = []): array
    {
        $event->load('organization');

        $query = Attendance::query()
            ->whereHas(
                'ticket.registration',
                fn ($query) => $query->where('event_id', $event->id),
            )
            ->with(['officer', 'ticket.registration.user'])
            ->latest('checked_in_at');

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query->whereHas('ticket.registration.user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('ticket', function ($query) use ($search) {
                    $query->where('ticket_code', 'like', "%{$search}%");
                });
            });
        }

        $attendances = $query->paginate($filters['per_page'] ?? 20);

        return [
            'event' => (new EventResource($event))->resolve(),
            'summary' => $this->eventSummary($event),
            'attendances' => $attendances->through(fn (Attendance $attendance) => [
                'id' => $attendance->id,
                'checked_in_at' => $attendance->checked_in_at?->toIso8601String(),
                'officer' => $attendance->officer ? [
                    'id' => $attendance->officer->id,
                    'name' => $attendance->officer->name,
                ] : null,
                'ticket' => [
                    'id' => $attendance->ticket->id,
                    'ticket_code' => $attendance->ticket->ticket_code,
                    'status' => $attendance->ticket->status->value,
                    'owner' => [
                        'id' => $attendance->ticket->registration->user->id,
                        'name' => $attendance->ticket->registration->user->name,
                        'email' => $attendance->ticket->registration->user->email,
                    ],
                ],
            ])->items(),
            'meta' => $this->paginationMeta($attendances),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function platformSummary(): array
    {
        return [
            'users_count' => User::count(),
            'organizations_count' => Organization::count(),
            'events_count' => Event::count(),
            'published_events_count' => Event::where('status', EventStatus::PUBLISHED->value)->count(),
            'registrations_count' => Registration::count(),
            'confirmed_registrations_count' => Registration::where('status', RegistrationStatus::CONFIRMED->value)->count(),
            'tickets_count' => Ticket::count(),
            'used_tickets_count' => Ticket::where('status', TicketStatus::USED->value)->count(),
            'check_ins_count' => Attendance::count(),
            'paid_payments_total' => (float) Payment::where('status', PaymentStatus::PAID->value)->sum('amount'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function organizationSummary(User $user): array
    {
        $organizations = $user->organizations()->withCount('events')->get();

        $organizationIds = $organizations->pluck('id');
        $eventIds = Event::whereIn('organization_id', $organizationIds)->pluck('id');

        return [
            'organizations_count' => $organizations->count(),
            'events_count' => $eventIds->count(),
            'registrations_count' => Registration::whereIn('event_id', $eventIds)->count(),
            'confirmed_registrations_count' => Registration::whereIn('event_id', $eventIds)
                ->where('status', RegistrationStatus::CONFIRMED->value)
                ->count(),
            'tickets_count' => Ticket::whereHas(
                'registration',
                fn ($query) => $query->whereIn('event_id', $eventIds),
            )->count(),
            'check_ins_count' => Attendance::whereHas(
                'ticket.registration',
                fn ($query) => $query->whereIn('event_id', $eventIds),
            )->count(),
            'paid_payments_total' => (float) Payment::whereHas(
                'registration',
                fn ($query) => $query->whereIn('event_id', $eventIds),
            )->where('status', PaymentStatus::PAID->value)->sum('amount'),
            'organizations' => $organizations->map(fn (Organization $organization) => [
                'id' => $organization->id,
                'name' => $organization->name,
                'events_count' => $organization->events_count,
            ])->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function officerSummary(User $user): array
    {
        return [
            'assigned_events_count' => $user->officerEvents()->count(),
            'check_ins_count' => Attendance::where('officer_id', $user->id)->count(),
            'assigned_events' => EventResource::collection(
                $user->officerEvents()
                    ->with('organization')
                    ->orderBy('event_start')
                    ->limit(10)
                    ->get(),
            )->resolve(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function studentSummary(User $user): array
    {
        return [
            'available_events_count' => Event::query()
                ->where('status', EventStatus::PUBLISHED->value)
                ->where('registration_end', '>=', now())
                ->count(),
            'registrations_count' => $user->registrations()->count(),
            'confirmed_registrations_count' => $user->registrations()
                ->where('status', RegistrationStatus::CONFIRMED->value)
                ->count(),
            'tickets_count' => Ticket::whereHas(
                'registration',
                fn ($query) => $query->where('user_id', $user->id),
            )->count(),
            'active_tickets_count' => Ticket::whereHas(
                'registration',
                fn ($query) => $query->where('user_id', $user->id),
            )->where('status', TicketStatus::ACTIVE->value)->count(),
            'attended_count' => Attendance::whereHas(
                'ticket.registration',
                fn ($query) => $query->where('user_id', $user->id),
            )->count(),
            'paid_payments_total' => (float) Payment::whereHas(
                'registration',
                fn ($query) => $query->where('user_id', $user->id),
            )->where('status', PaymentStatus::PAID->value)->sum('amount'),
            'upcoming_events' => EventResource::collection(
                Event::query()
                    ->where('status', EventStatus::PUBLISHED->value)
                    ->where('event_start', '>=', now())
                    ->with('organization')
                    ->orderBy('event_start')
                    ->limit(5)
                    ->get(),
            )->resolve(),
            'recent_registrations' => RegistrationResource::collection(
                $user->registrations()
                    ->with(['event.organization', 'payment', 'ticket'])
                    ->latest()
                    ->limit(5)
                    ->get(),
            )->resolve(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventSummary(Event $event): array
    {
        $registrations = Registration::where('event_id', $event->id);

        return [
            'capacity' => $event->capacity,
            'reserved_count' => $event->reserved_count,
            'sold_count' => $event->sold_count,
            'available_quota' => $event->availableQuota(),
            'registrations_count' => (clone $registrations)->count(),
            'confirmed_registrations_count' => (clone $registrations)
                ->where('status', RegistrationStatus::CONFIRMED->value)
                ->count(),
            'pending_registrations_count' => (clone $registrations)
                ->where('status', RegistrationStatus::PENDING->value)
                ->count(),
            'tickets_count' => Ticket::whereHas(
                'registration',
                fn ($query) => $query->where('event_id', $event->id),
            )->count(),
            'used_tickets_count' => Ticket::whereHas(
                'registration',
                fn ($query) => $query->where('event_id', $event->id),
            )->where('status', TicketStatus::USED->value)->count(),
            'check_ins_count' => Attendance::whereHas(
                'ticket.registration',
                fn ($query) => $query->where('event_id', $event->id),
            )->count(),
            'paid_payments_total' => (float) Payment::whereHas(
                'registration',
                fn ($query) => $query->where('event_id', $event->id),
            )->where('status', PaymentStatus::PAID->value)->sum('amount'),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
