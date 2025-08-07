<?php

namespace Database\Seeders;

use App\Models\RecruitmentRequest;
use App\Models\Department;
use App\Models\Position;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecruitmentRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get existing departments, positions, and projects
        $departments = Department::all();
        $positions = Position::all();
        $projects = Project::all();

        if ($departments->isEmpty() || $positions->isEmpty() || $projects->isEmpty()) {
            $this->command->error('Please run DepartmentSeeder, PositionSeeder, and ProjectSeeder first!');
            return;
        }

        $employmentTypes = ['pkwtt', 'pkwt', 'harian', 'magang'];
        $requestReasons = ['replacement_resign', 'replacement_promotion', 'additional_workplan', 'other'];
        $statuses = ['approved']; // All approved as requested

        $otherReasons = [
            'Ekspansi tim development',
            'Proyek baru membutuhkan tenaga ahli',
            'Peningkatan kapasitas tim',
            'Kebutuhan skill baru untuk proyek',
            'Tim baru untuk divisi IT',
            'Ekspansi bisnis membutuhkan SDM',
            'Proyek maintenance membutuhkan tenaga'
        ];

        $jobDescriptions = [
            'Bertanggung jawab atas pengembangan aplikasi web menggunakan teknologi modern',
            'Melakukan analisis sistem dan memberikan rekomendasi perbaikan',
            'Mengelola proyek IT dari awal hingga selesai sesuai timeline',
            'Melakukan analisis bisnis dan mengidentifikasi kebutuhan sistem',
            'Melakukan testing dan quality assurance untuk memastikan kualitas produk',
            'Mendesain user interface dan user experience yang user-friendly',
            'Mengelola infrastruktur dan deployment aplikasi',
            'Mengelola database dan memastikan performa optimal',
            'Mengelola jaringan dan keamanan sistem',
            'Memberikan dukungan teknis kepada pengguna sistem'
        ];

        $requiredSkills = [
            'Minimal S1 Teknik Informatika atau bidang terkait',
            'Pengalaman minimal 2 tahun di bidang yang sama',
            'Menguasai bahasa pemrograman PHP, JavaScript, dan SQL',
            'Memiliki kemampuan analisis dan problem solving yang baik',
            'Dapat bekerja dalam tim dan memiliki komunikasi yang baik',
            'Menguasai framework Laravel dan Vue.js',
            'Memiliki pengalaman dengan database MySQL dan PostgreSQL',
            'Menguasai tools development seperti Git, Docker',
            'Memiliki sertifikasi IT yang relevan',
            'Dapat bekerja di bawah tekanan dan deadline'
        ];

        for ($i = 1; $i <= 50; $i++) {
            $department = $departments->random();
            $position = $positions->random();
            $project = $projects->random();

            $requestNumber = 'FPTK-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            $requestReason = $requestReasons[array_rand($requestReasons)];
            $otherReason = $requestReason === 'other' ? $otherReasons[array_rand($otherReasons)] : null;

            RecruitmentRequest::create([
                'id' => Str::uuid(),
                'request_number' => $requestNumber,
                'department_id' => $department->id,
                'position_id' => $position->id,
                'project_id' => $project->id,
                'level_id' => rand(1, 5), // Assuming levels 1-5 exist
                'required_qty' => rand(1, 5),
                'required_date' => now()->addDays(rand(30, 90)),
                'employment_type' => $employmentTypes[array_rand($employmentTypes)],
                'request_reason' => $requestReason,
                'other_reason' => $otherReason,
                'job_description' => $jobDescriptions[array_rand($jobDescriptions)],
                'required_gender' => ['male', 'female', 'any'][array_rand(['male', 'female', 'any'])],
                'required_age_min' => rand(20, 25),
                'required_age_max' => rand(35, 45),
                'required_marital_status' => ['single', 'married', 'any'][array_rand(['single', 'married', 'any'])],
                'required_education' => 'Minimal S1 Teknik Informatika atau bidang terkait',
                'required_skills' => $requiredSkills[array_rand($requiredSkills)],
                'required_experience' => 'Pengalaman minimal ' . rand(1, 5) . ' tahun di bidang yang sama',
                'required_physical' => 'Sehat jasmani dan rohani',
                'required_mental' => 'Dapat bekerja dalam tim dan memiliki komunikasi yang baik',
                'other_requirements' => 'Dapat bekerja di bawah tekanan dan deadline',
                'created_by' => rand(1, 4),
                'status' => $statuses[array_rand($statuses)],
                'positions_filled' => 0,
                'submit_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now()->subDays(rand(0, 30))
            ]);
        }
    }
}
