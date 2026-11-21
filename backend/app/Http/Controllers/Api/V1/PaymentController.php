<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\SimulatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    private const RELATIONS = ['registration.event.organization', 'registration.ticket'];

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return response()->json([
            'data' => new PaymentResource($payment->load(self::RELATIONS)),
        ]);
    }

    public function simulate(
        SimulatePaymentRequest $request,
        Payment $payment,
        PaymentService $service,
    ): JsonResponse {
        $this->authorize('simulate', $payment);

        $result = $service->simulate($payment, $request->boolean('success', true));

        $payload = [
            'data' => (new PaymentResource($result['payment']))->resolve(),
            'ticket_token' => $result['ticket_token'],
        ];

        if ($result['error'] !== null) {
            return response()->json(['message' => $result['error'], ...$payload], $result['status']);
        }

        return response()->json($payload);
    }
}
