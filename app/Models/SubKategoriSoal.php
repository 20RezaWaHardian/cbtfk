<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubKategoriSoal extends Model
{
    use HasFactory;

    protected $table = 'sub_kategori_soal';
    public $primaryKey = 'id_sub_kategori_soal';
    protected $guarded = [];

    protected $casts = [
        'is_delete' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_delete', false);
    }

    public function kategori_soal()
    {
        return $this->belongsTo(KategoriSoal::class, 'id_kategori_soal', 'id_kategori_soal');
    }

    public function soal()
    {
        return $this->hasMany(Soal::class, 'sub_kategori_soal_id', 'id_sub_kategori_soal');
    }
}
