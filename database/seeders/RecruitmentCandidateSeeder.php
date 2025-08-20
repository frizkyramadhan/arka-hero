<?php

namespace Database\Seeders;

use App\Models\RecruitmentCandidate;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecruitmentCandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get actual positions from database
        $allPositions = Position::where('position_status', 1)->get();

        if ($allPositions->isEmpty()) {
            $this->command->error('No active positions found in database. Please run PositionSeeder first!');
            return;
        }

        // Get mechanic positions to ensure some candidates apply for them
        $mechanicPositions = $allPositions->filter(function ($position) {
            return str_contains(strtolower($position->position_name), 'mechanic');
        });

        $educationLevels = ['SMA/SMK', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'];

        $indonesianNames = [
            'Ahmad Rizki',
            'Siti Nurhaliza',
            'Budi Santoso',
            'Dewi Sartika',
            'Eko Prasetyo',
            'Fatimah Azzahra',
            'Gunawan Setiawan',
            'Hesti Wulandari',
            'Indra Kusuma',
            'Juwita Sari',
            'Kartika Dewi',
            'Lukman Hakim',
            'Maya Indah',
            'Nugraha Pratama',
            'Oktavia Putri',
            'Pandu Wijaya',
            'Qonita Zahra',
            'Raden Mas',
            'Sari Indah',
            'Taufik Hidayat',
            'Umi Kulsum',
            'Vina Safitri',
            'Wahyu Nugroho',
            'Xena Putri',
            'Yoga Pratama',
            'Zahra Amalia',
            'Aditya Ramadhan',
            'Bella Safira',
            'Candra Wijaya',
            'Dinda Permata',
            'Evan Saputra',
            'Fika Nurul',
            'Gita Purnama',
            'Hendra Gunawan',
            'Ika Sari',
            'Joko Widodo',
            'Kartika Sari',
            'Lukas Setiawan',
            'Mira Safitri',
            'Nando Pratama',
            'Oscar Wijaya',
            'Putri Anggraini',
            'Rafi Akbar',
            'Sinta Dewi',
            'Toni Kusuma',
            'Udin Santoso',
            'Vera Safitri',
            'Wawan Setiawan',
            'Yuni Safitri',
            'Zainal Abidin'
        ];

        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'ymail.com'];

        for ($i = 1; $i <= 20; $i++) {
            $name = $indonesianNames[$i - 1];
            $firstName = explode(' ', $name)[0];
            $lastName = explode(' ', $name)[1] ?? '';

            $email = strtolower(str_replace(' ', '.', $name)) . '@' . $domains[array_rand($domains)];
            $phone = '08' . rand(100000000, 999999999);

            $candidateNumber = 'CAN-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            // Ensure at least 6-8 candidates apply for mechanic positions (30-40% of total)
            if ($i <= 8 && $mechanicPositions->isNotEmpty()) {
                $appliedPosition = $mechanicPositions->random()->position_name;
            } else {
                $appliedPosition = $allPositions->random()->position_name;
            }

            RecruitmentCandidate::create([
                'id' => Str::uuid(),
                'candidate_number' => $candidateNumber,
                'fullname' => $name,
                'email' => $email,
                'phone' => $phone,
                'education_level' => $educationLevels[array_rand($educationLevels)],
                'position_applied' => $appliedPosition,
                'experience_years' => rand(0, 15),
                'global_status' => 'available',
                'remarks' => rand(0, 1) ? 'Kandidat potensial dengan pengalaman yang relevan.' : null,
                'created_by' => rand(1, 4),
                'updated_by' => rand(1, 4),
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now()->subDays(rand(0, 30))
            ]);
        }
    }
}
