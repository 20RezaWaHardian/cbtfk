<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSiakad extends Model
{
    use HasFactory;
    protected $table = 'sistembl_siakad-uin .users';
    protected $guarded = [];
    public $primaryKey = 'id';

    public function mahasiswa()
    {
        return $this->hasOne(SiakadMahasiswa::class, 'user_id', 'id');
    }
}
