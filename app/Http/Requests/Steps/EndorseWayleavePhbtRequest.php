<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 5: officer endorses a wayleave PBT record and fills payment details.
class EndorseWayleavePhbtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'endorsed_file'        => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'fi_payment'           => ['nullable', 'in:required,not_required,waived'],
            'fi_eds_no'            => ['nullable', 'string', 'max:255'],
            'fi_date'              => ['nullable', 'date'],
            'deposit_payment'      => ['nullable', 'in:required,not_required,waived'],
            'deposit_eds_no'       => ['nullable', 'string', 'max:255'],
            'deposit_payment_type' => ['nullable', 'in:BG,BD'],
            'deposit_date'         => ['nullable', 'date'],
        ];
    }
}
