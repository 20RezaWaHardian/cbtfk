<?php

namespace App\Helpers;

use App\Models\SiakadMahasiswa;
use Illuminate\Support\Facades\DB;

class MyHelpers
{
    public static function nama_mahasiswa($id_mhs_pt)
    {
        $data = SiakadMahasiswa::join('siakad.mhs_pt as mhs', 'mhs.id_mahasiswa', '=', 'siakad.mahasiswa.id_mahasiswa')->where('mhs.id_mhs_pt', $id_mhs_pt)->first();
        return $data->nama_mahasiswa;
    }

    public static function nama_gelar($data)
    {
        if (!empty($data->gelar_belakang)) $koma = ',';
        else $koma = '';
        $nama_gelar = $data->gelar_depan . ' ' . $data->nama_pegawai . $koma . ' ' . $data->gelar_belakang;

        return $nama_gelar;
    }

    public static function nama_gelarById($id_pegawai)
    {
        $pegawai = DB::table('sistembl_siakad-uin .dosen')->where('id_dosen', $id_pegawai)->first();

        if ($pegawai && isset($pegawai->nama)) {
            $koma = '';

            // Menyiapkan format tanpa 'gelar_depan' untuk memulai
            $nama_gelar = $pegawai->nama;

            // Menambahkan 'gelar_depan' jika ada dan tidak kosong
            if (!empty($pegawai->gelar_depan)) {
                $nama_gelar = $pegawai->gelar_depan . ' ' . $nama_gelar;
            }

            // Menambahkan koma dan 'gelar_belakang' jika ada dan tidak kosong
            if (!empty($pegawai->gelar_belakang)) {
                $koma = ',';
                $nama_gelar .= $koma . ' ' . $pegawai->gelar_belakang;
            }

            return $nama_gelar;
        } else {
            // Jika tidak ada data atau tidak ada 'nama_pegawai' dengan id yang diberikan
            return 'Data tidak valid';
        }
    }

    public static function semester($str)
    {
        if (strlen($str) == 5) {
            $sem = substr($str, 0, 4);
            $sthunb = $sem + 1;
            $nama = substr($str, 4, 1);
            if ($nama <= 3) {
                $arr = array(
                    '1' => 'Ganjil',
                    '2' => 'Genap',
                    '3' => 'Pendek'
                );
            } else {
                $arr = array(
                    $nama  => 'Error!'
                );
            }
            if ($arr[$nama] == TRUE) {
                if ($nama == '3') {
                    $out = $arr[$nama] . " " . $sem;
                } else {
                    $out = $arr[$nama] . " " . $sem . " / " . $sthunb;
                }
            } else {
                $out = "Error";
            }
        } else {
            $out = "-";
        }
        return $out;
    }

    public static function angkaKeRomawi($angka)
    {
        $romawi = '';
        $map = [
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1
        ];

        foreach ($map as $roman => $value) {
            while ($angka >= $value) {
                $romawi .= $roman;
                $angka -= $value;
            }
        }

        return $romawi;
    }

    public static function kelas($kelas)
    {
        $kelas = DB::table('siakad.kelas as a')
            ->select(
                'a.id_kelas',
                'b.nama_matakuliah',
                'c.nama_kurikulum',
                'a.kode_kelas',
                'a.jumlah_peserta',
                'b.kode_matakuliah',
                'd.nama_blok',
                'd.tahun as tahun_blok',
                'b.sks_total',
                'd.id_blok'
            )
            ->leftJoin('siakad.matakuliah as b', 'a.id_matakuliah', '=', 'b.id_matakuliah')
            ->leftJoin('siakad.kurikulum as c', 'a.id_kurikulum', '=', 'c.id_kurikulum')
            ->leftJoin('siakad_blok.blok as d', 'b.id_blok', '=', 'd.id_blok')
            ->where('b.sistembl_siakad-uin ', '1')
            ->where('a.id_kelas', $kelas)
            ->whereNull('a.id_kelas_parent')
            ->orderBy('b.nama_blok')
            ->orderBy('a.id_kelas')
            ->first();

        return $kelas;
    }
}
