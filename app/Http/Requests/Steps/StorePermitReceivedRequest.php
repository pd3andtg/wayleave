<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 7: contractor records permit received date and uploads the permit file.
class StorePermitReceivedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'permit_received_date' => ['required', 'date'],
            'permit_file'          => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'remarks'              => ['nullable', 'string'],
        ];
    }
}
