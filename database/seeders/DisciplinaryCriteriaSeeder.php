<?php

namespace Database\Seeders;

use App\Models\DisciplinaryCriterion;
use Illuminate\Database\Seeder;

class DisciplinaryCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $rows = array_merge(
            $this->counselingCriteria(),
            $this->sp1Criteria(),
            $this->sp3Criteria(),
        );

        foreach ($rows as $index => $row) {
            DisciplinaryCriterion::updateOrCreate(
                ['code' => $row['code']],
                [
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'article_reference' => $row['article_reference'],
                    'sanction_type' => $row['sanction_type'],
                    'is_active' => true,
                    'sort_order' => $row['sort_order'] ?? ($index + 1),
                ]
            );
        }

        $this->command?->info('Seeded '.count($rows).' disciplinary PP criteria (Counseling, SP1, SP Pertama & Terakhir).');
    }

    /**
     * PP Pasal 22 ayat (5) — Coaching/Counseling (Teguran lisan dan tertulis), poin 6.a–6.p
     */
    protected function counselingCriteria(): array
    {
        $items = [
            'a' => 'Datang terlambat / pulang lebih cepat dari jam kerja yang ditentukan tanpa alasan yang sah.',
            'b' => 'Tidak masuk kerja tanpa memberikan keterangan atau alasan yang sah.',
            'c' => 'Membuang sampah / kotoran tidak pada tempatnya.',
            'd' => 'Merokok selain pada tempat-tempat yang telah ditentukan.',
            'e' => 'Mengotori dinding, pintu, jendela dan bangunan atau peralatan Perusahaan lainnya.',
            'f' => 'Bermalas-malasan dalam melaksanakan tugas-tugasnya.',
            'g' => 'Bermain kartu atau permainan lainnya pada jam kerja.',
            'h' => 'Mencoret-coret daftar hadir atau pengumuman yang terpasang pada papan pengumuman.',
            'i' => 'Bertingkah tidak sopan, berpakaian tidak rapi atau berambut acak-acakan.',
            'j' => 'Menjalankan usaha pribadi di dalam Perusahaan.',
            'k' => 'Memasuki tempat-tempat yang tidak diijinkan tanpa seijin atasan.',
            'l' => 'Mengganggu ketentraman dan ketenangan di lingkungan kerja.',
            'm' => 'Mengubah jadwal kerja tanpa seijin atasan dan manajemen Perusahaan.',
            'n' => 'Tidak memakai kartu identitas selama berada di lingkungan Perusahaan.',
            'o' => 'Tidak mematuhi standar kebersihan, kesehatan dan sanitasi yang diwajibkan Perusahaan.',
            'p' => 'Melakukan perbuatan lainnya yang berbobot sama atau dianggap berbobot sama dengan pelanggaran / kesalahan Pasal 22 ayat (5).',
        ];

        return $this->mapItems('counseling', 'PP-22.5', 'Pasal 22 ayat (5)', $items, 100);
    }

    /**
     * PP Pasal 22 ayat (6) — Surat Peringatan I, poin 7.a–7.r
     */
    protected function sp1Criteria(): array
    {
        $items = [
            'a' => 'Karyawan yang telah mendapatkan teguran, tetapi masih melakukan kesalahan atau pelanggaran, walaupun sifatnya sama atau tidak sama.',
            'b' => 'Datang terlambat tanpa alasan yang sah lebih dari 3 (tiga) kali dalam sebulan.',
            'c' => 'Meninggalkan pekerjaan tanpa seijin atasan.',
            'd' => 'Tidak masuk kerja tanpa memberikan keterangan atau alasan yang sah selama 2 (dua) hari dalam sebulan.',
            'e' => 'Mempergunakan fasilitas atau asset Perusahaan untuk kepentingan pribadi tanpa seijin.',
            'f' => 'Menolak perintah kerja yang layak, tidak mentaati arahan atasan, atau tidak menyelesaikan target pekerjaan yang diberikan.',
            'g' => 'Tidak memakai alat-alat keselamatan kerja yang disediakan oleh Perusahaan.',
            'h' => 'Tidak mematuhi rambu-rambu dan aturan Keselamatan dan Kesehatan Kerja (K3).',
            'i' => 'Mencatat kehadiran karyawan lain pada daftar hadir / mesin absensi.',
            'j' => 'Memperpanjang waktu istirahat tanpa seijin atasan lebih dahulu.',
            'k' => 'Merokok di area yang berbahaya dan atau terdapat tanda Dilarang Merokok.',
            'l' => 'Menolak diperiksa oleh petugas keamanan.',
            'm' => 'Mempergunakan barang milik Perusahaan untuk kepentingan pribadi atau untuk orang lain yang tidak ada hubungannya dengan pekerjaan.',
            'n' => 'Tidur selama jam kerja.',
            'o' => 'Mengabaikan kewajiban secara tidak bertanggung jawab.',
            'p' => 'Tidak mengindahkan ketentuan dan prosedur kerja.',
            'q' => 'Karena kelalaiannya atau kurang hati-hati sehingga menimbulkan kerugian bagi perusahaan.',
            'r' => 'Melakukan perbuatan lainnya yang berbobot sama atau dianggap berbobot sama dengan pelanggaran / kesalahan Pasal 22 ayat (6).',
        ];

        return $this->mapItems('sp1', 'PP-22.6', 'Pasal 22 ayat (6)', $items, 200);
    }

    /**
     * PP Pasal 22 ayat (8) — Surat Peringatan Pertama dan Terakhir, poin 10.a–10.k
     */
    protected function sp3Criteria(): array
    {
        $items = [
            'a' => 'Karyawan yang telah mendapatkan Surat Peringatan II (dua) dan masih berlaku tetapi masih melakukan kesalahan atau pelanggaran, walaupun sifatnya sama atau tidak sama.',
            'b' => 'Setelah 3 (tiga) kali berturut-turut menolak untuk mentaati perintah atau penugasan yang layak.',
            'c' => 'Melanggar norma-norma, aturan keselamatan dan kesehatan kerja serta lingkungan hidup yang dapat berdampak besar menimbulkan kerugian bagi perusahaan.',
            'd' => 'Perbuatan yang mengakibatkan kerusakan berat barang-barang milik perusahaan dan menimbulkan kerugian bagi perusahaan.',
            'e' => 'Tidak hadir bekerja tanpa memberikan keterangan atau alasan yang sah selama 3 (tiga) hari dalam sebulan.',
            'f' => 'Atas tindakan dan perilakunya mengakibatkan dirinya dalam keadaan sedemikian rupa sehingga tidak dapat menjalankan pekerjaan yang diberikan kepadanya.',
            'g' => 'Tidak mematuhi atau mengabaikan standart operasional prosedur kerja sehingga mengancam/membahayakan/menimbulkan kerugian bagi perusahaan.',
            'h' => 'Melakukan praktek bisnis atau kegiatan tertentu di lingkungan perusahaan yang berdampak memperkaya diri sendiri atau kelompok sehingga mempengaruhi kebijakan perusahaan.',
            'i' => 'Melakukan tindakan atau perbuatan yang dapat menimbulkan keonaran atau keresahan dilingkungan Perusahaan.',
            'j' => 'Memasukkan orang lain / bukan pekerja dalam lingkungan perusahaan tanpa seijin atasan atau perusahaan.',
            'k' => 'Melakukan perbuatan lainnya yang berbobot sama atau dianggap berbobot sama dengan kesalahan / pelanggaran Pasal 22 ayat (8).',
        ];

        return $this->mapItems('sp3', 'PP-22.8', 'Pasal 22 ayat (8)', $items, 300);
    }

    protected function mapItems(string $sanctionType, string $codePrefix, string $articleBase, array $items, int $sortBase): array
    {
        $rows = [];
        $offset = 0;

        foreach ($items as $letter => $text) {
            $offset++;
            $letterUpper = strtoupper((string) $letter);
            $rows[] = [
                'code' => $codePrefix.'.'.$letterUpper,
                'title' => $this->shortTitle($text),
                'description' => $text,
                'article_reference' => $articleBase.' huruf '.$letter,
                'sanction_type' => $sanctionType,
                'sort_order' => $sortBase + $offset,
            ];
        }

        return $rows;
    }

    protected function shortTitle(string $text): string
    {
        $text = rtrim($text, '.');
        if (mb_strlen($text) <= 180) {
            return $text;
        }

        return mb_substr($text, 0, 177).'...';
    }
}
