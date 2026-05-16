<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeminarServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Seminar Proposal',
                'code' => 'SP',
                'description' => 'Pengajuan Seminar Proposal Mahasiswa',
                'color' => '#4f46e5',
                'is_active' => true
            ],
            [
                'name' => 'Seminar Hasil',
                'code' => 'SH',
                'description' => 'Pengajuan Seminar Hasil Mahasiswa',
                'color' => '#10b981',
                'is_active' => true
            ],
            [
                'name' => 'Seminar Tutup',
                'code' => 'ST',
                'description' => 'Pengajuan Seminar Tutup Mahasiswa',
                'color' => '#f59e0b',
                'is_active' => true
            ],
        ];

        foreach ($services as $service) {
            \App\Models\QueueService::updateOrCreate(['code' => $service['code']], $service);
        }
    }
}
