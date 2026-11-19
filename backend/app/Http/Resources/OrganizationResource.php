<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Organization
 */
class OrganizationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'members_count' => $this->whenCounted('members'),
            'members' => $this->whenLoaded(
                'members',
                fn () => OrganizationMemberResource::collection($this->members),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
