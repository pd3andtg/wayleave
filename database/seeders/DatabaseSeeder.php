<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Node;
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

        // ── Nodes (TM Nodes — Admin can add more via UI) ───────────────────────
        $nodeData = [
            ['acronym' => 'KT',  'full_name' => 'Kota Tinggi'],
            ['acronym' => 'KBR', 'full_name' => 'Kota Bharu'],
            ['acronym' => 'TRG', 'full_name' => 'Terengganu'],
            ['acronym' => 'KTN', 'full_name' => 'Kelantan'],
            ['acronym' => 'PHG', 'full_name' => 'Pahang'],
            ['acronym' => 'KL',  'full_name' => 'Kuala Lumpur'],
            ['acronym' => 'PJ',  'full_name' => 'Petaling Jaya'],
            ['acronym' => 'SBH', 'full_name' => 'Sabah'],
            ['acronym' => 'SWK', 'full_name' => 'Sarawak'],
            ['acronym' => 'PRK', 'full_name' => 'Perak'],
            ['acronym' => 'SLG', 'full_name' => 'Selangor'],
            ['acronym' => 'JHR', 'full_name' => 'Johor'],
            ['acronym' => 'MLK', 'full_name' => 'Melaka'],
            ['acronym' => 'NSN', 'full_name' => 'Negeri Sembilan'],
            ['acronym' => 'PNG', 'full_name' => 'Pulau Pinang'],
            ['acronym' => 'KDH', 'full_name' => 'Kedah'],
            ['acronym' => 'PLS', 'full_name' => 'Perlis'],
        ];

        foreach ($nodeData as $node) {
            Node::firstOrCreate(['acronym' => $node['acronym']], ['full_name' => $node['full_name']]);
        }

        // ── TM Company (special record — used for self-applied projects) ───────
        // Must exist in companies table so officer/admin can set self_applied_by_tm = true.
        $tmCompany = Company::firstOrCreate(
            ['name' => 'TM'],
            ['status' => 'approved']
        );

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
            [
                'name'       => 'Admin User',
                'password'   => Hash::make('password'),
                'company_id' => $tmCompany->id,
                'status'     => 'approved',
            ]
        );
        $admin->assignRole($adminRole);

        $officerTrg = User::firstOrCreate(
            ['email' => 'officer_trg@wayleave.test'],
            [
                'name'       => 'Officer TRG',
                'password'   => Hash::make('password'),
                'id_number'  => 'TM001',
                'unit_id'    => $unitTrg->id,
                'company_id' => $tmCompany->id,
                'status'     => 'approved',
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
