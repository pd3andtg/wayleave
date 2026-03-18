<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Step 4: contractor uploads a wayleave PBT record.
// pbt_name_other is required only when pbt_name is 'Others'.
class StoreWayleavePhbtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'pbt_number'             => ['required', 'in:PBT1,PBT2,PBT3'],
            'pbt_name'               => ['required', 'in:MBKT,MPK,MDS,MDB,MPD,JKR HT,JKR KN,JKR DN,JKR KT,JKR KM,JKR ST,Others'],
            'pbt_name_other'         => ['nullable', 'required_if:pbt_name,Others', 'string', 'max:255'],
            'wayleave_received_date' => ['required', 'date'],
            'wayleave_file'          => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
