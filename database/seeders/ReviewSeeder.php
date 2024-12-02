<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Movie;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            [
                'user_id' => 2,
                'movie_id' => 333623,
                'body' => 'This movie was amazing! I loved it! I would definitely recommend it to anyone. 10/10! I want to watch it again!',
            ],
            [
                'user_id' => 2,
                'movie_id' => 10494,
                'body' => 'I hated this movie. It was terrible. I would not recommend it to anyone. 0/10. I want my money back. I want my time back. I want my life back. I want my soul back.',
            ],
            [
                'user_id' => 3,
                'movie_id' => 257475,
                'body' => 'This movie was okay. I liked it. I would recommend it to some people. 5/10. I would watch it again if I had nothing else to do. I would not watch it again if I had something else to do.',
            ],
            [
                'user_id' => 4,
                'movie_id' => 8392,
                'body' => 'This movie was terrible. I hated it. I would not recommend it to anyone. 0/10. I want my money back. I want my time back. I want my life back. I want my soul back.',
            ],
            [
                'user_id' => 5,
                'movie_id' => 615165,
                'body' => 'This movie was amazing! I loved it! I would definitely recommend it to anyone. 10/10! I want to watch it again!',
            ],
            [
                'user_id' => 5,
                'movie_id' => 257475,
                'body' => 'Wow, what a great movie! I don\'t know why I waited so long to watch it. I\'m definitely going to watch it again soon. But first, I need to watch the sequel. i watched this movie with my friends and we all loved it. I highly recommend it to everyone. It\'s a must-watch! I can\'t wait to watch it again. Yaay!',
            ],
            [
                'user_id' => 2,
                'movie_id' => 615165,
                'body' => 'This movie is terrible.',
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}