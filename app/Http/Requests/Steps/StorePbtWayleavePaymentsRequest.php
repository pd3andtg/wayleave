<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Section 6: officer saves both FI and Deposit for one PBT in a single form submit.
// Fields are grouped under fi[*] and deposit[*] to distinguish the two payment types.
class StorePbtWayleavePaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'wayleave_pbt_id'          => ['required', 'integer', 'exists:wayleave_pbts,id'],

            'fi.status'                => ['nullable', 'in:required,not_required,waived'],
            'fi.amount'                => ['nullable', 'numeric', 'min:0'],
            'fi.eds_no'                => ['nullable', 'string', 'max:255'],
            'fi.method_of_payment'     => ['nullable', 'in:BG,BD_DAP,EFT_DAP'],
            'fi.application_date'      => ['nullable', 'date'],

            'deposit.status'           => ['nullable', 'in:required,not_required,waived'],
            'deposit.amount'           => ['nullable', 'numeric', 'min:0'],
            'deposit.eds_no'           => ['nullable', 'string', 'max:255'],
            'deposit.method_of_payment'=> ['nullable', 'in:BG,BD_DAP,EFT_DAP'],
            'deposit.application_date' => ['nullable', 'date'],
        ];
    }
}
