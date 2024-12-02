<?php

namespace App\Http\Controllers;

use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Movie;
use App\Models\Review;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /*
    review have a user_id, movie_id, and body. First, we need to show all reviews in json format.
    */

    public function index()
    {
        $reviews = Review::with(['user', 'movie'])->latest()->get();

        // Add recommended message to each review
        $reviews->each(function ($review) {
            $review->recommendation_message = $this->getRecommendedMessage($review);
        });

        return new ReviewCollection($reviews);
    }

    // Take the movie score of the user obtained from the Watchlist model, then add a new response that states a movie is recommended or not recommended based on the score.
    private function getRecommendedMessage($review)
    {
        $watchlist = $review->user->watchlists->where('movie_id', $review->movie_id)->first();
        $score = $watchlist ? $watchlist->score : 0;

        if ($score >= 7) {
            return 'Recommended';
        } elseif ($score >= 5) {
            return 'Mixed Feelings';
        } else {
            return 'Not Recommended';
        }
    }

    // create review
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $request->validate([
            'movie_id' => 'required',
            'body' => 'nullable',
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

        $review = Review::create([
            'user_id' => auth()->id(),
            'movie_id' => $request->movie_id,
            'body' => $request->body,
        ]);

        return new ReviewResource($review);
    }

    // update review
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'body' => 'required',
        ]);

        $review->update([
            'body' => $request->body,
        ]);

        return new ReviewResource($review);
    }

    // delete review
    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }
}
