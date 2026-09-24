<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiakadDosen extends Model
{
    use HasFactory;
    protected $table = 'sistembl_siakad-uin.dosen';
    public $primaryKey = 'id_dosen';
    protected $guarded = [];
}
