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
            ],
            [
                'user_id' => 2,
                'movie_id' => 10494,
                'status_id' => 3,
            ],
            [
                'user_id' => 3,
                'movie_id' => 257475,
                'status_id' => 2,
            ],
            [
                'user_id' => 4,
                'movie_id' => 8392,
                'status_id' => 3,
            ],
            [
                'user_id' => 5,
                'movie_id' => 615165,
                'status_id' => 2,
            ]
        ];

        foreach ($watchlists as $watchlist) {
            WatchList::create($watchlist);
        }
    }
}
