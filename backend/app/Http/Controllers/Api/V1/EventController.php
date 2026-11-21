<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\AssignOfficerRequest;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Requests\Event\UpdateEventStatusRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserResource;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Event::query()->with('organization');

        $isManager = $user !== null
            && $user->hasAnyRole([UserRole::SUPER_ADMIN, UserRole::ORGANIZATION_ADMIN]);

        if ($request->boolean('mine') && $isManager) {
            if (! $user->hasRole(UserRole::SUPER_ADMIN)) {
                $query->whereHas(
                    'organization.members',
                    fn ($query) => $query->where('user_id', $user->id),
                );
            }

            if ($request->filled('status')) {
                $query->where('status', $request->string('status'));
            }
        } else {
            $query->where('status', EventStatus::PUBLISHED->value);
        }

        $query
            ->when($request->string('search')->trim()->value(), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->string('category')->trim()->value(), function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->filled('organization_id'), function ($query) use ($request) {
                $query->where('organization_id', $request->integer('organization_id'));
            });

        return EventResource::collection(
            $query->orderBy('event_start')->paginate(20),
        );
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        /** @var Organization $organization */
        $organization = Organization::findOrFail($request->validated('organization_id'));

        Gate::authorize('create', [Event::class, $organization]);

        $event = Event::create([
            ...$request->safe()->except('status'),
            'reserved_count' => 0,
            'sold_count' => 0,
            'status' => $request->validated('status', EventStatus::DRAFT->value),
        ]);

        return response()->json([
            'data' => new EventResource($event->load('organization')),
        ], 201);
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        return response()->json([
            'data' => new EventResource(
                $event->load(['organization', 'officers']),
            ),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $event->update($request->validated());

        return response()->json([
            'data' => new EventResource($event->load('organization')),
        ]);
    }

    public function updateStatus(UpdateEventStatusRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $target = EventStatus::from($request->validated('status'));

        if ($event->status !== $target) {
            if (! $event->status->canTransitionTo($target)) {
                return response()->json([
                    'message' => "Status event tidak dapat diubah dari {$event->status->value} ke {$target->value}.",
                ], 422);
            }

            $event->update(['status' => $target]);
        }

        return response()->json([
            'data' => new EventResource($event->load('organization')),
        ]);
    }

    public function destroy(Event $event): Response
    {
        $this->authorize('delete', $event);

        if ($event->registrations()->exists()) {
            abort(422, 'Event yang sudah memiliki pendaftaran tidak dapat dihapus.');
        }

        $event->delete();

        return response()->noContent();
    }

    public function officers(Event $event): AnonymousResourceCollection
    {
        $this->authorize('manageOfficers', $event);

        return UserResource::collection($event->officers()->orderBy('name')->get());
    }

    public function assignOfficer(AssignOfficerRequest $request, Event $event): JsonResponse
    {
        $this->authorize('manageOfficers', $event);

        $officer = User::findOrFail($request->validated('user_id'));

        $attached = $event->officers()->syncWithoutDetaching([$officer->id]);

        if (empty($attached['attached'])) {
            return response()->json([
                'message' => 'Pengguna sudah ditugaskan pada event ini.',
            ], 422);
        }

        return response()->json([
            'data' => new UserResource($officer->load('roles')),
        ], 201);
    }

    public function removeOfficer(Event $event, User $user): Response
    {
        $this->authorize('manageOfficers', $event);

        $event->officers()->detach($user->id);

        return response()->noContent();
    }
}
