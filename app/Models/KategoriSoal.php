<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriSoal extends Model
{
    use HasFactory;
    protected $table = 'kategori_soal';
    public $primaryKey = 'id_kategori_soal';
    protected $guarded = [];

    public function soal()
    {
        return $this->hasManyThrough(
            Soal::class,
            SubKategoriSoal::class,
            'id_kategori_soal',
            'sub_kategori_soal_id',
            'id_kategori_soal',
            'id_sub_kategori_soal'
        );
    }

    public function sub_kategori_soal()
    {
        return $this->hasMany(SubKategoriSoal::class, 'id_kategori_soal', 'id_kategori_soal')->active();
    }
}
