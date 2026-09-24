<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pilgan extends Model
{
    use HasFactory;
    protected $table = 'pilgan';
    public $primaryKey = 'id_pilgan';
    protected $guarded = [];

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }

    public function pilgan_jawab(){
        return $this->hasMany(PilganJawab::class,'pilgan_id');
    }

    public function peserta_ujian(){
        return $this->belongsTo(PesertaUjian::class);
    }

}
