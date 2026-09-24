<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiakadSemester extends Model
{
    use HasFactory;
    protected $table = 'sistem_blok.semester';
    public $primaryKey = 'id_semester';
    protected $guarded = [];

    public function scopeSemesterAktif($query)
    {
        return $query->where('is_aktif', 1);
    }
}
