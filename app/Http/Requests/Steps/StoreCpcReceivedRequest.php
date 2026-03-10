<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 10: contractor uploads the received CPC. Triggers project status → completed.
class StoreCpcReceivedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        $project  = $this->route('project');
        $required = $project->cpcReceived ? 'nullable' : 'required';

        return [
            'cpc_file' => [$required, 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
