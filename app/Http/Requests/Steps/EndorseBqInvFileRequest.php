<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 5: officer endorses a BQ/INV file.
// BQ fields: document_info, date, remarks.
// INV fields: document_info, date, amount, eds_no, payment_status, remarks.
// The controller routes to the correct endorsement table based on the file's payment_type.
class EndorseBqInvFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'document_info'  => ['required', 'string', 'max:255'],
            'date'           => ['required', 'date'],
            // INV-only fields — nullable for BQ endorsements.
            'amount'         => ['nullable', 'numeric', 'min:0'],
            'eds_no'         => ['nullable', 'string', 'max:255'],
            'payment_status' => ['nullable', 'in:paid,outstanding,waived'],
            'remarks'        => ['nullable', 'string'],
        ];
    }
}
