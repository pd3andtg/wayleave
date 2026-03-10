<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validates the new project registration form (Step 1 — contractor only).
class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('contractor');
    }

    public function rules(): array
    {
        return [
            'ref_no'       => ['nullable', 'string', 'max:255'],
            'lor_no'       => ['nullable', 'string', 'max:255'],
            'project_no'   => ['nullable', 'string', 'max:255'],
            'project_desc' => ['required', 'string'],
            'nd_state'     => ['required', 'in:ND_TRG,ND_PHG,ND_KEL'],
            'remarks'      => ['nullable', 'string'],
        ];
    }
}
