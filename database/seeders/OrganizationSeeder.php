<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $organizationData = Organization::factory()
                ->makeOne([
                    'name' => "Organizacion Seed {$i}",
                ])
                ->toArray();

            Organization::updateOrCreate(
                ['name' => "Organizacion Seed {$i}"],
                ['description' => $organizationData['description']]
            );
        }

        for ($i = 1; $i <= 8; $i++) {
            $user = User::factory()
                ->makeOne([
                    'name' => "Usuario Sin Rol {$i}",
                    'email' => "usuario.sinrol{$i}@example.com",
                ]);

            User::updateOrCreate(
                ['email' => "usuario.sinrol{$i}@example.com"],
                [
                    'name' => $user->name,
                    'password' => $user->password,
                ]
            );
        }

        for ($i = 1; $i <= 3; $i++) {
            $teamOrganizationData = Organization::factory()
                ->makeOne([
                    'name' => "Organizacion Equipo {$i}",
                    'description' => "Organizacion de prueba con usuarios {$i}.",
                ])
                ->toArray();

            $organization = Organization::updateOrCreate(
                ['name' => "Organizacion Equipo {$i}"],
                ['description' => $teamOrganizationData['description']]
            );

            $adminSeedUser = User::factory()
                ->makeOne([
                    'name' => "Admin Org {$i}",
                    'email' => "org{$i}.admin@example.com",
                ]);

            $adminUser = User::updateOrCreate(
                ['email' => "org{$i}.admin@example.com"],
                [
                    'name' => $adminSeedUser->name,
                    'password' => $adminSeedUser->password,
                ]
            );

            $memberSeedUser = User::factory()
                ->makeOne([
                    'name' => "Member Org {$i}",
                    'email' => "org{$i}.member@example.com",
                ]);

            $memberUser = User::updateOrCreate(
                ['email' => "org{$i}.member@example.com"],
                [
                    'name' => $memberSeedUser->name,
                    'password' => $memberSeedUser->password,
                ]
            );

            $adminUser->syncRoles(['admin']);
            $memberUser->syncRoles(['member']);

            $organization->users()->syncWithoutDetaching([
                $adminUser->id => ['role' => 'admin'],
                $memberUser->id => ['role' => 'member'],
            ]);
        }
    }
}
