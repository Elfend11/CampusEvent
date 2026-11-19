<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->whenLoaded(
                'roles',
                fn () => $this->roles
                    ->map(fn ($assignment) => $assignment->role instanceof UserRole
                        ? $assignment->role->value
                        : $assignment->role)
                    ->values(),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
