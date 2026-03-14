<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ─────────────────────────────────────────────────────────────
        $adminRole      = Role::firstOrCreate(['name' => 'admin']);
        $officerRole    = Role::firstOrCreate(['name' => 'officer']);
        $contractorRole = Role::firstOrCreate(['name' => 'contractor']);

        // ── Units ─────────────────────────────────────────────────────────────
        $unitTrg = Unit::firstOrCreate(['name' => 'ND TRG']);
                   Unit::firstOrCreate(['name' => 'ND KEL']);
                   Unit::firstOrCreate(['name' => 'ND PHG']);

        // ── Companies (approved, for contractor test accounts) ─────────────────
        $companyA = Company::firstOrCreate(
            ['name' => 'Company A'],
            ['status' => 'approved']
        );
        $companyB = Company::firstOrCreate(
            ['name' => 'Company B'],
            ['status' => 'approved']
        );

        // ── Test Accounts ──────────────────────────────────────────────────────
        // All test accounts use password: "password"
        // All seeded accounts are pre-approved — no manual approval needed for testing.
        $admin = User::firstOrCreate(
            ['email' => 'admin@wayleave.test'],
            ['name' => 'Admin User', 'password' => Hash::make('password'), 'status' => 'approved']
        );
        $admin->assignRole($adminRole);

        $officerTrg = User::firstOrCreate(
            ['email' => 'officer_trg@wayleave.test'],
            [
                'name'      => 'Officer TRG',
                'password'  => Hash::make('password'),
                'id_number' => 'TM001',
                'unit_id'   => $unitTrg->id,
                'status'    => 'approved',
            ]
        );
        $officerTrg->assignRole($officerRole);

        $contractorA = User::firstOrCreate(
            ['email' => 'contractor_a@wayleave.test'],
            [
                'name'       => 'Contractor A',
                'password'   => Hash::make('password'),
                'id_number'  => '900101-01-1111',
                'company_id' => $companyA->id,
                'status'     => 'approved',
            ]
        );
        $contractorA->assignRole($contractorRole);

        $contractorB = User::firstOrCreate(
            ['email' => 'contractor_b@wayleave.test'],
            [
                'name'       => 'Contractor B',
                'password'   => Hash::make('password'),
                'id_number'  => '900202-02-2222',
                'company_id' => $companyB->id,
                'status'     => 'approved',
            ]
        );
        $contractorB->assignRole($contractorRole);
    }
}
