<?php

namespace Database\Seeders;

use App\Models\Religion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReligionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Religion::create([
            'religion_name' => 'Islam',
            'religion_status' => '1',
        ]);
        Religion::create([
            'religion_name' => 'Kristen',
            'religion_status' => '1',
        ]);
        Religion::create([
            'religion_name' => 'Katolik',
            'religion_status' => '1',
        ]);
        Religion::create([
            'religion_name' => 'Hindu',
            'religion_status' => '1',
        ]);
        Religion::create([
            'religion_name' => 'Budha',
            'religion_status' => '1',
        ]);
        Religion::create([
            'religion_name' => 'Konghucu',
            'religion_status' => '1',
        ]);
    }
}
