<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 4: contractor uploads a BQ/INV file for a specific slot (file_number 1-6).
// File is required on first upload for a slot, optional when replacing.
class StoreBqInvFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        $project    = $this->route('project');
        $fileNumber = $this->input('file_number');

        // File is required only when this slot has no existing record.
        $existing = $project->bqInvFiles()->where('file_number', $fileNumber)->first();
        $fileRule = $existing ? 'nullable' : 'required';

        return [
            'file_number'  => ['required', 'integer', 'between:1,6'],
            'file_path'    => [$fileRule, 'file', 'mimes:pdf', 'max:10240'],
            'document_info'=> ['required', 'string', 'max:255'],
            'payment_type' => ['required', 'in:BQ,INV'],
            'date'         => ['required', 'date'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'eds_no'       => ['required', 'string', 'max:255'],
            'remarks'      => ['nullable', 'string'],
        ];
    }
}
