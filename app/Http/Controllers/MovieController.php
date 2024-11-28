<?php

namespace App\Http\Controllers;

use App\Http\Requests\Movie\MovieSearchRequest;
use App\Http\Resources\Movie\MovieResource;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MovieController extends Controller {
    public function searchMovies(MovieSearchRequest $request)
    {
        $query = $request->input('query');

        // Fetch movies from the database
        $dbMovies = Movie::where('title', 'like', "%{$query}%")->get(['id', 'title', 'poster_path', 'release_date']);

        // Fetch movies from TMDB API
        $client = new Client();
        $response = $client->get('https://api.themoviedb.org/3/search/movie', [
            'query' => [
                'query' => $query,
                'language' => 'id-ID',
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $tmdbMovies = json_decode($response->getBody()->getContents(), true)['results'];

        // Filter and format TMDB movies
        $tmdbMovies = array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'],
                'release_date' => $movie['release_date'],
            ];
        }, $tmdbMovies);

        // Combine DB movies and TMDB movies
        $movies = $dbMovies->toArray();
        $movies = array_merge($movies, $tmdbMovies);

        return response()->json(new MovieResource(true, 'Movies fetched successfully', $movies));
    }

    public function getMovie($id)
    {
        // Fetch movie from the database
        $dbMovie = Movie::find($id);

        if ($dbMovie) {
            return response()->json(new MovieResource(true, 'Movie fetched in DB successfully', $dbMovie));
        }

        // Fetch movie from TMDB API
        $client = new Client();
        $response = $client->get("https://api.themoviedb.org/3/movie/{$id}", [
            'query' => [
                'language' => 'id-ID',
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $tmdbMovie = json_decode($response->getBody()->getContents(), true);

        // Safely access the first production company name
        $firstProductionCompanyName = isset($tmdbMovie['production_companies'][0])
            ? $tmdbMovie['production_companies'][0]['name']
            : null;

        // Filter and format TMDB movie
        $tmdbMovie = [
            'id' => $tmdbMovie['id'],
            'title' => $tmdbMovie['title'],
            'poster_path' => $tmdbMovie['poster_path'],
            'release_date' => $tmdbMovie['release_date'],
            'genres' => array_map(function ($genre) {
                return $genre['name'];
            }, $tmdbMovie['genres']),
            'overview' => $tmdbMovie['overview'],
            'production_companies' => [
                'name' => $firstProductionCompanyName
            ],
            'runtime' => $tmdbMovie['runtime'],
            'status' => $tmdbMovie['status'],
        ];

        return response()->json(new MovieResource(true, 'Movie fetched in TDMB API successfully', $tmdbMovie));
    }

    // Get all movies for specific movie based movie_id. Endpoint: GET /movies/{movie_id}/reviews
    public function getMovieReviews($movie_id)
{
    $reviews = Review::where('movie_id', $movie_id)->latest()->get();

    if ($reviews->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No reviews found for this movie',
        ], 404);
    }

    return response()->json(new ReviewResource(true, 'All reviews for movie', $reviews));
}
}
