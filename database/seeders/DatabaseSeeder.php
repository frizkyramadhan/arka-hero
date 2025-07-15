<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\PositionSeeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\GradeLevelSeeder;
use Database\Seeders\OfficialTravelSeeder;
use Database\Seeders\RecruitmentRolePermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ReligionSeeder::class);
        $this->call(BankSeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(PositionSeeder::class);
        $this->call(RoleAndPermissionSeeder::class);

        User::factory()->create([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@arka.co.id',
            // 'level' => 'superadmin',
            'user_status' => 1,
            'password' => Hash::make('admin'),
        ]);

        User::where('email', 'admin@arka.co.id')->first()->assignRole('administrator');
        $this->call(RecruitmentRolePermissionSeeder::class);

        // Letter numbering system seeders
        $this->call(LetterCategorySeeder::class);
        $this->call(LetterSubjectSeeder::class);

        $this->call(OfficialTravelSeeder::class);

        $this->call(GradeLevelSeeder::class);
    }
}
