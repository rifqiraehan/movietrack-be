<?php

namespace App\Http\Resources\Review;

use App\Http\Resources\Movie\MovieResource;
use App\Http\Resources\User\UserResource;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'movie_id' => $this->movie_id,
            'body' => $this->body,
            'user_name' => $this->user->username,
            'movie_title' => $this->getMovieTitleFromTMDB($this->movie_id),
            'user_pfp' => $this->user->pfp,
            // 'user' => new UserResource($this->whenLoaded('user')),
            // 'movie' => new MovieResource(true, 'Movie details', $this->whenLoaded('movie')),
            'date' => $this->created_at->format('d M Y'),
        ];
    }

    private function getMovieTitleFromTMDB($movieId)
    {
        $client = new Client();
        $response = $client->get("https://api.themoviedb.org/3/movie/{$movieId}", [
            'query' => [
                'api_key' => env('TMDB_API_KEY')
            ],
        ]);

        $movie = json_decode($response->getBody()->getContents(), true);

        return $movie['title'] ?? 'Unknown Title';
    }
}
