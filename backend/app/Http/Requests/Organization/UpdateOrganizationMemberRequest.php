<?php

namespace App\Http\Requests\Organization;

use App\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationMemberRequest extends FormRequest
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
            'role' => ['required', 'string', Rule::in([
                OrganizationMember::ROLE_ADMIN,
                OrganizationMember::ROLE_MEMBER,
            ])],
        ];
    }
}
