<?php

namespace Database\Seeders;

use App\Models\WatchList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WatchlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $watchlists = [
            [
                'user_id' => 2,
                'movie_id' => 333623,
                'status_id' => 2,
                'score' => 7,
            ],
            [
                'user_id' => 2,
                'movie_id' => 10494,
                'status_id' => 3,
                'score' => 5,
            ],
            [
                'user_id' => 3,
                'movie_id' => 257475,
                'status_id' => 2,
                'score' => 8,
            ],
            [
                'user_id' => 4,
                'movie_id' => 8392,
                'status_id' => 3,
                'score' => 4,
            ],
            [
                'user_id' => 5,
                'movie_id' => 615165,
                'status_id' => 2,
                'score' => 9,
            ],
            [
                'user_id' => 5,
                'movie_id' => 257475,
                'status_id' => 2,
                'score' => 8,
            ]
        ];

        $movies = [
            747, 8699, 7446, 120467, 546554, 385687, 447365, 985617, 980489, 974635,
            713704, 116104, 811704, 113443, 976785, 758323, 507089, 768362, 955531, 977177
        ];

        foreach ($watchlists as $watchlist) {
            WatchList::create($watchlist);
        }

        for ($i = 0; $i < 10; $i++) {
            WatchList::create([
                'user_id' => rand(6, 7),
                'movie_id' => $movies[$i],
                'status_id' => rand(1, 4),
                'score' => rand(6, 10),
            ]);
        }
    }
}