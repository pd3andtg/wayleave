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
            'nd_state'           => ['required', 'in:ND_JS,ND_JU,ND_KD_PL,ND_KEL,ND_KL,ND_MK,ND_MSC,ND_NS,ND_PG,ND_PHG,ND_PJ,ND_PRK,ND_SABAH,ND_SARAWAK,ND_SB,ND_ST,ND_TRG,NO_JS,NO_JU,NO_KD_PL,NO_KEL,NO_KL,NO_MK,NO_MSC,NO_NS,NO_PG,NO_PHG,NO_PJ,NO_PRK,NO_SABAH,NO_SARAWAK,NO_SB,NO_ST,NO_TRG'],
            'node_id'            => ['required', 'exists:nodes,id'],
            'application_date'   => ['nullable', 'date'],
            'self_applied_by_tm' => ['nullable', 'boolean'],
            'payment_to_pbt'     => ['nullable', 'in:charged,waived,not_required'],
            'company_id'         => ['nullable', 'exists:companies,id'],
            'remarks'            => ['nullable', 'string'],
        ];
    }
}
