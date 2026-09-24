<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriSoalKue extends Model
{
    use HasFactory;

    protected $table = 'kategori_kuesioner';
    public $primaryKey = 'id_kategori_kuesioner';
    protected $guarded = [];

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class, 'kuesioner_id', 'id_kuesioner');
    }

    public function pertanyaan()
    {
    return $this->hasMany(PertanyaanKuesioner::class, 'kategori_kuesioner_id');
    }
}
