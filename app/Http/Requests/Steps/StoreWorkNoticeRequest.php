<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 10: contractor uploads Notis Mula Kerja and Notis Siap Kerja.
// Gambar (site photos) has been removed from the system.
// Files are optional — contractor can upload one at a time.
// Service preserves existing files if no new file is sent.
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
        ];
    }
}
