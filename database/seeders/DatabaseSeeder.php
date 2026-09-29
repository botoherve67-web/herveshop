<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hervershop.tg'],
            [
                'name' => 'Hervé (Admin)',
                'password' => Hash::make('changeme123'),
                'is_admin' => true,
            ]
        );

        $categories = [
            'Électronique & téléphones',
            'Mode & chaussures',
            'Maison & accessoires',
            'Beauté & soins',
            'Sacs',
            'Sport',
            'Bijoux',
            'Lunettes',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
