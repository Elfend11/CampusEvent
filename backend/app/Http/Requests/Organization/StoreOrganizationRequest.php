<?php

namespace App\Http\Requests\Organization;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:organizations,name'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['sometimes', 'string', Rule::in([
                Organization::STATUS_ACTIVE,
                Organization::STATUS_INACTIVE,
            ])],
        ];
    }
}
