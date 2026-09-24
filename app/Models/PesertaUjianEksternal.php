<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaUjianEksternal extends Model
{
    use HasFactory;

    protected $table = "peserta_eksternal";
    protected $primaryKey = "id_peserta_eksternal";

    public $guarded = [];
}
