<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiakadUser extends Model
{
    use HasFactory;

    protected $table = 'sistembl_siakad-uin.users';
    protected $primaryKey = 'id';

    public $guarded = [];
}
