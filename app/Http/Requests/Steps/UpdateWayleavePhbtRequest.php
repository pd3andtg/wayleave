<?php

namespace App\Http\Requests\Steps;

use Illuminate\Foundation\Http\FormRequest;

// Section 4: update an existing wayleave PBT record.
// File is optional — existing file is kept if none is uploaded.
// pbt_number is not editable (it is the record identifier).
class UpdateWayleavePhbtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'pbt_name'               => ['required', 'in:MBKT - MAJLIS BANDARAYA KUALA TERENGGANU,MPK - MAJLIS PERBANDARAN KEMAMAN,MDS - MAJLIS PERBANDARAN SETIU,MDB - MAJLIS DAERAH BESUT,MPD - MAJLIS PERBANDARAN DUNGUN,JKR HT - JKR HULU TERENGGANU,JKR KN - JKR KUALA NERUS,JKR DN - JKR DUNGUN,JKR KT - JKR KUALA TERENGGANU,JKR KM - JKR KEMAMAN,JKR ST - JKR SETIU,Others'],
            'pbt_name_other'         => ['nullable', 'required_if:pbt_name,Others', 'string', 'max:255'],
            'wayleave_received_date' => ['nullable', 'date'],
            'wayleave_file'          => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
