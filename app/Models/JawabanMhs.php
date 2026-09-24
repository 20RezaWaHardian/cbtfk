<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanMhs extends Model
{
    use HasFactory;

    protected $table = 'jawaban_mhs';
    protected $primaryKey = 'id_jawaban_mhs';
    protected $guarded = [];
}
