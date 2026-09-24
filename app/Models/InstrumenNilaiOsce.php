<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstrumenNilaiOsce extends Model
{
    use HasFactory;

    protected $table = 'instrumen_nilai_osce';
    protected $primaryKey = 'id';

    public $guarded = [];
}
