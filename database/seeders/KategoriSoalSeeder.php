<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSoalSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            'Anatomi',
            'Fisiologi',
            'Biokimia',
            'Farmakologi',
            'Patologi Anatomi',
            'Mikrobiologi',
            'Ilmu Penyakit Dalam',
            'Ilmu Bedah',
            'Obstetri dan Ginekologi',
            'Ilmu Kesehatan Anak',
        ];

        foreach ($kategori as $nama) {
            DB::table('kategori_soal')->updateOrInsert(
                ['nama_kategori' => $nama],
                [
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}