<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PertanyaanKuesioner extends Model
{
    use HasFactory;
    protected $table = 'pertanyaan_kuesioner';
    public $primaryKey = 'id_pertanyaan_kuesioner';
    protected $guarded = [];

    public function kategoriKue()
    {
        return $this->belongsTo(KategoriSoalKue::class, 'kategori_kuesioner_id', 'id_kategori_kuesioner');
    }
}
