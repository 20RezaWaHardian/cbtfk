<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as RoleSpatie;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Role extends RoleSpatie
{
    use HasFactory;

    #relasi pivot tabel
    public function navigation()
    {
        return $this->belongsToMany(Navigation::class, 'role_has_menus', 'role_id', 'menu_id');
    }
}
