<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create Permissions
        $manageFoldersPermission = Permission::firstOrCreate(['name' => 'manage folders']);
        $viewFoldersPermission = Permission::firstOrCreate(['name' => 'view folders']);

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Give Permissions to Roles
        $adminRole->givePermissionTo($manageFoldersPermission);
        $userRole->givePermissionTo($viewFoldersPermission);
    }
}
