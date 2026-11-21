<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Registration
 */
class RegistrationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'event' => $this->whenLoaded('event', fn () => new EventResource($this->event)),
            'payment' => $this->whenLoaded('payment', fn () => $this->payment
                ? new PaymentResource($this->payment)
                : null),
            'ticket' => $this->whenLoaded('ticket', fn () => $this->ticket
                ? new TicketResource($this->ticket)
                : null),
        ];
    }
}
