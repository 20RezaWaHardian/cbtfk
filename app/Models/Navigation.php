<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Navigation extends Model
{
    use HasFactory;

    protected $table = 'navigation';
    protected $guarded = ['id'];
    protected $fillable = ['name', 'url', 'icon', 'main_menu', 'sub_menu', 'sort'];

    #relasikan navigasi ke diri sendiri
    public function subMenus()
    {
        return $this->hasMany(Navigation::class, 'main_menu')->orderBy('sort', 'asc');
    }

    public function subSubMenus()
    {
        return $this->belongsTo(Navigation::class, 'sub_menu');
    }

    #relasi pivot tabel
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_menus', 'menu_id', 'role_id');
    }
}
