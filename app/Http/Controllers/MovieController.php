<?php

namespace App\Http\Controllers;

use App\Http\Requests\Movie\MovieSearchRequest;
use App\Http\Resources\Movie\MovieResource;
use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Movie;
use App\Models\Review;
use App\Models\WatchList;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

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

    // create movie with genre fetch in Genre Model and save in movie_genres table
    public function createMovie(Request $request)
    {
        // i have genre table for save the genre name and its id, and i have pivot table movie_genres for save the movie_id and genre_id, in movie model i have many to many relationship with genre model, so i dont have genre id in movie table
        $request->validate([
            'title' => 'nullable',
            'poster_path' => 'nullable',
            'vote_average' => 'nullable',
            'production_name' => 'nullable',
            'duration' => 'nullable',
            'status' => 'nullable',
            'release_date' => 'nullable',
            'overview' => 'nullable',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
        ]);

        $movie = Movie::create($request->only([
            'title',
            'poster_path',
            'vote_average',
            'production_name',
            'duration',
            'status',
            'release_date',
            'overview',
        ]));

        $movie->genres()->sync($request->input('genres'));

        return response()->json(new MovieResource(true, 'Movie created successfully', $movie));

        // how handle this endpoint on postman:
        // endpoint: POST /api/movies
        // body: raw JSON
        // {
        //     "title": "The Shawshank Redemption",
        //     "poster_path": "/q6y0Go1tsGEs6jGyGz4p3wYfR2E.jpg",
        //     "vote_average": 8.7,
        //     "production_name": "Castle Rock Entertainment",
        //     "duration": 142,
        //     "status": "Released",
        //     "release_date": "1994-09-23",
        //     "overview": "Framed in the 1940s for the double
        //
        //     "genres": [1, 2]
        // }
    }

    // update movie with genre fetch in Genre Model and save in movie_genres table
    public function updateMovie(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable',
            'poster_path' => 'nullable',
            'vote_average' => 'nullable',
            'production_name' => 'nullable',
            'duration' => 'nullable',
            'status' => 'nullable',
            'release_date' => 'nullable',
            'overview' => 'nullable',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
        ]);

        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found',
            ], 404);
        }

        $movie->update($request->only([
            'title',
            'poster_path',
            'vote_average',
            'production_name',
            'duration',
            'status',
            'release_date',
            'overview',
        ]));

        $movie->genres()->sync($request->input('genres'));

        return response()->json(new MovieResource(true, 'Movie updated successfully', $movie));
    }

    // delete movie with genre fetch in Genre Model and save in movie_genres table
    public function deleteMovie($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found',
            ], 404);
        }

        $movie->genres()->detach();
        $movie->delete();

        return response()->json(new MovieResource(true, 'Movie deleted successfully', $movie));
    }
}
