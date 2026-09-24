<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalOsce extends Model
{
    use HasFactory;

    protected $table = 'jadwal_osce';
    protected $primaryKey = 'id_jadwal_osce';

    public $guarded = [];

    public function blok()
    {
        return $this->belongsTo(SBBlok::class,'blok_id','id');
    }


    // public function stase()
    // {
    //     return $this->belongsToMany(JenisOsce::class,'jadwal_has_stase','id_jadwal_osce','id_jenis_osce')->withPivot('id_pegawai','id_jadwal_has_stase');
    // }

    // public function stase()
    // {
    //     return $this->hasMany(JenisOsce::class,'id_jadwal_osce','id_jadwal_osce');
    // }

}
