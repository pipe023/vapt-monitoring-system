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
        // 1. Create Executive / Supervisory Users
        User::create([
            'username' => '960645',
            'role'     => User::ROLE_LEGACY_SUPERADMIN,
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'username' => 'OPNS',
            'role'     => User::ROLE_OPNS,
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'username' => 'DUTY SERVER',
            'role'     => User::ROLE_DUTY_SERVER,
            'password' => Hash::make('password123'),
        ]);

        // 2. Create Office / Section Users
        $officeRoles = [
            User::ROLE_POIC_SMSB,
            User::ROLE_POIC_ASDB,
            User::ROLE_POIC_ADMIN,
            User::ROLE_POIC_REB,
        ];

        foreach ($officeRoles as $index => $role) {
            User::create([
                'username' => 'POIC_' . ($index + 1),
                'role'     => $role,
                'password' => Hash::make('password123'),
            ]);
        }

        User::create([
            'username' => 'admin',
            'role'     => User::ROLE_LEGACY_ADMIN,
            'password' => Hash::make('password123'),
        ]);
    }
}