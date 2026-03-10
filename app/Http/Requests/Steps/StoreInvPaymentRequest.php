<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 3: officer saves an invoice payment record (INV1, INV2, or INV3).
class StoreInvPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'inv'                      => ['required', 'array'],
            'inv.*'                    => ['array'],
            'inv.*.eds_no'             => ['nullable', 'string', 'max:255'],
            'inv.*.date'               => ['nullable', 'date'],
            'inv.*.amount'             => ['nullable', 'numeric', 'min:0'],
            'inv.*.payment_status'     => ['nullable', 'in:paid,outstanding'],
        ];
    }
}
