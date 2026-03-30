<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 6 (Officer): overwrite the contractor's wayleave file with the endorsed version.
// endorsement_remarks is set automatically in the service — no manual input needed.
class EndorseWayleavePhbtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('officer') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'wayleave_file'  => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'endorsed_date'  => ['nullable', 'date'],
        ];
    }
}
