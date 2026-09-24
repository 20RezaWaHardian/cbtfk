<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketSoal extends Model
{
    use HasFactory;

    use HasFactory;
    protected $table = 'paket_soal';
    public $primaryKey = 'id_paket_soal';
    protected $guarded = [];

    public function soal()
    {
        return $this->belongsToMany(Soal::class, 'paket_has_soal', 'paket_soal_id', 'soal_id')->withPivot('poin');;
    }


}
