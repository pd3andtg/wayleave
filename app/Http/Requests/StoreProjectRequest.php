<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validates the new project registration form (all roles can register).
// Officers/admins can set self_applied_by_tm and assign to any approved company.
// Contractors have company_id auto-set from their profile — no dropdown.
class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy::create() handles role checks
    }

    public function rules(): array
    {
        return [
            'ref_no'             => ['required', 'string', 'max:255'],
            'lor_no'             => ['nullable', 'string', 'max:255'],
            'project_no'         => ['nullable', 'string', 'max:255'],
            'project_desc'       => ['required', 'string'],
            'nd_state'           => ['required', 'in:ND_TRG,ND_PHG,ND_KEL'],
            'node_id'            => ['required', 'exists:nodes,id'],
            'self_applied_by_tm' => ['nullable', 'boolean'],
            'payment_to_pbt'     => ['nullable', 'in:charged,waived,not_required'],
            'company_id'         => ['nullable', 'exists:companies,id'],
            'remarks'            => ['nullable', 'string'],
        ];
    }
}
