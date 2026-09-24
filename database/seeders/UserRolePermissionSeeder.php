<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $default_user_value = [
            'password' => Hash::make('password01'), // password
            'remember_token' => Str::random(10),
        ];

        DB::beginTransaction();
        try {
            $developer = User::create(array_merge([
                'id_asal' => 6645,
                'username' => 20220018,
                'email' => 'repaldi@unja.ac.id',
            ], $default_user_value));


            $admin = User::create(array_merge([
                'username' => 'admin',
                'email' => 'admin@unja.ac.id',
            ], $default_user_value));


            $user = User::create(array_merge([
                'username' => 'user',
                'email' => 'user@unja.ac.id',
            ], $default_user_value));


            $role_developer = Role::create(['name' => 'developer']);
            $role_admin = Role::create(['name' => 'admin']);
            $role_user = Role::create(['name' => 'user']);

            $permission = Permission::create(['name' => 'read dashboard']);

            $permission = Permission::create(['name' => 'read konfigurasi']);
            $permission = Permission::create(['name' => 'read konfigurasi/roles', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'create konfigurasi/roles', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'update konfigurasi/roles', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'delete konfigurasi/roles', 'main_permission' => 2]);

            $permission = Permission::create(['name' => 'read konfigurasi/permissions', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'create konfigurasi/permissions', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'update konfigurasi/permissions', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'delete konfigurasi/permissions', 'main_permission' => 2]);

            $permission = Permission::create(['name' => 'read konfigurasi/users', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'create konfigurasi/users', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'update konfigurasi/users', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'delete konfigurasi/users', 'main_permission' => 2]);

            $permission = Permission::create(['name' => 'read konfigurasi/menus', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'create konfigurasi/menus', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'update konfigurasi/menus', 'main_permission' => 2]);
            $permission = Permission::create(['name' => 'delete konfigurasi/menus', 'main_permission' => 2]);

            $role_developer->givePermissionTo([
                'read dashboard', 'read konfigurasi', 'read konfigurasi/roles', 'create konfigurasi/roles', 'update konfigurasi/roles', 'delete konfigurasi/roles',
                'read konfigurasi/permissions', 'create konfigurasi/permissions', 'update konfigurasi/permissions', 'delete konfigurasi/permissions',
                'read konfigurasi/users', 'create konfigurasi/users', 'update konfigurasi/users', 'delete konfigurasi/users',
                'read konfigurasi/menus', 'create konfigurasi/menus', 'update konfigurasi/menus', 'delete konfigurasi/menus',
            ]);
            $role_admin->givePermissionTo([
                'read dashboard', 'read konfigurasi',
                'read konfigurasi/users', 'create konfigurasi/users', 'update konfigurasi/users', 'delete konfigurasi/users',
            ]);
            $role_user->givePermissionTo([
                'read dashboard',
            ]);

            $developer->assignRole('developer');
            $admin->assignRole('admin');
            $user->assignRole('user');

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
