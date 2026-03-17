<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Validates officer/admin update of a BOQ/INV row in Section 3.
// Includes eds_no, payment_status, and optional endorsed file upload.
class UpdateBoqInvItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'document_info'  => ['nullable', 'string', 'max:255'],
            'type'           => ['nullable', 'in:BQ,INV'],
            'date_received'  => ['nullable', 'date'],
            'amount'         => ['nullable', 'numeric', 'min:0'],
            'eds_no'         => ['nullable', 'string', 'max:255'],
            'payment_status' => ['nullable', 'in:endorsed,endorsed_and_paid,pending_endorsement,waived,cancelled'],
            'file'           => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remarks'        => ['nullable', 'string', 'max:255'],
        ];
    }
}
