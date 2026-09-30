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
        $adminPassword = env('ADMIN_PASSWORD');

        if ($adminPassword) {
            $adminEmail = env('ADMIN_EMAIL', 'intelligencecelebre@gmail.com');
            User::where('is_admin', true)
                ->where(function ($query) use ($adminEmail) {
                    $query->where('email', '!=', $adminEmail)
                        ->orWhere('email', 'admin@hervershop.tg');
                })
                ->update(['is_admin' => false, 'admin_role' => null]);

            User::updateOrCreate(
                ['email' => $adminEmail],
            [
                'name' => 'Hervé (Admin)',
                'password' => Hash::make($adminPassword),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
            );
        }

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
