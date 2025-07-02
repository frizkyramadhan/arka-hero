<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LetterCategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'category_code' => 'A',
                'category_name' => 'Surat Eksternal',
                'description' => 'Surat untuk pihak eksternal perusahaan',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'B',
                'category_name' => 'Surat Internal',
                'description' => 'Surat untuk internal perusahaan',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'PKWT',
                'category_name' => 'Surat PKWT',
                'description' => 'Perjanjian Kerja Waktu Tertentu',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'PAR',
                'category_name' => 'Surat PAR',
                'description' => 'Personal Action Request',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'CRTE',
                'category_name' => 'Surat Pengalaman Kerja',
                'description' => 'Certificate of Employment',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'SKPK',
                'category_name' => 'Surat Ket. Pengalaman Kerja',
                'description' => 'Surat Keterangan Pengalaman Kerja',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'MEMO',
                'category_name' => 'Surat Memo',
                'description' => 'Internal Memo',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'FPTK',
                'category_name' => 'Form Permintaan Tenaga Kerja',
                'description' => 'FPTK',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'FR',
                'category_name' => 'Form Request Tiket',
                'description' => 'Permintaan Tiket Perjalanan',
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('letter_categories')->insert($categories);
    }
}
