<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = Permission::query()->pluck('name')->all();

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        $admin->syncPermissions($allPermissions);

        $manager->syncPermissions([
            'users.show',
            'organizations.create',
            'organizations.show',
            'organizations.update',
            'invitations.create',
            'invitations.show',
            'invitations.update',
        ]);

        $member->syncPermissions([
            'organizations.show',
            'invitations.show',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

