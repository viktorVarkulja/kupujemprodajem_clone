<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo users with known password
        $usersData = [
            ['name' => 'Ana Admin', 'email' => 'ana@example.com'],
            ['name' => 'Marko Prodavac', 'email' => 'marko@example.com'],
            ['name' => 'Jelena Kupac', 'email' => 'jelena@example.com'],
            ['name' => 'Ivan Korisnik', 'email' => 'ivan@example.com'],
            ['name' => 'Mila Test', 'email' => 'mila@example.com'],
        ];

        $users = collect();
        foreach ($usersData as $data) {
            $users->push(User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('12345678'),
                    'email_verified_at' => now(),
                ]
            ));
        }

        // Ensure there are categories
        $categoryIds = Category::query()->pluck('id')->all();
        if (count($categoryIds) === 0) {
            $this->command?->warn('No categories found; skipping ad seeding.');
            return;
        }

        // Create a handful of ads for first 3 users
        $owners = $users->take(3);
        foreach ($owners as $owner) {
            Ad::factory()
                ->count(3)
                ->state(function () use ($owner, $categoryIds) {
                    return [
                        'user_id' => $owner->id,
                        'category_id' => fake()->randomElement($categoryIds),
                    ];
                })
                ->create();
        }
    }
}

