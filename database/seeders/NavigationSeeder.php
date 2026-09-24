<?php

namespace Database\Seeders;

use App\Models\Navigation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Navigation::create([
            'name' => 'Dashboard',
            'url' => 'dashboard',
            'icon' => 'fas fa-warehouse',
            'main_menu' => Null,
        ]);

        Navigation::create([
            'name' => 'Konfigurasi',
            'url' => 'konfigurasi',
            'icon' => 'fas fa-columns',
            'main_menu' => Null,
        ]);

        Navigation::create([
            'name' => 'Roles',
            'url' => 'konfigurasi/roles',
            'icon' => Null,
            'main_menu' => 2,
        ]);

        Navigation::create([
            'name' => 'Permissions',
            'url' => 'konfigurasi/permissions',
            'icon' => Null,
            'main_menu' => 2,
        ]);

        Navigation::create([
            'name' => 'Users',
            'url' => 'konfigurasi/users',
            'icon' => Null,
            'main_menu' => 2,
        ]);

        Navigation::create([
            'name' => 'Menus',
            'url' => 'konfigurasi/menus',
            'icon' => Null,
            'main_menu' => 2,
        ]);
    }
}
