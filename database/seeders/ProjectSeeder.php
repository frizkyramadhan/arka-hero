<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Project::create([
            'project_code' => '000H',
            'project_name' => 'HO - Balikpapan',
            'project_location' => 'Balikpapan',
            'bowheer' => 'Arka',
            'project_status' => 1,
        ]);
        Project::create([
            'project_code' => '001H',
            'project_name' => 'BO - Jakarta',
            'project_location' => 'Jakarta',
            'bowheer' => 'Arka',
            'project_status' => 1,
        ]);
        Project::create([
            'project_code' => '008C',
            'project_name' => 'Tanito - Senoni',
            'project_location' => 'Senoni',
            'bowheer' => 'Tanito Harum',
            'project_status' => 1,
        ]);
        Project::create([
            'project_code' => '015C',
            'project_name' => 'PPC - Penajam',
            'project_location' => 'Penajam',
            'bowheer' => 'PPC',
            'project_status' => 0,
        ]);
        Project::create([
            'project_code' => '017C',
            'project_name' => 'KPUC - Malinau',
            'project_location' => 'Malinau',
            'bowheer' => 'KPUC',
            'project_status' => 1,
        ]);
        Project::create([
            'project_code' => '021C',
            'project_name' => 'SBI - Bogor',
            'project_location' => 'Bogor',
            'bowheer' => 'SBI',
            'project_status' => 1,
        ]);
        Project::create([
            'project_code' => '022C',
            'project_name' => 'GPK - Melak',
            'project_location' => 'Melak',
            'bowheer' => 'GPK',
            'project_status' => 1,
        ]);
        Project::create([
            'project_code' => '023C',
            'project_name' => 'BEK - Muara Lawa',
            'project_location' => 'Muara Lawa',
            'bowheer' => 'BEK',
            'project_status' => 0,
        ]);
        Project::create([
            'project_code' => 'APS',
            'project_name' => 'APS - Kariangau',
            'project_location' => 'Kariangau',
            'bowheer' => 'Arka',
            'project_status' => 1,
        ]);
    }
}
