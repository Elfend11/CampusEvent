<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\StoreRegistrationRequest;
use App\Http\Resources\RegistrationResource;
use App\Models\Event;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RegistrationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $registrations = Registration::query()
            ->with(['event.organization', 'payment', 'ticket'])
            ->where('user_id', $request->user()->id)
            ->when($request->filled('event_id'), function ($query) use ($request) {
                $query->where('event_id', $request->integer('event_id'));
            })
            ->latest()
            ->paginate(20);

        return RegistrationResource::collection($registrations);
    }

    public function store(StoreRegistrationRequest $request, RegistrationService $service): JsonResponse
    {
        /** @var Event $event */
        $event = Event::findOrFail($request->validated('event_id'));

        $result = $service->register($request->user(), $event);

        $payload = (new RegistrationResource($result['registration']))->resolve();
        $payload['ticket_token'] = $result['ticket_token'];

        return response()->json(['data' => $payload], $result['created'] ? 201 : 200);
    }

    public function show(Registration $registration): JsonResponse
    {
        $this->authorize('view', $registration);

        return response()->json([
            'data' => new RegistrationResource(
                $registration->load(['event.organization', 'payment', 'ticket']),
            ),
        ]);
    }

    public function cancel(Registration $registration, RegistrationService $service): JsonResponse
    {
        $this->authorize('cancel', $registration);

        $cancelled = $service->cancel($registration);

        return response()->json([
            'data' => new RegistrationResource($cancelled),
        ]);
    }
}
