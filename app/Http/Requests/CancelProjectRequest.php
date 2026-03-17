<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validates the project cancellation form.
// Anyone (contractor, officer, admin) can cancel — reason is compulsory.
class CancelProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:5'],
        ];
    }
}
