<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomponenNilaiOsce extends Model
{
    use HasFactory;

    protected $table = 'komponen_nilai_osce';
    protected $primaryKey = 'id_komponen_nilai_osce';

    public $guarded = [];

    public function jenis()
    {
        return $this->belongsTo(JenisOsce::class,'id_jenis_osce','id_jenis_osce');
    }

    public function instrumenNilai()
    {
        return $this->hasMany(InstrumenNilaiOsce::class,'id_komponen_nilai_osce','id_komponen_nilai_osce');
    }
}
