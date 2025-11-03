<?php

namespace App\Http\Requests\TeamMember;

use App\Models\StoreUser;
use Illuminate\Foundation\Http\FormRequest;

class RemoveTeamMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('removeAny', StoreUser::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'team_member_ids' => ['required', 'array', 'min:1'],
            'team_member_ids.*' => ['uuid'],
            'store_id' => ['required', 'uuid']
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'team_member_ids.required' => 'The team member IDs are required.',
            'team_member_ids.array' => 'The team member IDs must be an array.',
            'team_member_ids.min' => 'At least one team member ID is required.',
            'team_member_ids.*.uuid' => 'Each team member ID must be a valid UUID.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.'
        ];
    }
}
