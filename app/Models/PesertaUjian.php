<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaUjian extends Model
{
    use HasFactory;
    protected $table = 'peserta_ujian';
    public $primaryKey = 'id_peserta_ujian';
    protected $guarded = [];

    public function mahasiswa_rombel()
    {
        return $this->belongsTo(SBMahasiswaRombel::class, 'mahasiswa_rombel_id', 'id_mahasiswa_rombel');
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'id_ujian');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(SiakadMahasiswa::class, 'id_mhs_pt', 'id_mahasiswa');
    }

    public function peserta_eksternal()
    {
        return $this->belongsTo(PesertaUjianEksternal::class, 'id_peserta_eksternal', 'id_peserta_eksternal');
    }

    public function total_nilai()
    {
        $id_peserta_ujian = $this->id_peserta_ujian;

        $ujian = $this->ujian;

        if (!$ujian) {
            return 0;
        }

        $paket_has_soal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)->whereNotNull('poin')->get();

        $total_poin = 0;
        if ($paket_has_soal->isNotEmpty()) {

            $total_poin = $paket_has_soal->sum('poin');
        } else {
            return false;
        }

        $score_pilgan = PilganJawab::where('peserta_ujian_id', $id_peserta_ujian)->sum('score');
        $score_essay = EssayJawab::where('peserta_ujian_id', $id_peserta_ujian)->where('score', '!=', null)->sum('score');
        $poin_didapat = $score_pilgan + $score_essay;
        $nilai_akhir = $poin_didapat / $total_poin * 100;
        $nilai_akhir = substr($nilai_akhir, 0, 5);
        return $nilai_akhir;
    }
}
