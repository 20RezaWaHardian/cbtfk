<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaStationOsce extends Model
{
    use HasFactory;

    protected $table = 'peserta_station_osce';
    protected $primaryKey = 'id_peserta_station_osce';
    public $guarded = [];
}
