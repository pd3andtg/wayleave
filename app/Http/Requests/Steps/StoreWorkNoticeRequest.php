<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 8: contractor uploads Notis Mula, Notis Siap, and combined site photos PDF.
// All files are always optional — contractor can upload them one at a time
// as documents become available. Service preserves existing files if no new file is sent.
class StoreWorkNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'notis_mula_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'notis_siap_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'gambar_file'     => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
