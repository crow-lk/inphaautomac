<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\UserSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // You can call other seeders here. Register UserSeeder to seed users and roles.
        $this->call([
            UserSeeder::class,
        ]);
    }
}
