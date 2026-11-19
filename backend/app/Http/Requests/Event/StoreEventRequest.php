<?php

namespace App\Http\Requests\Event;

use App\Enums\EventStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'registration_start' => ['required', 'date'],
            'registration_end' => ['required', 'date', 'after_or_equal:registration_start'],
            'event_start' => ['required', 'date', 'after_or_equal:registration_end'],
            'event_end' => ['required', 'date', 'after:event_start'],
            'capacity' => ['required', 'integer', 'min:1'],
            'ticket_price' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', Rule::enum(EventStatus::class)],
        ];
    }
}
