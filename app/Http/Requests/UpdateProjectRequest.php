<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validates Section 1 edits on the project detail page.
// Any user who can view the project can edit Section 1 fields.
// payment_to_pbt and application_status changes are included here.
// Officers/admins can also update node_id and self_applied_by_tm.
class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'ref_no'             => ['nullable', 'string', 'max:255'],
            'lor_no'             => ['nullable', 'string', 'max:255'],
            'project_no'         => ['nullable', 'string', 'max:255'],
            'project_desc'       => ['required', 'string'],
            'nd_state'           => ['required', 'in:ND_TRG,ND_PHG,ND_KEL'],
            'node_id'            => ['nullable', 'exists:nodes,id'],
            'application_date'   => ['nullable', 'date'],
            'self_applied_by_tm' => ['nullable', 'boolean'],
            'payment_to_pbt'     => ['nullable', 'in:charged,waived,not_required'],
            'company_id'         => ['nullable', 'exists:companies,id'],
            'remarks'            => ['nullable', 'string'],
        ];
    }
}
