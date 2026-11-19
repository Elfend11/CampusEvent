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
        ];
    }
}
