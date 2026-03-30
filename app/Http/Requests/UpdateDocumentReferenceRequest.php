<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validates document reference edits. Admin only.
// File is optional on update — if omitted, existing file is preserved.
class UpdateDocumentReferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file'        => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
