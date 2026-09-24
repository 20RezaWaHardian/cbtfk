<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SBBlok extends Model
{
    use HasFactory;

    protected $table = 'sistembl_siakad-uin.blok';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
