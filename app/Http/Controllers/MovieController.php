<?php

namespace App\Http\Controllers;

use App\Http\Requests\Movie\MovieSearchRequest;
use App\Http\Resources\Movie\MovieResource;
use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MovieController extends Controller
{
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

        // Combine DB movies and TMDB movies, prioritizing DB movies
        $movies = $dbMovies->toArray();
        $dbMovieIds = $dbMovies->pluck('id')->toArray();

        foreach ($tmdbMovies as $tmdbMovie) {
            if (!in_array($tmdbMovie['id'], $dbMovieIds)) {
                $movies[] = $tmdbMovie;
            }
        }

        return response()->json(new MovieResource(true, 'Movies fetched successfully', $movies));
    }

    public function getMovie($id)
    {
        // Fetch movie from TMDB API
        $client = new Client();
        $response = $client->get("https://api.themoviedb.org/3/movie/{$id}", [
            'query' => [
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $tmdbMovie = json_decode($response->getBody()->getContents(), true);

        if (isset($tmdbMovie['id'])) {
            // Safely access the first production company name
            $firstProductionCompanyName = isset($tmdbMovie['production_companies'][0])
                ? $tmdbMovie['production_companies'][0]['name']
                : null;

            $translatedOverview = $this->translatedOverview($tmdbMovie['overview']);

            // Filter and format TMDB movie
            $tmdbMovie = [
                'id' => $tmdbMovie['id'],
                'title' => $tmdbMovie['title'],
                'poster_path' => $tmdbMovie['poster_path'],
                'release_date' => $tmdbMovie['release_date'],
                'genres' => array_map(function ($genre) {
                    return [
                        'id' => $genre['id'],
                        'name' => $genre['name']
                    ];
                }, $tmdbMovie['genres']),
                'overview' => $translatedOverview,
                'production_companies' => [
                    'name' => $firstProductionCompanyName
                ],
                'runtime' => $tmdbMovie['runtime'],
                'status' => $tmdbMovie['status'],
                'vote_average' => number_format($tmdbMovie['vote_average'], 1),
            ];

            return response()->json(new MovieResource(true, 'Detail Movie fetched in TMDB API successfully', $tmdbMovie));
        }

        // Fetch movie from the database
        $dbMovie = Movie::find($id);

        if ($dbMovie) {
            return response()->json(new MovieResource(true, 'Movie fetched in DB successfully', $dbMovie));
        }

        return response()->json([
            'success' => false,
            'message' => 'Movie not found',
        ], 404);
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

        return new ReviewCollection($reviews);
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

    /*
    get recommendations based input movie_id the endpoint: GET https://api.themoviedb.org/3/movie/{movie_id}/recommendations
    */

    public function getMovieRecommendations($movie_id)
    {
        $client = new Client();
        $response = $client->get("https://api.themoviedb.org/3/movie/{$movie_id}/recommendations", [
            'query' => [
                'language' => 'id-ID',
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $movies = json_decode($response->getBody()->getContents(), true)['results'];

        $movies = array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'],
            ];
        }, $movies);

        if (empty($movies)) {
            return response()->json([
                'success' => false,
                'message' => 'No recommendations found for this movie',
            ], 404);
        }

        return response()->json(new MovieResource(true, 'Recommended movies fetched successfully', $movies));
    }

    public function getTopRatedMovies()
    {
        $client = new Client();
        $response = $client->get('https://api.themoviedb.org/3/movie/top_rated', [
            'query' => [
                'language' => 'id-ID',
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $movies = json_decode($response->getBody()->getContents(), true)['results'];

        $movies = array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'],
            ];
        }, $movies);

        return response()->json(new MovieResource(true, 'Top rated movies fetched successfully', $movies));
    }

    public function getPopularMovies()
    {
        $client = new Client();
        $response = $client->get('https://api.themoviedb.org/3/movie/popular', [
            'query' => [
                'language' => 'id-ID',
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $movies = json_decode($response->getBody()->getContents(), true)['results'];

        $movies = array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'],
            ];
        }, $movies);

        return response()->json(new MovieResource(true, 'Popular movies fetched successfully', $movies));
    }

    public function getUpcomingMovies()
    {
        $client = new Client();
        $response = $client->get('https://api.themoviedb.org/3/movie/upcoming', [
            'query' => [
                'language' => 'id-ID',
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $movies = json_decode($response->getBody()->getContents(), true)['results'];

        $movies = array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'],
            ];
        }, $movies);

        return response()->json(new MovieResource(true, 'Upcoming movies fetched successfully', $movies));
    }

    public function getNowPlayingMovies()
    {
        $client = new Client();
        $response = $client->get('https://api.themoviedb.org/3/movie/now_playing', [
            'query' => [
                'language' => 'id-ID',
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $movies = json_decode($response->getBody()->getContents(), true)['results'];

        $movies = array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'],
            ];
        }, $movies);

        return response()->json(new MovieResource(true, 'Now Playing movies fetched successfully', $movies));
    }
}
