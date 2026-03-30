<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view admin dashboard',
            'manage datasets',
            'manage imports',
            'publish imports',
            'manage analytics',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $analyst = Role::findOrCreate('analyst', 'web');
        $dataManager = Role::findOrCreate('data_manager', 'web');

        $superAdmin->syncPermissions(Permission::all());
        $analyst->syncPermissions([
            'view admin dashboard',
            'manage analytics',
            'manage imports',
        ]);
        $dataManager->syncPermissions([
            'view admin dashboard',
            'manage datasets',
            'manage imports',
            'publish imports',
        ]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@civicatlas.test'],
            ['name' => 'Atlas Super Admin', 'password' => Hash::make('password')]
        );
        $admin->syncRoles([$superAdmin]);

        $analystUser = User::query()->updateOrCreate(
            ['email' => 'analyst@civicatlas.test'],
            ['name' => 'Atlas Analyst', 'password' => Hash::make('password')]
        );
        $analystUser->syncRoles([$analyst]);

        $dataManagerUser = User::query()->updateOrCreate(
            ['email' => 'manager@civicatlas.test'],
            ['name' => 'Data Manager', 'password' => Hash::make('password')]
        );
        $dataManagerUser->syncRoles([$dataManager]);
    }
}
