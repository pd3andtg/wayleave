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
        $unitTrg =  Unit::firstOrCreate(['name' => 'ND JS']);
                    Unit::firstOrCreate(['name' => 'ND JU']);
                    Unit::firstOrCreate(['name' => 'ND KD/PL']);
                    Unit::firstOrCreate(['name' => 'ND KEL']);
                    Unit::firstOrCreate(['name' => 'ND KL']);
                    Unit::firstOrCreate(['name' => 'ND MK']);
                    Unit::firstOrCreate(['name' => 'ND MSC']);
                    Unit::firstOrCreate(['name' => 'ND NS']);
                    Unit::firstOrCreate(['name' => 'ND PG']);
                    Unit::firstOrCreate(['name' => 'ND PHG']);
                    Unit::firstOrCreate(['name' => 'ND PJ']);
                    Unit::firstOrCreate(['name' => 'ND PRK']);
                    Unit::firstOrCreate(['name' => 'ND SABAH']);
                    Unit::firstOrCreate(['name' => 'ND SARAWAK']);
                    Unit::firstOrCreate(['name' => 'ND SB']);
                    Unit::firstOrCreate(['name' => 'ND ST']);
                    Unit::firstOrCreate(['name' => 'ND TRG']);
                    Unit::firstOrCreate(['name' => 'NO JS']);
                    Unit::firstOrCreate(['name' => 'NO JU']);
                    Unit::firstOrCreate(['name' => 'NO KD/PL']);
                    Unit::firstOrCreate(['name' => 'NO KEL']);
                    Unit::firstOrCreate(['name' => 'NO KL']);
                    Unit::firstOrCreate(['name' => 'NO MK']);
                    Unit::firstOrCreate(['name' => 'NO MSC']);
                    Unit::firstOrCreate(['name' => 'NO NS']);
                    Unit::firstOrCreate(['name' => 'NO PG']);
                    Unit::firstOrCreate(['name' => 'NO PHG']);
                    Unit::firstOrCreate(['name' => 'NO PJ']);
                    Unit::firstOrCreate(['name' => 'NO PRK']);
                    Unit::firstOrCreate(['name' => 'NO SABAH']);
                    Unit::firstOrCreate(['name' => 'NO SARAWAK']);
                    Unit::firstOrCreate(['name' => 'NO SB']);
                    Unit::firstOrCreate(['name' => 'NO ST']);
                    Unit::firstOrCreate(['name' => 'NO TRG']);

        // ── Nodes (TM Nodes — Admin can add more via UI) ───────────────────────
        // Truncate and re-seed so the list stays exact on migrate:fresh.
        Node::truncate();

        $nodeData = [
            ['acronym' => 'AJH',  'full_name' => 'AIR JERNIH',                  'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'AJI',  'full_name' => 'AJIL',                         'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'AMS',  'full_name' => 'AL-MUKTAFI BILLAH SHAH',       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'BBG',  'full_name' => 'BUKIT BADING',                 'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'BKI',  'full_name' => 'BUKIT BESI',                   'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'BPI',  'full_name' => 'BANDAR PERMAISURI',            'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'BRT',  'full_name' => 'BATU RAKIT',                   'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'BUP',  'full_name' => 'BKT PAYONG',                   'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'CDG',  'full_name' => 'CHENDERING',                   'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'CEN',  'full_name' => 'CHENEH',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'CKI',  'full_name' => 'CHUKAI',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'CRU',  'full_name' => 'CHERANG RUKU',                 'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'DN',   'full_name' => 'DUNGUN',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'GBD',  'full_name' => 'GONG BADAK',                   'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'GGA',  'full_name' => 'GELIGA',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'JBI',  'full_name' => 'JABI',                         'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'JH',   'full_name' => 'JERTIH',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'JRG',  'full_name' => 'FELDA JERANGAU',               'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KBE',  'full_name' => 'KUALA BESUT',                  'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KBR',  'full_name' => 'KUALA BERANG',                 'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KFR',  'full_name' => 'KAMPUNG SAUJANA',              'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KJA',  'full_name' => 'KETENGAH JAYA',                'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KKJ',  'full_name' => 'KG. KUALA JENGAI',             'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KKR',  'full_name' => 'KG. KERUAK & KG. LA',          'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KKT',  'full_name' => 'KAMPONG KUALA TELEMONG',       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KMK',  'full_name' => 'KEMASIK',                      'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KRA',  'full_name' => 'KAMPONG RAJA',                 'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KSH',  'full_name' => 'KG. SHUKUR',                   'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KTH',  'full_name' => 'KERTIH',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'KWA',  'full_name' => 'KAMPUNG WA',                   'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'MA',   'full_name' => 'MARANG',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'MAN',  'full_name' => 'MANIR',                        'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'MER',  'full_name' => 'MERANG',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'MND',  'full_name' => 'KG. MINDA',                    'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'MRC',  'full_name' => 'MERCHANG',                     'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'PGK',  'full_name' => 'PADANG KUBU',                  'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'PHN',  'full_name' => 'PULAU PERHENTIAN',             'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'PK',   'full_name' => 'JALAN SANTONG',                'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'PKGA', 'full_name' => 'PULAU KERENGGA (FTTS)',        'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'PST',  'full_name' => 'PASIR TINGGI',                 'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'PURG', 'full_name' => 'PULAU REDANG(PURG)',           'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'PUG',  'full_name' => 'PULAU REDANG',                 'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'SBI',  'full_name' => 'SRI BANDI',                    'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'SGW',  'full_name' => 'SG GAWI',                      'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'TBAK', 'full_name' => 'TEBAK (FTTS)',                 'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'TG',   'full_name' => 'KUALA TERENGGANU',             'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'TLG',  'full_name' => 'TELOK KALONG',                 'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'TNG',  'full_name' => 'TENANG',                       'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'TOG',  'full_name' => 'SG TONG',                      'nd' => 'TRG', 'state' => 'TERENGGANU'],
            ['acronym' => 'WTI',  'full_name' => 'WAKAF TAPAI',                  'nd' => 'TRG', 'state' => 'TERENGGANU'],
        ];

        foreach ($nodeData as $node) {
            Node::create($node);
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
