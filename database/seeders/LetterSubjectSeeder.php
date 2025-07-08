<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LetterSubjectSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        $categories = DB::table('letter_categories')->pluck('id', 'category_code');

        $subjects = [
            // Surat Eksternal (A)
            [
                'subject_name' => 'Surat Pengantar MCU',
                'letter_category_id' => $categories['A'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Penawaran Kerja',
                'letter_category_id' => $categories['A'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Keterangan',
                'letter_category_id' => $categories['A'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Penonaktifan BPJS',
                'letter_category_id' => $categories['A'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Surat Internal (B)
            [
                'subject_name' => 'Surat Pengantar Karyawan',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'LOT',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Perjanjian Ikatan Dinas',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Pemberitahuan Daily',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Perjanjian Ikatan Dinas OJT',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Berita Acara',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Pengajuan Cuti Khusus',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Internal Memo',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Permohonan Mutasi',
                'letter_category_id' => $categories['B'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PKWT
            [
                'subject_name' => 'Perjanjian Kerja Waktu Tertentu - PKWT I',
                'letter_category_id' => $categories['PKWT'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Perjanjian Kerja Waktu Tertentu - PKWT II',
                'letter_category_id' => $categories['PKWT'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Perjanjian Kerja Waktu Tertentu - PKWT III',
                'letter_category_id' => $categories['PKWT'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PAR
            [
                'subject_name' => 'Personal Action Request - New Hire',
                'letter_category_id' => $categories['PAR'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Personal Action Request - Promosi',
                'letter_category_id' => $categories['PAR'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Personal Action Request - Mutasi',
                'letter_category_id' => $categories['PAR'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Personal Action Request - Demosi',
                'letter_category_id' => $categories['PAR'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Certificate of Employment
            [
                'subject_name' => 'Surat Pengalaman Kerja',
                'letter_category_id' => $categories['CRTE'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // SKPK
            [
                'subject_name' => 'Surat Keterangan Pengalaman Kerja',
                'letter_category_id' => $categories['SKPK'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Memo
            [
                'subject_name' => 'Memorandum Internal',
                'letter_category_id' => $categories['MEMO'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // FPTK
            [
                'subject_name' => 'Tenaga Kerja Karyawan',
                'letter_category_id' => $categories['FPTK'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Tenaga Kerja Magang',
                'letter_category_id' => $categories['FPTK'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Form Request
            [
                'subject_name' => 'Permintaan Tiket Pesawat',
                'letter_category_id' => $categories['FR'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Permintaan Tiket Kereta Api',
                'letter_category_id' => $categories['FR'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Permintaan Tiket Bus',
                'letter_category_id' => $categories['FR'],
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('letter_subjects')->insert($subjects);
    }
}
