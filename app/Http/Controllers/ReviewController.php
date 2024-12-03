<?php

namespace App\Http\Controllers;

use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
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

    // Get all movies for specific movie based movie_id. Endpoint: GET /movies/{movie_id}/reviews
    public function getMovieReviews($movie_id)
    {
        $reviews = Review::where('movie_id', $movie_id)->latest()->get();

        $reviews->each(function ($review) {
            $review->recommendation_message = $this->getRecommendedMessage($review);
        });

        return new ReviewCollection($reviews);
    }

    // get movie reviews only latest
    public function getLatestMovieReviews($movie_id)
    {
        $reviews = Review::where('movie_id', $movie_id)->latest()->limit(1)->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews found for this movie',
            ], 404);
        }

        return new ReviewCollection($reviews);
    }

    // get review of movie_id by current user's watchlist
    public function getReviewByUser($movie_id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $review = Review::where('movie_id', $movie_id)->where('user_id', $user->id)->first();

        /* if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        } */

        // if the review is not created yet by user, return null
        if (!$review) {
            return response()->json([
                'success' => true,
                'message' => 'Review not found',
                'data' => 'type your review here',
            ]);
        }


        return new ReviewResource($review);
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
            'user_id' => $user->id,
            'movie_id' => $movieId,
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
