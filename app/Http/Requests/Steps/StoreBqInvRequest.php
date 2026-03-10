<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 2: contractor uploads BQ/INV file. File required on first upload, optional on replace.
class StoreBqInvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        $project  = $this->route('project');
        $required = $project->bqInv?->bq_inv_file ? 'nullable' : 'required';

        return [
            'bq_inv_file' => [$required, 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
