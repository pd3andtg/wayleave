<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 9: contractor uploads four CPC application documents.
// All files are always optional — contractor can upload them one at a time
// as documents become available. Service preserves existing files if no new file is sent.
class StoreCpcApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'date_submit_to_kutt'    => ['nullable', 'date'],
            'surat_serahan_file'     => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'laporan_bergambar_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'salinan_coa_file'       => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'salinan_permit_file'    => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
