<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Section 6: officer records one payment row (FI or Deposit) per PBT.
// payment_type distinguishes FI row from Deposit row.
// method_of_payment replaces old deposit_payment_type — now applies to both rows.
class StoreWayleavePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'wayleave_pbt_id'   => ['required', 'integer', 'exists:wayleave_pbts,id'],
            'payment_type'      => ['required', 'in:FI,Deposit'],
            'status'            => ['nullable', 'in:required,not_required,waived'],
            'amount'            => ['nullable', 'numeric', 'min:0'],
            'eds_no'            => ['nullable', 'string', 'max:255'],
            'method_of_payment' => ['nullable', 'in:BG,BD_DAP,EFT_DAP'],
            'application_date'  => ['nullable', 'date'],
        ];
    }
}
