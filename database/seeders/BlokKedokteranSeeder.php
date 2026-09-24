<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BlokKedokteranSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('semester')->updateOrInsert(
            ['id_semester' => '20261'],
            $this->withTimestamps('semester', [
                'tgl_mulai' => '2026-08-01',
                'tgl_selesai' => '2027-01-31',
                'periode_aktif' => 1,
            ])
        );

        $blok = [
            ['id_blok' => 1, 'nama_blok' => 'Biomedik Dasar', 'tahun' => 2026, 'kode_kelas' => 'KED-BD-20261'],
            ['id_blok' => 2, 'nama_blok' => 'Kardiovaskular', 'tahun' => 2026, 'kode_kelas' => 'KED-KV-20261'],
            ['id_blok' => 3, 'nama_blok' => 'Respirasi', 'tahun' => 2026, 'kode_kelas' => 'KED-RS-20261'],
            ['id_blok' => 4, 'nama_blok' => 'Gastrointestinal', 'tahun' => 2026, 'kode_kelas' => 'KED-GI-20261'],
            ['id_blok' => 5, 'nama_blok' => 'Neuropsikiatri', 'tahun' => 2026, 'kode_kelas' => 'KED-NP-20261'],
        ];

        foreach ($blok as $item) {
            DB::table('blok')->updateOrInsert(
                ['id_blok' => $item['id_blok']],
                $this->withTimestamps('blok', [
                    'nama_blok' => $item['nama_blok'],
                    'tahun' => $item['tahun'],
                    'status' => 1,
                ])
            );

            DB::table('kelas')->updateOrInsert(
                ['id_blok' => $item['id_blok'], 'id_semester' => '20261'],
                $this->withOptionalColumns('kelas', $this->withTimestamps('kelas', [
                    'nama_kelas' => $item['nama_blok'],
                    'kode_kelas' => $item['kode_kelas'],
                    'jumlah_peserta' => 0,
                    'id_kelas_parent' => null,
                ]))
            );
        }
    }

    private function withTimestamps(string $table, array $data): array
    {
        if (Schema::hasColumn($table, 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        return $data;
    }

    private function withOptionalColumns(string $table, array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->all();
    }
}