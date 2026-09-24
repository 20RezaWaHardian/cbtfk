<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilganJawab extends Model
{
    use HasFactory;
    protected $table = 'pilgan_jawab';
    public $primaryKey = 'id_pilgan_jawab';
    protected $guarded = [];

    public function peserta_ujian(){
        return $this->belongsTo(PesertaUjian::class);
    }
    public function soal_pilgan(){
        return $this->belongsTo(Pilgan::class,'pilgan_id');
    }
    public function soal(){
        return $this->belongsTo(Soal::class,'soal_id');
    }


}
