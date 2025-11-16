<?php

namespace Database\Seeders;

use App\Models\RecruitmentCandidate;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        // Get mechanic/technical positions to ensure some candidates apply for them
        $mechanicPositions = $allPositions->filter(function ($position) {
            $positionName = strtolower($position->position_name);
            return str_contains($positionName, 'mechanic') ||
                str_contains($positionName, 'teknik') ||
                str_contains($positionName, 'engineer') ||
                str_contains($positionName, 'operator');
        });

        $educationLevels = ['SMA/SMK', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'];
        $globalStatuses = ['available', 'hired', 'rejected', 'withdrawn'];
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Makassar', 'Palembang', 'Yogyakarta'];

        $candidatesData = [
            [
                'name' => 'Rizki Maulana',
                'email' => 'rizki.maulana',
                'phone_prefix' => '0812',
                'education' => 'S1',
                'experience' => 5,
                'city' => 'Jakarta',
                'is_mechanic' => true,
                'remarks' => 'Memiliki pengalaman 5 tahun sebagai mekanik heavy equipment. Terampil dalam troubleshooting dan maintenance.'
            ],
            [
                'name' => 'Nurul Fitriani',
                'email' => 'nurul.fitriani',
                'phone_prefix' => '0813',
                'education' => 'D3',
                'experience' => 3,
                'city' => 'Bandung',
                'is_mechanic' => false,
                'remarks' => 'Fresh graduate dengan semangat tinggi. Aktif dalam organisasi kampus.'
            ],
            [
                'name' => 'Agung Wijaya',
                'email' => 'agung.wijaya',
                'phone_prefix' => '0814',
                'education' => 'SMA/SMK',
                'experience' => 8,
                'city' => 'Surabaya',
                'is_mechanic' => true,
                'remarks' => 'Pengalaman luas di bidang maintenance dan repair. Memiliki sertifikasi kompetensi.'
            ],
            [
                'name' => 'Putri Lestari',
                'email' => 'putri.lestari',
                'phone_prefix' => '0815',
                'education' => 'S1',
                'experience' => 2,
                'city' => 'Jakarta',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan komunikasi yang baik. Berpengalaman dalam administrasi.'
            ],
            [
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi.kurniawan',
                'phone_prefix' => '0816',
                'education' => 'D3',
                'experience' => 6,
                'city' => 'Medan',
                'is_mechanic' => true,
                'remarks' => 'Spesialisasi dalam hydraulic system dan electrical troubleshooting.'
            ],
            [
                'name' => 'Sinta Rahmawati',
                'email' => 'sinta.rahmawati',
                'phone_prefix' => '0817',
                'education' => 'S1',
                'experience' => 4,
                'city' => 'Semarang',
                'is_mechanic' => false,
                'remarks' => 'Memiliki kemampuan analisis yang baik. Berpengalaman dalam quality control.'
            ],
            [
                'name' => 'Hendra Saputra',
                'email' => 'hendra.saputra',
                'phone_prefix' => '0818',
                'education' => 'SMA/SMK',
                'experience' => 10,
                'city' => 'Makassar',
                'is_mechanic' => true,
                'remarks' => 'Senior mechanic dengan pengalaman di berbagai proyek konstruksi dan mining.'
            ],
            [
                'name' => 'Rina Wati',
                'email' => 'rina.wati',
                'phone_prefix' => '0819',
                'education' => 'S1',
                'experience' => 1,
                'city' => 'Jakarta',
                'is_mechanic' => false,
                'remarks' => 'Fresh graduate dengan IPK tinggi. Aktif dalam kegiatan ekstrakurikuler.'
            ],
            [
                'name' => 'Fajar Nugroho',
                'email' => 'fajar.nugroho',
                'phone_prefix' => '0821',
                'education' => 'D3',
                'experience' => 7,
                'city' => 'Bandung',
                'is_mechanic' => true,
                'remarks' => 'Expert dalam engine repair dan preventive maintenance. Memiliki banyak sertifikasi.'
            ],
            [
                'name' => 'Lina Marlina',
                'email' => 'lina.marlina',
                'phone_prefix' => '0822',
                'education' => 'S1',
                'experience' => 3,
                'city' => 'Surabaya',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan leadership yang baik. Berpengalaman sebagai team leader.'
            ],
            [
                'name' => 'Yuni Astuti',
                'email' => 'yuni.astuti',
                'phone_prefix' => '0823',
                'education' => 'SMA/SMK',
                'experience' => 5,
                'city' => 'Palembang',
                'is_mechanic' => false,
                'remarks' => 'Berpengalaman dalam customer service dan administrasi. Ramah dan komunikatif.'
            ],
            [
                'name' => 'Aris Setiawan',
                'email' => 'aris.setiawan',
                'phone_prefix' => '0824',
                'education' => 'S1',
                'experience' => 4,
                'city' => 'Yogyakarta',
                'is_mechanic' => true,
                'remarks' => 'Spesialis dalam welding dan fabrication. Memiliki sertifikasi pengelasan.'
            ],
            [
                'name' => 'Desi Ratnasari',
                'email' => 'desi.ratnasari',
                'phone_prefix' => '0825',
                'education' => 'D3',
                'experience' => 2,
                'city' => 'Jakarta',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan multitasking yang baik. Detail-oriented.'
            ],
            [
                'name' => 'Bambang Hermawan',
                'email' => 'bambang.hermawan',
                'phone_prefix' => '0826',
                'education' => 'S1',
                'experience' => 6,
                'city' => 'Bandung',
                'is_mechanic' => true,
                'remarks' => 'Expert dalam diagnostic dan repair heavy equipment. Memiliki pengalaman di proyek besar.'
            ],
            [
                'name' => 'Mira Handayani',
                'email' => 'mira.handayani',
                'phone_prefix' => '0827',
                'education' => 'S1',
                'experience' => 3,
                'city' => 'Surabaya',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan problem solving yang baik. Berpengalaman dalam project management.'
            ],
            [
                'name' => 'Joko Susilo',
                'email' => 'joko.susilo',
                'phone_prefix' => '0828',
                'education' => 'SMA/SMK',
                'experience' => 9,
                'city' => 'Medan',
                'is_mechanic' => true,
                'remarks' => 'Senior technician dengan pengalaman luas. Memiliki kemampuan training dan mentoring.'
            ],
            [
                'name' => 'Ratna Dewi',
                'email' => 'ratna.dewi',
                'phone_prefix' => '0829',
                'education' => 'S1',
                'experience' => 2,
                'city' => 'Jakarta',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan analisis data yang baik. Familiar dengan sistem komputer.'
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi',
                'phone_prefix' => '0831',
                'education' => 'D3',
                'experience' => 5,
                'city' => 'Semarang',
                'is_mechanic' => true,
                'remarks' => 'Spesialis dalam electrical system dan automation. Memahami PLC dan control system.'
            ],
            [
                'name' => 'Winda Sari',
                'email' => 'winda.sari',
                'phone_prefix' => '0832',
                'education' => 'S1',
                'experience' => 4,
                'city' => 'Makassar',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan komunikasi yang sangat baik. Berpengalaman dalam public relations.'
            ],
            [
                'name' => 'Rudi Hartono',
                'email' => 'rudi.hartono',
                'phone_prefix' => '0833',
                'education' => 'SMA/SMK',
                'experience' => 7,
                'city' => 'Palembang',
                'is_mechanic' => true,
                'remarks' => 'Expert dalam hydraulic dan pneumatic system. Memiliki banyak pengalaman di field.'
            ],
            [
                'name' => 'Eka Sari',
                'email' => 'eka.sari',
                'phone_prefix' => '0834',
                'education' => 'S1',
                'experience' => 1,
                'city' => 'Yogyakarta',
                'is_mechanic' => false,
                'remarks' => 'Fresh graduate dengan motivasi tinggi. Cepat belajar dan adaptif.'
            ],
            [
                'name' => 'Sari Puspita',
                'email' => 'sari.puspita',
                'phone_prefix' => '0835',
                'education' => 'D3',
                'experience' => 3,
                'city' => 'Jakarta',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan organisasi yang baik. Berpengalaman dalam event management.'
            ],
            [
                'name' => 'Asep Supriadi',
                'email' => 'asep.supriadi',
                'phone_prefix' => '0836',
                'education' => 'S1',
                'experience' => 5,
                'city' => 'Bandung',
                'is_mechanic' => true,
                'remarks' => 'Spesialis dalam engine overhaul dan rebuild. Memiliki sertifikasi dari berbagai brand.'
            ],
            [
                'name' => 'Dina Kartika',
                'email' => 'dina.kartika',
                'phone_prefix' => '0837',
                'education' => 'S1',
                'experience' => 2,
                'city' => 'Surabaya',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan bahasa Inggris yang baik. Berpengalaman dalam komunikasi internasional.'
            ],
            [
                'name' => 'Toni Gunawan',
                'email' => 'toni.gunawan',
                'phone_prefix' => '0838',
                'education' => 'SMA/SMK',
                'experience' => 6,
                'city' => 'Medan',
                'is_mechanic' => true,
                'remarks' => 'Expert dalam transmission dan drivetrain system. Memiliki pengalaman di berbagai jenis kendaraan.'
            ],
            [
                'name' => 'Indah Permata',
                'email' => 'indah.permata',
                'phone_prefix' => '0839',
                'education' => 'S1',
                'experience' => 3,
                'city' => 'Jakarta',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan presentasi yang baik. Berpengalaman dalam training dan development.'
            ],
            [
                'name' => 'Rizki Pratama',
                'email' => 'rizki.pratama',
                'phone_prefix' => '0851',
                'education' => 'D3',
                'experience' => 4,
                'city' => 'Semarang',
                'is_mechanic' => true,
                'remarks' => 'Spesialis dalam air conditioning dan cooling system. Memiliki sertifikasi HVAC.'
            ],
            [
                'name' => 'Sari Indira',
                'email' => 'sari.indira',
                'phone_prefix' => '0852',
                'education' => 'S1',
                'experience' => 2,
                'city' => 'Makassar',
                'is_mechanic' => false,
                'remarks' => 'Kandidat dengan kemampuan analisis yang tajam. Berpengalaman dalam data analysis.'
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi.wijaya',
                'phone_prefix' => '0853',
                'education' => 'SMA/SMK',
                'experience' => 8,
                'city' => 'Palembang',
                'is_mechanic' => true,
                'remarks' => 'Senior mechanic dengan pengalaman di berbagai industri. Memiliki kemampuan leadership.'
            ],
            [
                'name' => 'Nina Sari',
                'email' => 'nina.sari',
                'phone_prefix' => '0854',
                'education' => 'S1',
                'experience' => 1,
                'city' => 'Yogyakarta',
                'is_mechanic' => false,
                'remarks' => 'Fresh graduate dengan IPK 3.8. Aktif dalam kegiatan volunteer dan organisasi.'
            ]
        ];

        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'ymail.com'];
        $year = date('Y');

        // Get the last candidate number to avoid duplicates
        $lastCandidate = RecruitmentCandidate::orderBy('candidate_number', 'desc')->first();
        $sequence = 21; // Default start from 0021

        if ($lastCandidate && preg_match('/CAN-\d+-(\d+)/', $lastCandidate->candidate_number, $matches)) {
            $lastSequence = (int) $matches[1];
            $sequence = $lastSequence + 1; // Start from next available number
        }

        foreach ($candidatesData as $index => $data) {
            $email = strtolower(str_replace(' ', '.', $data['email'])) . '@' . $domains[array_rand($domains)];
            $phone = $data['phone_prefix'] . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            $candidateNumber = 'CAN-' . $year . '-' . str_pad($sequence++, 4, '0', STR_PAD_LEFT);

            // Determine position
            if ($data['is_mechanic'] && $mechanicPositions->isNotEmpty()) {
                $appliedPosition = $mechanicPositions->random()->position_name;
            } else {
                $appliedPosition = $allPositions->random()->position_name;
            }

            // All candidates have 'available' status
            $status = 'available';

            // Random created date within last year
            $createdAt = Carbon::now()->subDays(rand(1, 365));
            $updatedAt = $createdAt->copy()->addDays(rand(0, 30));

            RecruitmentCandidate::create([
                'id' => Str::uuid(),
                'candidate_number' => $candidateNumber,
                'fullname' => $data['name'],
                'email' => $email,
                'phone' => $phone,
                'education_level' => $data['education'],
                'position_applied' => $appliedPosition,
                'experience_years' => $data['experience'],
                'global_status' => $status,
                'remarks' => $data['remarks'],
                'created_by' => rand(1, 4),
                'updated_by' => rand(1, 4),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt
            ]);
        }

        $this->command->info('Successfully seeded ' . count($candidatesData) . ' recruitment candidates!');
    }
}
