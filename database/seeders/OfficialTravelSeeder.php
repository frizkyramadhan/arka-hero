<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Transportation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OfficialTravelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Transportation::create([
            'transportation_name' => 'Company Car',
            'transportation_status' => 1,
        ]);
        Transportation::create([
            'transportation_name' => 'Public Transport',
            'transportation_status' => 1,
        ]);

        Accommodation::create([
            'accommodation_name' => 'Hotel',
            'accommodation_status' => 1,
        ]);
        Accommodation::create([
            'accommodation_name' => 'Site',
            'accommodation_status' => 1,
        ]);
        Accommodation::create([
            'accommodation_name' => 'Mess',
            'accommodation_status' => 1,
        ]);
    }
}
