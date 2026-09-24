<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiakadMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'sistem_blok.mahasiswa';
    protected $primaryKey = 'id_mahasiswa';
    protected $guarded = [];
}
