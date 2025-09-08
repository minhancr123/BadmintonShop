<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@badmintonshop.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '0123456789',
            'address' => '123 Admin Street, Ho Chi Minh City',
            'email_verified_at' => now(),
        ]);

        // Create regular user
        User::create([
            'name' => 'John Doe',
            'email' => 'user@badmintonshop.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'phone' => '0987654321',
            'address' => '456 User Avenue, Ha Noi',
            'email_verified_at' => now(),
        ]);

        // Create more sample users
        User::create([
            'name' => 'Nguyen Van A',
            'email' => 'nguyenvana@email.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'phone' => '0909090909',
            'address' => '789 Nguyen Hue, District 1, Ho Chi Minh City',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Tran Thi B',
            'email' => 'tranthib@email.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'phone' => '0808080808',
            'address' => '321 Le Loi, District 5, Ho Chi Minh City',
            'email_verified_at' => now(),
        ]);
    }
}
