<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SuperAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Superadmin',
            'username' => 'superadmin',
            'email' => 'superadmin@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('123123123'),
            'user_type' => 'superadmin',
            'remember_token' => Str::random(10),
        ]);
    }
}
