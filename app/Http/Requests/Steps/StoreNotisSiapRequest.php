<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Section 11: contractor uploads Notis Siap Kerja only.
class StoreNotisSiapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'notis_siap_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
