<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EssayJawab extends Model
{
    use HasFactory;
    protected $table = 'essay_jawab';
    public $primaryKey = 'id_essay_jawab';
    protected $guarded = [];

    public function peserta_ujian(){
        return $this->belongsTo(PesertaUjian::class);
    }
    public function soal_essay(){
        return $this->belongsTo(Essay::class);
    }

    public function soal(){
        return $this->belongsTo(Soal::class,'soal_id');
    }


}
