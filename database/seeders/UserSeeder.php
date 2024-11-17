<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'Chieru16',
            'email' => 'rifqiraehan86@gmail.com',
            'password' => Hash::make('rahasia'),
            'is_admin' => 1,
            'token' => 'test',
            'pfp' => null
        ]);

        User::create([
            'username' => 'AkebiKomichi',
            'email' => 'akebikomichi@gmail.com',
            'password' => Hash::make('rahasia'),
            'token' => 'tests',
            'pfp' => null
        ]);
    }
}