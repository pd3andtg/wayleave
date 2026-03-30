<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validates new document reference uploads. Admin only.
// Accepted file types: PDF, DOC, DOCX, JPG, JPEG, PNG. Max 10MB.
class StoreDocumentReferenceRequest extends FormRequest
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
            'file'        => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
