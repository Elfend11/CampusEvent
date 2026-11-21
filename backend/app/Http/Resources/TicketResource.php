<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ticket
 */
class TicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_code' => $this->ticket_code,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'registration_id' => $this->registration_id,
            'event' => $this->whenLoaded(
                'registration',
                fn () => $this->registration->event
                    ? new EventResource($this->registration->event)
                    : null,
            ),
            'owner' => $this->whenLoaded(
                'registration',
                fn () => $this->registration->user
                    ? new UserResource($this->registration->user)
                    : null,
            ),
            'attendance' => $this->whenLoaded(
                'attendance',
                fn () => $this->attendance
                    ? [
                        'checked_in_at' => $this->attendance->checked_in_at?->toIso8601String(),
                        'officer' => $this->attendance->relationLoaded('officer')
                            ? new UserResource($this->attendance->officer)
                            : null,
                    ]
                    : null,
            ),
        ];
    }
}
