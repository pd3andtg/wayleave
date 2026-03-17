<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Section 10: contractor uploads Notis Mula Kerja only.
class StoreNotisMulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'notis_mula_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
