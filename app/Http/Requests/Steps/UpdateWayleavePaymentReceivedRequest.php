<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Section 7: officer updates received_posted_date and/or bg_bd document
// on an existing payment row where status = required.
class UpdateWayleavePaymentReceivedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'received_posted_date' => ['nullable', 'date'],
            'bg_bd_file'           => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
