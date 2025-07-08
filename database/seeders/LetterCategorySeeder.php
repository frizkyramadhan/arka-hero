<?php

namespace Database\Seeders;

use App\Models\LetterCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class LetterCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'admin@arka.co.id')->first();
        if (!$user) {
            $user = User::factory()->create(['email' => 'admin@arka.co.id', 'name' => 'Administrator']);
        }

        $categories = [
            ['category_code' => 'A', 'category_name' => 'Surat Eksternal', 'description' => 'Surat untuk pihak eksternal', 'numbering_behavior' => 'annual_reset'],
            ['category_code' => 'B', 'category_name' => 'Surat Internal', 'description' => 'Surat untuk internal perusahaan', 'numbering_behavior' => 'annual_reset'],
            ['category_code' => 'PKWT', 'category_name' => 'Surat PKWT', 'description' => 'Perjanjian Kerja Waktu Tertentu', 'numbering_behavior' => 'continuous'],
            ['category_code' => 'PAR', 'category_name' => 'Surat PAR', 'description' => 'Personal Action Request', 'numbering_behavior' => 'continuous'],
            ['category_code' => 'CRTE', 'category_name' => 'Surat Pengalaman Kerja', 'description' => 'Certificate of Employment', 'numbering_behavior' => 'continuous'],
            ['category_code' => 'SKPK', 'category_name' => 'Surat Ket. Pengalaman Kerja', 'description' => 'Surat Keterangan Pengalaman Kerja', 'numbering_behavior' => 'continuous'],
            ['category_code' => 'MEMO', 'category_name' => 'Surat Memo', 'description' => 'Internal Memo', 'numbering_behavior' => 'annual_reset'],
            ['category_code' => 'FPTK', 'category_name' => 'Form Permintaan Tenaga Kerja', 'description' => 'FPTK', 'numbering_behavior' => 'annual_reset'],
            ['category_code' => 'FR', 'category_name' => 'Form Request Tiket', 'description' => 'Permintaan Tiket Perjalanan', 'numbering_behavior' => 'annual_reset'],
        ];

        foreach ($categories as $category) {
            LetterCategory::updateOrCreate(
                ['category_code' => $category['category_code']],
                [
                    'category_name' => $category['category_name'],
                    'description' => $category['description'],
                    'numbering_behavior' => $category['numbering_behavior'],
                    'is_active' => 1,
                    'user_id' => $user->id,
                ]
            );
        }
    }
}
