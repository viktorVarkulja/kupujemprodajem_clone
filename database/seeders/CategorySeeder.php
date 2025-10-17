<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Flat list of KP categories. A pipe (|) indicates parent | child relationship.
        $entries = [
            'Alati i oruđa',
            'Antikviteti',
            'Asesoari',
            'Audio',
            'Audio | Vinili, CD i kasete',
            'Auto delovi i alati',
            'Auto oprema',
            'Automobili',
            'Bela tehnika i kućni aparati',
            'Bicikli',
            'Bicikli | Delovi i oprema',
            'Časopisi i stripovi',
            'Dvorište i bašta',
            'Elektro i rasveta',
            'Elektronika i komponente',
            'Etno stvari',
            'Fitnes i vežbanje',
            'Foto-aparati i kamere',
            'Građevinarstvo',
            'Građevinske mašine',
            'Igračke i igre',
            'Industrijska oprema',
            'Knjige',
            'Kolekcionarstvo',
            'Kompjuteri | Desktop',
            'Kompjuteri | Laptop i tablet',
            'Konzole i igrice',
            'Kućni ljubimci',
            'Kućni ljubimci | Oprema',
            'Kupatilo i oprema',
            'Lov i ribolov',
            'Mama i beba',
            'Mobilni tel. | Oprema i delovi',
            'Mobilni telefoni',
            'Motocikli',
            'Motocikli | Oprema i delovi',
            'Muzički instrumenti',
            'Nakit i dragocenosti',
            'Nameštaj',
            'Nega i lična higijena',
            'Nekretnine | Izdavanje',
            'Nekretnine | Prodaja',
            'Obuća | Dečja',
            'Obuća | Muška',
            'Obuća | Ženska',
            'Odeća | Dečja',
            'Odeća | Muška',
            'Odeća | Ženska',
            'Odmor u Srbiji',
            'Oprema u zdravstvu',
            'Oprema za poslovanje',
            'Plovni objekti',
            'Poljoprivreda',
            'Poljoprivreda | Domaće životinje',
            'Ručni i džepni satovi',
            'Ručni radovi',
            'Sport i razonoda',
            'Sve za školu',
            'Transportna vozila',
            'Transportna vozila | Delovi i oprema',
            'TV i Video',
            'Ugostiteljstvo | Oprema',
            'Umetnička dela i materijali',
            'Uređenje kuće',
            'Usluge',
            'Poslovi',
        ];

        foreach ($entries as $entry) {
            $entry = trim($entry);
            if ($entry === '') continue;

            if (str_contains($entry, '|')) {
                [$parentName, $childName] = array_map(static fn($s) => trim($s), explode('|', $entry, 2));

                $parent = Category::query()->firstOrCreate(
                    ['slug' => Str::slug($parentName)],
                    ['name' => $parentName]
                );

                Category::query()->firstOrCreate(
                    ['slug' => Str::slug($parentName.' '.$childName)],
                    ['name' => $childName, 'parent_id' => $parent->id]
                );
            } else {
                Category::query()->firstOrCreate(
                    ['slug' => Str::slug($entry)],
                    ['name' => $entry]
                );
            }
        }
    }
}
