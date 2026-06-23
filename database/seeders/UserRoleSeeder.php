<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'super@ieams.com', 'role' => 'super_admin'],
            ['name' => 'Admin', 'email' => 'admin@ieams.com', 'role' => 'admin'],
            ['name' => 'Manager', 'email' => 'manager@ieams.com', 'role' => 'manager'],
            ['name' => 'Staff Encoder', 'email' => 'staff@ieams.com', 'role' => 'staff'],
            ['name' => 'Viewer', 'email' => 'viewer@ieams.com', 'role' => 'viewer'],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
                'role' => $user['role'],
            ]);
        }
    }
}