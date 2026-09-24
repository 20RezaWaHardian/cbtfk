<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;

class ImportPesertaOsce implements ToCollection
{
    protected $id_jadwal_osce;

    public function __construct($id_jadwal_osce)
    {
        $this->id_jadwal_osce = $id_jadwal_osce;
    }
    public function collection(Collection $collection)
    {
        $collection->skip(1)->each(function ($row) {
            $nim = $row[1];
            $station = $row[2] ?? null;
            $urutan = $row[3] ?? null;
            $mhs_pt = DB::table('siakad.mhs_pt')
                ->where('no_mhs', $nim)
                ->first();

            // Contoh: Simpan ke database
            if ($mhs_pt && $station) {
                $jenisOsce = DB::table('jadwal_has_stase as a')
                    ->join('jenis_osce as b', 'b.id_jenis_osce', '=', 'a.id_jenis_osce')
                    ->where('a.id_jadwal_osce', $this->id_jadwal_osce)
                    ->where(function ($query) use ($station) {
                        $query->where('a.id_jenis_osce', $station)
                            ->orWhere('b.nama_jenis_osce', $station);
                    })
                    ->select('a.id_jenis_osce')
                    ->first();

                if (!$jenisOsce) {
                    return;
                }

                $cek = DB::table('jadwal_has_mhs')
                    ->where('id_jadwal_osce', $this->id_jadwal_osce)
                    ->where('id_mhs_pt', $mhs_pt->id_mhs_pt)
                    ->first();
                if (!$cek) {
                    DB::table('jadwal_has_mhs')->insert([
                        'id_jadwal_osce'  => $this->id_jadwal_osce,
                        'id_mhs_pt'  => $mhs_pt->id_mhs_pt,
                    ]);
                }

                $cekStation = DB::table('peserta_station_osce')
                    ->where('id_jadwal_osce', $this->id_jadwal_osce)
                    ->where('id_jenis_osce', $jenisOsce->id_jenis_osce)
                    ->where('id_mhs_pt', $mhs_pt->id_mhs_pt)
                    ->exists();

                if (!$cekStation) {
                    $urutanAntrian = $urutan ?: ((int) DB::table('peserta_station_osce')
                        ->where('id_jadwal_osce', $this->id_jadwal_osce)
                        ->where('id_jenis_osce', $jenisOsce->id_jenis_osce)
                        ->max('urutan_antrian') + 1);

                    DB::table('peserta_station_osce')->insert([
                        'id_jadwal_osce' => $this->id_jadwal_osce,
                        'id_jenis_osce' => $jenisOsce->id_jenis_osce,
                        'id_mhs_pt' => $mhs_pt->id_mhs_pt,
                        'urutan_antrian' => $urutanAntrian,
                        'status' => 'menunggu',
                        'waktu_masuk' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
