<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Validates new BOQ/INV row creation (Section 2/3 — "Add New BOQ/INV" button).
// Contractor submits: document_info, type, date_received, amount, file, remarks.
class StoreBoqInvItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'document_info' => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:BQ,INV'],
            'date_received' => ['required', 'date'],
            'amount'        => ['nullable', 'numeric', 'min:0'],
            'file'          => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remarks'       => ['nullable', 'string', 'max:255'],
        ];
    }
}
