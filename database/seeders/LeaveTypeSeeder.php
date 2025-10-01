<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Cuti Tahunan',
                'code' => '1.01',
                'category' => 'annual',
                'default_days' => 12,
                'eligible_after_years' => 1,
                'deposit_days_first' => 0,
                'carry_over' => true,
                'remarks' => 'Cuti tahunan 12 hari setelah 1 tahun masa kerja',
                'is_active' => true
            ],
            [
                'name' => 'Karyawan sendiri kawin',
                'code' => '2.01',
                'category' => 'paid',
                'default_days' => 3,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Karyawan sendiri kawin',
                'is_active' => true
            ],
            [
                'name' => 'Mengawinkan anak',
                'code' => '2.04',
                'category' => 'paid',
                'default_days' => 2,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Mengawinkan anaknya - The marriage of his/her child',
                'is_active' => true
            ],
            [
                'name' => 'Menyunatkan anak',
                'code' => '2.02',
                'category' => 'paid',
                'default_days' => 2,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Menyunatkan anak',
                'is_active' => true
            ],
            [
                'name' => 'Membaptiskan anak',
                'code' => '2.03',
                'category' => 'paid',
                'default_days' => 2,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Membaptiskan anak - The baptism of his/her child',
                'is_active' => true
            ],
            [
                'name' => 'Anggota keluarga meninggal dunia',
                'code' => '2.05',
                'category' => 'paid',
                'default_days' => 2,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Anggota keluarga meninggal dunia, yaitu suami/isteri, orang tua/mertua atau anak',
                'is_active' => true
            ],
            [
                'name' => 'Isteri melahirkan',
                'code' => '2.06',
                'category' => 'paid',
                'default_days' => 2,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Isteri melahirkan',
                'is_active' => true
            ],
            [
                'name' => 'Anggota keluarga dalam satu rumah meninggal dunia',
                'code' => '2.07',
                'category' => 'paid',
                'default_days' => 1,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => null,
                'is_active' => true
            ],
            [
                'name' => 'Sakit',
                'code' => '2.08',
                'category' => 'paid',
                'default_days' => 99,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Sesuai rekomendasi dokter perusahaan/dokter yang disetujui perusahaan dan melampirkan surat dokter',
                'is_active' => true
            ],
            [
                'name' => 'Datang bulan',
                'code' => '2.09',
                'category' => 'paid',
                'default_days' => 99,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Konsultasi dengan bagian Administrasi',
                'is_active' => true
            ],
            [
                'name' => 'Melahirkan/cuti hamil',
                'code' => '2.10',
                'category' => 'paid',
                'default_days' => 99,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Konsultasi dengan bagian Administrasi',
                'is_active' => true
            ],
            [
                'name' => 'Keguguran',
                'code' => '2.11',
                'category' => 'paid',
                'default_days' => 99,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Konsultasi dengan bagian Administrasi',
                'is_active' => true
            ],
            [
                'name' => 'Tugas pemerintah',
                'code' => '2.12',
                'category' => 'paid',
                'default_days' => 99,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Konsultasi dengan bagian Administrasi dan menunjukan dokumen resmi',
                'is_active' => true
            ],
            [
                'name' => 'Naik Haji/Ziarah Agama',
                'code' => '2.13',
                'category' => 'paid',
                'default_days' => 99,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Konsultasi dengan bagian Administrasi',
                'is_active' => true
            ],
            [
                'name' => 'Izin Tanpa Upah',
                'code' => '3.01',
                'category' => 'unpaid',
                'default_days' => 99,
                'eligible_after_years' => 0,
                'deposit_days_first' => 0,
                'carry_over' => false,
                'remarks' => 'Selain hal yang disebut dalam izin dengan upah/dibayar, cuti tahunan/cuti panjang/cuti lapangan dan sebutkan alasannya',
                'is_active' => true
            ],
            [
                'name' => 'Cuti Panjang - Staff',
                'code' => '4.01',
                'category' => 'lsl',
                'default_days' => 50,
                'eligible_after_years' => 5,
                'deposit_days_first' => 10,
                'carry_over' => true,
                'remarks' => 'Cuti panjang atau Long Service Leave untuk karyawan dengan klasifikasi Staff',
                'is_active' => true
            ],
            [
                'name' => 'Cuti Panjang - Non Staff',
                'code' => '4.02',
                'category' => 'lsl',
                'default_days' => 50,
                'eligible_after_years' => 6,
                'deposit_days_first' => 10,
                'carry_over' => true,
                'remarks' => 'Cuti panjang atau Long Service Leave untuk karyawan dengan klasifikasi Non Staff',
                'is_active' => true
            ]
        ];

        foreach ($leaveTypes as $leaveType) {
            \App\Models\LeaveType::updateOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }
    }
}
