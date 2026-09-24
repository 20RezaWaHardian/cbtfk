<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kuesioner extends Model
{
    use HasFactory;

    protected $table = 'kuesioner';
    public $primaryKey = 'id_kuesioner';
    protected $guarded = [];

    public function kategori_kuesioner()
    {
        return $this->hasMany(KategoriSoalKue::class, 'kuesioner_id');
    }

}
