<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validates Step 1 edits. Any user who can update the project can submit this.
class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
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
