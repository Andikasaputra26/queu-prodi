<?php

namespace Database\Seeders;

use App\Models\QueueCounter;
use App\Models\QueueService;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // Jenis Layanan Konsultasi di Prodi
        // ============================================
        $services = [
            [
                'name'        => 'Surat Keterangan Aktif',
                'code'        => 'SKA',
                'color'       => '#6366F1',
                'description' => 'Surat keterangan mahasiswa aktif untuk beasiswa, tunjangan, dll',
            ],
            [
                'name'        => 'Legalisir Dokumen',
                'code'        => 'LEG',
                'color'       => '#8B5CF6',
                'description' => 'Legalisir ijazah, transkrip nilai, atau dokumen akademik lainnya',
            ],
            [
                'name'        => 'Surat Pengantar Magang',
                'code'        => 'MAG',
                'color'       => '#06B6D4',
                'description' => 'Surat pengantar untuk PKL, Magang, atau penelitian instansi luar',
            ],
            [
                'name'        => 'Cuti / Aktif Kembali',
                'code'        => 'ADM',
                'color'       => '#F59E0B',
                'description' => 'Pengajuan cuti akademik atau permohonan aktif kembali kuliah',
            ],
            [
                'name'        => 'Surat Rekomendasi',
                'code'        => 'REC',
                'color'       => '#10B981',
                'description' => 'Surat rekomendasi dosen untuk lanjut studi atau kompetisi',
            ],
            [
                'name'        => 'Urusan Administrasi Lain',
                'code'        => 'OTH',
                'color'       => '#EF4444',
                'description' => 'Urusan administrasi lainnya yang tidak terdaftar di atas',
            ],
        ];

        foreach ($services as $service) {
            QueueService::firstOrCreate(['code' => $service['code']], $service);
        }

        // ============================================
        // Ruangan / Loket Dosen & Staf Prodi
        // ============================================
        $counters = [
            ['name' => 'Ruang Dosen 1',   'officer_name' => 'Dr. Ahmad Fauzi, M.Kom'],
            ['name' => 'Ruang Dosen 2',   'officer_name' => 'Dr. Siti Rahayu, M.T'],
            ['name' => 'Ruang Dosen 3',   'officer_name' => 'Budi Santoso, S.T., M.Eng'],
            ['name' => 'Ruang Dosen 4',   'officer_name' => 'Rina Kusuma, S.Pd., M.Pd'],
            ['name' => 'Ruang Kaprodi',   'officer_name' => 'Prof. Dr. Hendra Wijaya, M.T'],
            ['name' => 'Meja Administrasi','officer_name' => 'Staf Tata Usaha Prodi'],
        ];

        foreach ($counters as $counter) {
            QueueCounter::firstOrCreate(['name' => $counter['name']], $counter);
        }
    }
}
