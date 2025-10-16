<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Cars' => [
                'Sedan', 'SUV', 'Hatchback', 'Coupe', 'Convertible', 'Electric', 'Motorcycles'
            ],
            'Real Estate' => [
                'Apartments', 'Houses', 'Land', 'Commercial'
            ],
            'Electronics' => [
                'Phones', 'Laptops', 'Tablets', 'TVs', 'Audio', 'Cameras'
            ],
            'Clothing' => [
                'Men', 'Women', 'Kids', 'Shoes', 'Accessories'
            ],
            'Home & Garden' => [
                'Furniture', 'Appliances', 'Tools', 'Garden'
            ],
            'Sports & Leisure' => [
                'Fitness', 'Cycling', 'Camping', 'Games'
            ],
        ];

        foreach ($data as $parentName => $children) {
            $parent = Category::query()->firstOrCreate(
                ['slug' => Str::slug($parentName)],
                ['name' => $parentName]
            );

            foreach ($children as $childName) {
                Category::query()->firstOrCreate(
                    ['slug' => Str::slug($parentName.'-'.$childName)],
                    ['name' => $childName, 'parent_id' => $parent->id]
                );
            }
        }
    }
}

