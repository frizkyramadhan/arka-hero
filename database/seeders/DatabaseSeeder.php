<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@arka.co.id',
            'level' => 'superadmin',
            'user_status' => 1,
            'password' => Hash::make('admin'),
        ]);
    }
}
