<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //membuat akun admin
          User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => hash::make('admin123'),
            'role' => 'admin',
        ]);

        //membuat akun penyewa
        User::create([
            'name' => 'Penyewa',
            'email' => 'penyewa@gmail.com',
            'password' => hash::make('penyewa123'),
            'role' => 'penyewa',
        ]);

        // php artisan db:seed
    }
}
