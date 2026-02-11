<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria@empresa.com',
                'role' => 'admin',
                'password' => Hash::make('123456'),
            ],
            [
                'name' => 'João Silva',
                'email' => 'joao@empresa.com',
                'role' => 'user',
                'password' => Hash::make('123456'),
            ],
        ];

        foreach ($users as $data) {
            User::query()->updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
