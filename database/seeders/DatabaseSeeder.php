<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed categories hierarchy
        $this->call([
            CategorySeeder::class,
        ]);

        // Optional: a demo user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Demo users and ads (with known password '12345678')
        $this->call([
            DemoDataSeeder::class,
        ]);

        // Seed chat demo data (users, conversations, messages) and images for ads
        $this->call([
            ChatSeeder::class,
            AdImageSeeder::class,
        ]);
    }
}
