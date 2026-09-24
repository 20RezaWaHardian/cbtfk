<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
  use HasFactory;
  protected $table = 'ujian';
  public $primaryKey = 'id_ujian';
  protected $guarded = [];

  public function peserta_ujian()
  {
    return $this->hasMany(PesertaUjian::class, 'ujian_id', 'id_ujian');
  }
  public function paket_soal()
  {
    return $this->belongsTo(PaketSoal::class, 'paket_soal_id');
  }

  public function prodi()
  {
    return $this->belongsTo(SiakadProdi::class, 'prodi_id');
  }

  public function pegawai()
  {
    return $this->belongsTo(KepegPegawai::class, 'pengawas_ujian', 'id_pegawai');
  }

  public function pengawas()
  {
      return $this->belongsToMany(SiakadDosen::class, 'ujian_has_pengawas', 'id_ujian', 'id_pegawai');
  }
}
