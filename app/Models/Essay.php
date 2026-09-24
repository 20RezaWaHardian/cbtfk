<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Essay extends Model
{
    use HasFactory;
    protected $table = 'essay';
    public $primaryKey = 'id_essay';
    protected $guarded = [];

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
    public function essay_jawab(){
        return $this->hasMany(EssayJawab::class,'essay_id');
    }
    public function peserta_ujian(){
        return $this->belongsTo(PesertaUjian::class);
    }
}
