<?php

namespace App\Http\Controllers;

use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Review;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /*
    review have a user_id, movie_id, and body. First, we need to show all reviews in json format.
    */

    public function index()
    {
        $reviews = Review::with(['user', 'movie'])->latest()->get();

        return new ReviewCollection($reviews);
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
