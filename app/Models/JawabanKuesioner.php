<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanKuesioner extends Model
{
    use HasFactory;
    protected $table = 'jawaban_kuesioner';
    public $primaryKey = 'id_jawaban_kuesioner';
    protected $guarded = [];

    public function pertanyaan()
    {
        return $this->belongsTo(PertanyaanKuesioner::class, 'pertanyaan_kuesioner_id', 'id_pertanyaan_kuesioner');
    }

    public function pesertaUjian()
    {
        return $this->belongsTo(PesertaUjian::class,'peserta_ujian_id','id_peserta_ujian');
    }
}
