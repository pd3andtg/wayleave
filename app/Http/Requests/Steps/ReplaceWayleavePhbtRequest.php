<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 3 (Contractor): replace their own wayleave file before officer endorsement.
// Only allowed while the PBT has not yet been endorsed (endorsed_by is null).
class ReplaceWayleavePhbtRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pbt = $this->route('wayleavePhbt');

        // Only contractors can use this route, and only before endorsement.
        return $this->user()->can('update', $this->route('project'))
            && is_null($pbt->endorsed_by);
    }

    public function rules(): array
    {
        return [
            'wayleave_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
