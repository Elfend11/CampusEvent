<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Event
 */
class EventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'organization' => $this->whenLoaded(
                'organization',
                fn () => new OrganizationResource($this->organization),
            ),
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'category' => $this->category,
            'registration_start' => $this->registration_start?->toIso8601String(),
            'registration_end' => $this->registration_end?->toIso8601String(),
            'event_start' => $this->event_start?->toIso8601String(),
            'event_end' => $this->event_end?->toIso8601String(),
            'capacity' => $this->capacity,
            'reserved_count' => $this->reserved_count,
            'sold_count' => $this->sold_count,
            'available_quota' => $this->availableQuota(),
            'ticket_price' => (float) $this->ticket_price,
            'is_free' => $this->isFree(),
            'status' => $this->status->value,
            'officers' => $this->whenLoaded(
                'officers',
                fn () => UserResource::collection($this->officers),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
