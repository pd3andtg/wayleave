<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Section 9: update an existing permit received record (date required, file optional — kept if blank).
class UpdatePermitReceivedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'permit_received_date' => ['required', 'date'],
            'permit_file'          => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
