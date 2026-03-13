<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 7: officer records FI and deposit payment details for a specific PBT.
class StoreWayleavePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'wayleave_pbt_id'          => ['required', 'integer', 'exists:wayleave_pbts,id'],
            'fi_payment'               => ['nullable', 'in:required,not_required,waived'],
            'fi_eds_no'                => ['nullable', 'string', 'max:255'],
            'fi_application_date'      => ['nullable', 'date'],
            'deposit_payment'          => ['nullable', 'in:required,not_required,waived'],
            'deposit_eds_no'           => ['nullable', 'string', 'max:255'],
            'deposit_payment_type'     => ['nullable', 'in:BG,BD'],
            'deposit_application_date' => ['nullable', 'date'],
        ];
    }
}
