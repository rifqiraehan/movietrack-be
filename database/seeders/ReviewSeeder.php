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
            ]
        ];

        foreach ($reviews as $review) {
            $this->createReviewWithMovie($review);
        }
    }

    private function createReviewWithMovie(array $review)
    {
        $movieId = $review['movie_id'];

        // Periksa apakah movie_id ada di database
        $movie = Movie::find($movieId);

        if (!$movie) {
            // Jika tidak ada, ambil data dari API TMDB
            $client = new Client();
            $response = $client->get("https://api.themoviedb.org/3/movie/{$movieId}", [
                'query' => [
                    'api_key' => env('TMDB_API_KEY')
                ],
            ]);

            $tmdbMovie = json_decode($response->getBody()->getContents(), true);

            // Simpan data movie dari TMDB ke database
            $translatedOverview = $this->translatedOverview($tmdbMovie['overview']);
            $movie = Movie::create([
                'id' => $tmdbMovie['id'],
                // 'title' => $tmdbMovie['title'],
                // 'poster_path' => $tmdbMovie['poster_path'],
                // 'release_date' => $tmdbMovie['release_date'],
                // 'overview' => $translatedOverview,
                // 'production_name' => $tmdbMovie['production_companies'][0]['name'] ?? null,
                // 'duration' => $tmdbMovie['runtime'],
                // 'status' => $tmdbMovie['status'],
                // 'vote_average' => $tmdbMovie['vote_average'],
            ]);
        }

        // Buat review baru
        Review::create([
            'user_id' => $review['user_id'],
            'movie_id' => $movie->id,
            'body' => $review['body'],
        ]);
    }

    private function translatedOverview(string $overview): string
    {
        $deeplClient = new Client();
        try {
            $deeplResponse = $deeplClient->post('https://api-free.deepl.com/v2/translate', [
                'headers' => [
                    'Authorization' => 'DeepL-Auth-Key ' . env('DEEPL_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'text' => [$overview],
                    'target_lang' => 'ID'
                ],
            ]);

            $deeplTranslation = json_decode($deeplResponse->getBody()->getContents(), true);
            return $deeplTranslation['translations'][0]['text'];
        } catch (RequestException $e) {
            return $overview;
        }
    }
}