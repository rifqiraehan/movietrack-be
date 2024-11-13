<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Action',
            'Adventure',
            'Animation',
            'Comedy',
            'Crime',
            'Documentary',
            'Drama',
            'Family',
            'Fantasy',
            'History',
            'Horror',
            'Music',
            'Mystery',
            'Romance',
            'Science Fiction',
            'TV Movie',
            'Thriller',
            'War',
            'Western',
        ];

        $ids = [
            28,
            12,
            16,
            35,
            80,
            99,
            18,
            10751,
            14,
            36,
            27,
            10402,
            9648,
            10749,
            878,
            10770,
            53,
            10752,
            37,
        ];

        foreach ($genres as $key => $genre) {
            \DB::table('genres')->insert([
                'id' => $ids[$key],
                'name' => $genre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}