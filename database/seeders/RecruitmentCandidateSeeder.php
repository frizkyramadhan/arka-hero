<?php

namespace Database\Seeders;

use App\Models\RecruitmentCandidate;
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
        $positions = [
            'Software Developer',
            'System Analyst',
            'Project Manager',
            'Business Analyst',
            'Quality Assurance',
            'UI/UX Designer',
            'DevOps Engineer',
            'Database Administrator',
            'Network Engineer',
            'IT Support',
            'Product Manager',
            'Scrum Master',
            'Technical Writer',
            'Data Analyst',
            'Security Engineer',
            'Mobile Developer',
            'Frontend Developer',
            'Backend Developer',
            'Full Stack Developer',
            'Cloud Engineer'
        ];

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

        for ($i = 1; $i <= 50; $i++) {
            $name = $indonesianNames[$i - 1];
            $firstName = explode(' ', $name)[0];
            $lastName = explode(' ', $name)[1] ?? '';

            $email = strtolower(str_replace(' ', '.', $name)) . '@' . $domains[array_rand($domains)];
            $phone = '08' . rand(100000000, 999999999);

            $candidateNumber = 'CAN-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            RecruitmentCandidate::create([
                'id' => Str::uuid(),
                'candidate_number' => $candidateNumber,
                'fullname' => $name,
                'email' => $email,
                'phone' => $phone,
                'education_level' => $educationLevels[array_rand($educationLevels)],
                'position_applied' => $positions[array_rand($positions)],
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
