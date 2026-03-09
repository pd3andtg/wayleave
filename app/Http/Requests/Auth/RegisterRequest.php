<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

// Validates user self-registration.
// company_selection is either 'tmtech' or an approved company ID.
// unit_id and id_number rules depend on the company selection.
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isTmTech = $this->company_selection === 'tmtech';

        return [
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'max:255', 'unique:users,email'],
            'company_selection'  => ['required', 'string'],
            'unit_id'            => $isTmTech ? ['required', 'exists:units,id'] : ['nullable'],
            'id_number'          => ['required', 'string', 'max:255'],
            'password'           => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'company_selection.required' => 'Please select a company.',
            'unit_id.required'           => 'Please select your unit.',
            'unit_id.exists'             => 'Selected unit is invalid.',
            'id_number.required'         => 'Staff ID / IC Number is required.',
        ];
    }
}
