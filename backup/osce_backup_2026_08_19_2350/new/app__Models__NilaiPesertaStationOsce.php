<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiPesertaStationOsce extends Model
{
    use HasFactory;

    protected $table = 'nilai_peserta_station_osce';
    protected $primaryKey = 'id_nilai_peserta_station_osce';
    public $guarded = [];
}
