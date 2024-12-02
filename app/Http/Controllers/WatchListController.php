<?php

namespace App\Http\Controllers;

use App\Http\Resources\Movie\MovieResource;
use App\Http\Resources\WatchList\WatchListCollection;
use App\Models\Movie;
use App\Models\WatchList;
use GuzzleHttp\Client;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchListController extends Controller
{
    /*
    Watchlist have columns: id, user_id, movie_id, and status_id. I want to Get All Movies in Current User’s Watchlist. Endpoint: GET /watchlist
    */

    public function get(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $watchlist = WatchList::where('user_id', $user->id)->get();

        if ($watchlist->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No movies in current user’s watchlist',
            ], 404);
        }

        $watchlistGrouped = $watchlist->groupBy('status_id');

        $data = [];
        foreach ($watchlistGrouped as $status_id => $movies) {
            $data[] = [
                'status_id' => $status_id,
                'count' => $movies->count(),
                'movies' => $movies,
            ];
        }

        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'All movies in current user’s watchlist',
        ]);
    }

    // Get Current User’s Watchlist by Status with Count. Endpoint: GET /watchlist/status/{status_id}
    public function getBasedStatus($status_id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $watchlist = WatchList::where('user_id', $user->id)->where('status_id', $status_id)->get();

        if ($watchlist->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No movies in current user’s watchlist with this status',
            ], 404);
        }

        $data = [
            'status_id' => $status_id,
            'count' => $watchlist->count(),
            'movies' => $watchlist,
        ];

        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'All movies in current user’s watchlist with status',
        ]);
    }

    // create watchlist:
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'movie_id' => 'required|integer',
            'status_id' => 'required|integer',
            'score' => 'nullable|integer',
        ]);

        $movieId = $request->movie_id;

        // Check if movie_id exists in the database
        $movie = Movie::find($movieId);

        if (!$movie) {
            // If not, fetch data from TMDB API
            $client = new Client();
            $response = $client->get("https://api.themoviedb.org/3/movie/{$movieId}", [
                'query' => [
                    'api_key' => env('TMDB_API_KEY')
                ],
            ]);

            $tmdbMovie = json_decode($response->getBody()->getContents(), true);

            if (isset($tmdbMovie['id'])) {
                $movie = Movie::create([
                    'id' => $tmdbMovie['id']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Movie not found in TMDB API',
                ], 404);
            }
        }

        $watchlist = WatchList::create([
            'user_id' => $user->id,
            'movie_id' => $movieId,
            'status_id' => $request->status_id,
            'score' => $request->score,
        ]);

        return response()->json([
            'data' => $watchlist,
            'success' => true,
            'message' => 'Movie added to watchlist',
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $watchlist = WatchList::where('user_id', $user->id)->where('id', $id)->first();

        if (!$watchlist) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found in current user’s watchlist',
            ], 404);
        }

        $request->validate([
            'movie_id' => 'required|integer',
            'status_id' => 'required|integer',
            'score' => 'nullable|integer',
        ]);

        $watchlist->update([
            'movie_id' => $request->movie_id,
            'status_id' => $request->status_id,
            'score' => $request->status_id,
        ]);

        return response()->json([
            'data' => $watchlist,
            'success' => true,
            'message' => 'Movie updated in watchlist',
        ]);
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
            // Filter and format TMDB movie
            $tmdbMovie = [
                'id' => $tmdbMovie['id'],
                'title' => $tmdbMovie['title'],
                'genres' => array_map(function ($genre) {
                    return [
                        'id' => $genre['id'],
                        'name' => $genre['name']
                    ];
                }, $tmdbMovie['genres']),
            ];

            return response()->json(new MovieResource(true, 'Detail Movie fetched in TMDB API successfully', $tmdbMovie));
        }

        // Fetch movie from the database
        $dbMovie = Movie::find($id);

        if ($dbMovie) {
            return response()->json(new MovieResource(true, 'Movie fetched in DB successfully', [
                'id' => $dbMovie->id,
                'title' => $dbMovie->title,
                'genres' => $dbMovie->genres->map(function ($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name
                    ];
                }),
            ]));
        }

        return response()->json([
            'success' => false,
            'message' => 'Movie not found',
        ], 404);
    }

    /*
    Create a process to get every total of every genre in the entire watchlist, so it's like getting all the movie_ids in the watchlist of the current user, and taking the genres and separating them, then grouping them and counting them. The movie_id comes from the watchlist, for genre retrieval use the watchlist movie_id via Tmdb API like the process above.
    */
    public function getGenres()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $watchlist = WatchList::where('user_id', $user->id)->get();

        if ($watchlist->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No movies in current user’s watchlist',
            ], 404);
        }

        $movieIds = $watchlist->pluck('movie_id')->toArray();

        $client = new Client();
        $response = $client->get("https://api.themoviedb.org/3/movie/{$movieIds[0]}", [
            'query' => [
                'api_key' => env('TMDB_API_KEY'),
            ],
        ]);

        $tmdbMovie = json_decode($response->getBody()->getContents(), true);

        if (!isset($tmdbMovie['id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found in TMDB API',
            ], 404);
        }

        $genres = [];
        foreach ($movieIds as $movieId) {
            $response = $client->get("https://api.themoviedb.org/3/movie/{$movieId}", [
                'query' => [
                    'api_key' => env('TMDB_API_KEY'),
                ],
            ]);

            $tmdbMovie = json_decode($response->getBody()->getContents(), true);

            if (isset($tmdbMovie['id'])) {
                foreach ($tmdbMovie['genres'] as $genre) {
                    if (!isset($genres[$genre['id']])) {
                        $genres[$genre['id']] = [
                            'id' => $genre['id'],
                            'name' => $genre['name'],
                            'count' => 1,
                        ];
                    } else {
                        $genres[$genre['id']]['count']++;
                    }
                }
            }
        }

        return response()->json([
            'data' => array_values($genres),
            'success' => true,
            'message' => 'All genres in current user’s watchlist',
        ]);
    }

    public function getRecs()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $watchlist = WatchList::where('user_id', $user->id)->get();

        if ($watchlist->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No movies found in the user’s watchlist',
            ], 404);
        }

        $movieIds = $watchlist->pluck('movie_id')->toArray();

        $client = new Client();
        $genres = [];
        foreach ($movieIds as $movieId) {
            $response = $client->get("https://api.themoviedb.org/3/movie/{$movieId}", [
                'query' => [
                    'api_key' => env('TMDB_API_KEY'),
                ],
            ]);

            $tmdbMovie = json_decode($response->getBody()->getContents(), true);

            if (isset($tmdbMovie['id'])) {
                foreach ($tmdbMovie['genres'] as $genre) {
                    if (!isset($genres[$genre['id']])) {
                        $genres[$genre['id']] = [
                            'id' => $genre['id'],
                            'name' => $genre['name'],
                            'count' => 1,
                        ];
                    } else {
                        $genres[$genre['id']]['count']++;
                    }
                }
            }
        }

        if (empty($genres)) {
            return response()->json([
                'success' => false,
                'message' => 'No genres found in the user’s watchlist',
            ], 404);
        }

        // Get the genre with the highest count with smallest id
        $mostCommonGenre = collect($genres)->sortByDesc('count')->sortBy('id')->first();

        $recommendations = $this->getMovieRecommendationsByGenre($mostCommonGenre['id']);

        return response()->json($recommendations->original);
    }

    /*
    get recommendations based on the most common genre in the user's watchlist
    */

    public function getMovieRecommendationsByGenre($genre_id)
    {
        $client = new Client();
        $response = $client->get("https://api.themoviedb.org/3/discover/movie", [
            'query' => [
                'include_adult' => 'false',
                'include_video' => 'false',
                'language' => 'en-US',
                'page' => '1',
                'sort_by' => 'popularity.desc',
                'with_genres' => $genre_id,
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
                'message' => 'No recommendations found for this genre',
            ], 404);
        }

        return response()->json(new MovieResource(true, 'Recommended movies fetched successfully', $movies));
    }

    // get count of most genre in watchlist current user
    public function getMostGenre()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $watchlist = WatchList::where('user_id', $user->id)->get();

        if ($watchlist->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No movies found in the user’s watchlist',
            ], 404);
        }

        $movieIds = $watchlist->pluck('movie_id')->toArray();

        $client = new Client();
        $genres = [];
        foreach ($movieIds as $movieId) {
            $response = $client->get("https://api.themoviedb.org/3/movie/{$movieId}", [
                'query' => [
                    'api_key' => env('TMDB_API_KEY'),
                ],
            ]);

            $tmdbMovie = json_decode($response->getBody()->getContents(), true);

            if (isset($tmdbMovie['id'])) {
                foreach ($tmdbMovie['genres'] as $genre) {
                    if (!isset($genres[$genre['id']])) {
                        $genres[$genre['id']] = [
                            'id' => $genre['id'],
                            'name' => $genre['name'],
                            'count' => 1,
                        ];
                    } else {
                        $genres[$genre['id']]['count']++;
                    }
                }
            }
        }

        if (empty($genres)) {
            return response()->json([
                'success' => false,
                'message' => 'No genres found in the user’s watchlist',
            ], 404);
        }

        // Get the genre with the highest count with smallest id
        $mostCommonGenre = collect($genres)->sortByDesc('count')->sortBy('id')->first();

        return response()->json([
            'data' => $mostCommonGenre,
            'success' => true,
            'message' => 'Most common genre in the user’s watchlist',
        ]);
    }

    // delete watchlist:
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $watchlist = WatchList::where('user_id', $user->id)->where('id', $id)->first();

        if (!$watchlist) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found in current user’s watchlist',
            ], 404);
        }

        $watchlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Movie deleted from watchlist',
        ]);
    }

}