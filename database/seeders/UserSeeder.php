<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::forceCreate([
            'name' => 'Admin Super',
            'email' => 'admin@lestari.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Customer
        User::forceCreate([
            'name' => 'Budi Customer',
            'email' => 'customer@lestari.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);
    }
}
