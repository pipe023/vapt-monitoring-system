<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Superadmin User
        User::create([
            'username' => '960645',
            'role'     => 'superadmin',
            'password' => Hash::make('password123'),
        ]);

        // 2. Create Admin User
        User::create([
            'username' => 'admin',
            'role'     => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // 3. Create Viewer User
        User::create([
            'username' => 'viewer',
            'role'     => 'viewer',
            'password' => Hash::make('password123'),
        ]);
    }
}