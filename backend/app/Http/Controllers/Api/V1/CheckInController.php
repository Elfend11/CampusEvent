<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckIn\CheckInRequest;
use App\Http\Resources\TicketResource;
use App\Services\CheckInService;
use Illuminate\Http\JsonResponse;

class CheckInController extends Controller
{
    public function validateToken(CheckInRequest $request, CheckInService $service): JsonResponse
    {
        $result = $service->checkIn(
            $request->user(),
            $request->validated('token'),
            $request->validated('event_id'),
        );

        $payload = [
            'data' => $result['ticket'] !== null
                ? (new TicketResource($result['ticket']))->resolve()
                : null,
        ];

        if ($result['error'] !== null) {
            return response()->json(['message' => $result['error'], ...$payload], $result['status']);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Check-in berhasil.',
            ...$payload,
        ]);
    }
}
