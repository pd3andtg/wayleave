<?php

namespace Database\Seeders;

// Seeds 11 dummy projects under Company A for pagination testing.
// Run with: php artisan db:seed --class=TestProjectSeeder

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestProjectSeeder extends Seeder
{
    public function run(): void
    {
        $companyA  = Company::where('name', 'Company A')->first();
        $createdBy = User::where('email', 'contractor_a@wayleave.test')->first();

        $projects = [
            ['ref_no' => 'KUTT123/001/0005', 'project_desc' => 'Fiber Cable Installation - Jalan Tok Lam',         'nd_state' => 'ND_TRG'],
            ['ref_no' => 'KUTT123/001/0006', 'project_desc' => 'Underground Ducting Works - Taman Batu Buruk',     'nd_state' => 'ND_TRG'],
            ['ref_no' => 'KUTT123/001/0007', 'project_desc' => 'Wayleave Submission - Jalan Sultan Zainal Abidin', 'nd_state' => 'ND_TRG'],
            ['ref_no' => 'KUTT123/002/0001', 'project_desc' => 'New Cable Route - Pekan Cherating',                'nd_state' => 'ND_PHG'],
            ['ref_no' => 'KUTT123/002/0002', 'project_desc' => 'Overhead Line Relocation - Kuantan',               'nd_state' => 'ND_PHG'],
            ['ref_no' => 'KUTT123/002/0003', 'project_desc' => 'Road Crossing Permit - Lebuhraya EKVE',            'nd_state' => 'ND_PHG'],
            ['ref_no' => 'KUTT123/003/0001', 'project_desc' => 'Fiber Backbone Extension - Kota Bharu',            'nd_state' => 'ND_KEL'],
            ['ref_no' => 'KUTT123/003/0002', 'project_desc' => 'Tower Foundation Works - Pasir Mas',               'nd_state' => 'ND_KEL'],
            ['ref_no' => 'KUTT123/003/0003', 'project_desc' => 'Cable Trenching - Tumpat Industrial Area',         'nd_state' => 'ND_KEL'],
            ['ref_no' => 'KUTT123/003/0004', 'project_desc' => 'Wayleave Renewal - Jalan Hamzah',                  'nd_state' => 'ND_KEL'],
            ['ref_no' => 'KUTT123/003/0005', 'project_desc' => 'New Substation Permit - Rantau Panjang',           'nd_state' => 'ND_KEL'],
        ];

        foreach ($projects as $data) {
            Project::create([
                'ref_no'             => $data['ref_no'],
                'project_desc'       => $data['project_desc'],
                'nd_state'           => $data['nd_state'],
                'pic_name'           => $createdBy->name,
                'payment_to_kutt'    => 'charged',
                'application_status' => 'in_progress',
                'status'             => 'outstanding',
                'company_id'         => $companyA->id,
                'created_by'         => $createdBy->id,
            ]);
        }
    }
}
