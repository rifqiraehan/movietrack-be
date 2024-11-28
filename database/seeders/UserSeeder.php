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
            'pfp' => 'akari.jpeg'
        ]);

        User::create([
            'username' => 'AkebiKomichi',
            'email' => 'akebikomichi@gmail.com',
            'password' => Hash::make('rahasia'),
            'token' => 'tests',
            'pfp' => 'akebi.jpeg'
        ]);

        User::create([
            'username' => 'raehan',
            'email' => 'raehan@gmail.com',
            'password' => Hash::make('secret'),
            'token' => 'ujicoba',
            'pfp' => 'default.jpeg'
        ]);

        User::create([
            'username' => 'tohru',
            'email' => 'hondatohru@gmail.com',
            'password' => Hash::make('rahasia'),
            'token' => 'testo',
            'pfp' => 'lain.jpeg'
        ]);

        User::create([
            'username' => 'hime',
            'email' => 'hime@gmail.com',
            'password' => Hash::make('secret'),
            'token' => 'cobacoba',
            'pfp' => 'akari.jpeg'
        ]);
    }
}