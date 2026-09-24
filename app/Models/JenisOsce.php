<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisOsce extends Model
{
    use HasFactory;

    protected $table = 'jenis_osce';
    protected $primaryKey = 'id_jenis_osce';

    public $guarded = [];

    public function komponen()
    {
        return $this->hasMany(KomponenNilaiOsce::class, 'id_jenis_osce');
    }
}
