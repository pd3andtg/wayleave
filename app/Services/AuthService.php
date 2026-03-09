<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Handles user registration logic including role assignment.
// Role is determined by company_selection from the register form:
//   - 'tmtech'     → officer role, unit_id set, no company_id
//   - company ID   → contractor role, company_id set, no unit_id
class AuthService
{
    public function registerUser(array $data): User
    {
        $isTmTech = $data['company_selection'] === 'tmtech';

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'id_number'  => $data['id_number'],
            'unit_id'    => $isTmTech ? $data['unit_id'] : null,
            'company_id' => $isTmTech ? null : $data['company_selection'],
        ]);

        // Assign role based on company selection.
        // Officers belong to TM Tech. Contractors belong to an approved company.
        $user->assignRole($isTmTech ? 'officer' : 'contractor');

        return $user;
    }
}
