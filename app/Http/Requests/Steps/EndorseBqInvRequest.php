<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 3: officer endorses BQ/INV and sets payment status.
class EndorseBqInvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'endorsed_file'            => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'payment_status'           => ['nullable', 'in:waived,charged'],
            'inv'                      => ['nullable', 'array'],
            'inv.*'                    => ['array'],
            'inv.*.eds_no'             => ['nullable', 'string', 'max:255'],
            'inv.*.date'               => ['nullable', 'date'],
            'inv.*.amount'             => ['nullable', 'numeric', 'min:0'],
            'inv.*.payment_status'     => ['nullable', 'in:paid,outstanding'],
        ];
    }
}
