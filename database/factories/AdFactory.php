<?php

namespace Database\Factories;

use App\Models\Ad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ad>
 */
class AdFactory extends Factory
{
    protected $model = Ad::class;

    public function definition(): array
    {
        $conditions = ['new','like_new','used','for_parts'];
        $currencies = ['RSD','EUR','USD'];
        $delivery = [
            ['pickup'],
            ['courier'],
            ['cod'],
            ['pickup','courier'],
            ['courier','cod'],
        ];

        return [
            'user_id' => null,
            'category_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraphs(2, true),
            'price' => $this->faker->numberBetween(1000, 150000),
            'currency' => $this->faker->randomElement($currencies),
            'city' => $this->faker->randomElement(['Beograd','Novi Sad','Niš','Kragujevac','Subotica']),
            'phone' => $this->faker->optional(0.7)->e164PhoneNumber(),
            'condition' => $this->faker->randomElement($conditions),
            'delivery_options' => $this->faker->randomElement($delivery),
            'is_negotiable' => $this->faker->boolean(40),
            'status' => 'active',
            'published_at' => now()->subDays($this->faker->numberBetween(0, 20)),
            'views' => $this->faker->numberBetween(0, 500),
        ];
    }
}

