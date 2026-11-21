<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\ValidateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    private const RELATIONS = [
        'registration.event.organization',
        'registration.user',
        'attendance.officer',
    ];

    public function index(Request $request): AnonymousResourceCollection
    {
        $tickets = Ticket::query()
            ->with(['registration.event.organization', 'attendance'])
            ->whereHas(
                'registration',
                fn ($query) => $query->where('user_id', $request->user()->id),
            )
            ->latest()
            ->paginate(20);

        return TicketResource::collection($tickets);
    }

    public function show(Ticket $ticket, TicketService $service): JsonResponse
    {
        $this->authorize('view', $ticket);

        $ticket->load(self::RELATIONS);

        $payload = (new TicketResource($ticket))->resolve();
        $payload['token'] = $service->tokenFor($ticket);

        return response()->json(['data' => $payload]);
    }

    public function validateToken(
        ValidateTicketRequest $request,
        TicketService $service,
    ): JsonResponse {
        $ticket = $service->findByToken($request->validated('token'));

        if ($ticket === null) {
            return response()->json(['message' => 'Tiket tidak ditemukan.'], 404);
        }

        if ($ticket->status !== TicketStatus::ACTIVE) {
            return response()->json([
                'message' => 'Tiket tidak aktif atau sudah digunakan.',
                'data' => new TicketResource($ticket),
            ], 409);
        }

        if ($request->filled('event_id')
            && $ticket->registration->event_id !== $request->integer('event_id')) {
            return response()->json([
                'message' => 'Tiket bukan untuk event ini.',
                'data' => new TicketResource($ticket),
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'data' => new TicketResource($ticket),
        ]);
    }
}
