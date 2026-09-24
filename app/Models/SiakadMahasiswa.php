<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiakadMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'sistembl_siakad-uin.mahasiswa';
    protected $primaryKey = 'id_mahasiswa';
    protected $guarded = [];
}
