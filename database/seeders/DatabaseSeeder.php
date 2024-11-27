<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Artisan;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if the 'Laravel Personal Access Client' exists
        if (Client::where('name', 'Laravel Personal Access Client')->doesntExist()) {
            Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => 'Laravel Personal Access Client'
            ]);
        }
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
