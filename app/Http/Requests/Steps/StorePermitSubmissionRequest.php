<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 6: contractor submits the permit document to KUTT/PBT.
class StorePermitSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        $project  = $this->route('project');
        $required = $project->permitSubmission ? 'nullable' : 'required';

        return [
            'submit_date'     => [$required, 'date'],
            'submission_file' => [$required, 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
