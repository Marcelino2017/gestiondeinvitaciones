<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin = User::updateOrCreate(
            ['email' => 'admin@gestiondeinvitaciones.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin12345*'),
            ]
        );

        $admin->syncRoles([$adminRole]);
    }
}
