<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Section 8: update an existing permit submission (date required, file optional — kept if blank).
class UpdatePermitSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'submit_date'     => ['required', 'date'],
            'submission_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
