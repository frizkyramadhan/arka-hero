<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $grades = [
            ['name' => 'Advance'],
            ['name' => 'Senior'],
            ['name' => 'Major'],
            ['name' => 'General'],
            ['name' => 'Deputy'],
        ];

        foreach ($grades as $grade) {
            Grade::create($grade);
        }

        $levels = [
            ['name' => 'Non Staff-Non Skill'],
            ['name' => 'Foreman/Officer'],
            ['name' => 'Supervisor'],
            ['name' => 'Superintendent'],
            ['name' => 'Manager'],
            ['name' => 'Director'],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }
    }
}
