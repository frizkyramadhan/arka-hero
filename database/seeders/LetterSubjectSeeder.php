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
        $subjects = [
            // Surat Eksternal (A)
            [
                'subject_name' => 'Surat Pengantar MCU',
                'category_code' => 'A',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Penawaran Kerja',
                'category_code' => 'A',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Keterangan',
                'category_code' => 'A',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Penonaktifan BPJS',
                'category_code' => 'A',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Surat Internal (B)
            [
                'subject_name' => 'Surat Pengantar Karyawan',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'LOT',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Perjanjian Ikatan Dinas',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Pemberitahuan Daily',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Perjanjian Ikatan Dinas OJT',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Berita Acara',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Pengajuan Cuti Khusus',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Internal Memo',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Surat Permohonan Mutasi',
                'category_code' => 'B',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PKWT
            [
                'subject_name' => 'Perjanjian Kerja Waktu Tertentu - PKWT I',
                'category_code' => 'PKWT',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Perjanjian Kerja Waktu Tertentu - PKWT II',
                'category_code' => 'PKWT',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Perjanjian Kerja Waktu Tertentu - PKWT III',
                'category_code' => 'PKWT',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PAR
            [
                'subject_name' => 'Personal Action Request - New Hire',
                'category_code' => 'PAR',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Personal Action Request - Promosi',
                'category_code' => 'PAR',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Personal Action Request - Mutasi',
                'category_code' => 'PAR',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Personal Action Request - Demosi',
                'category_code' => 'PAR',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Certificate of Employment
            [
                'subject_name' => 'Surat Pengalaman Kerja',
                'category_code' => 'CRTE',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // SKPK
            [
                'subject_name' => 'Surat Keterangan Pengalaman Kerja',
                'category_code' => 'SKPK',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Memo
            [
                'subject_name' => 'Memorandum Internal',
                'category_code' => 'MEMO',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // FPTK
            [
                'subject_name' => 'Form Permintaan Tenaga Kerja',
                'category_code' => 'FPTK',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Form Request
            [
                'subject_name' => 'Permintaan Tiket Pesawat',
                'category_code' => 'FR',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Permintaan Tiket Kereta Api',
                'category_code' => 'FR',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subject_name' => 'Permintaan Tiket Bus',
                'category_code' => 'FR',
                'document_model' => null,
                'is_active' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('letter_subjects')->insert($subjects);
    }
}
