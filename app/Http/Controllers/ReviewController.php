<?php

namespace App\Http\Controllers;

use App\Http\Resources\Review\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /*
    review have a user_id, movie_id, and body. First, we need to show all reviews in json format.
    */

    public function index()
    {
        $reviews = Review::with(['user', 'movie'])->latest()->get();

        return new ReviewResource(true, 'List of Reviews', $reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'movie_id' => $review->movie_id,
                'body' => $review->body,
                'user_name' => $review->user->username,
                'movie_title' => $review->movie->title,
                'user_pfp' => $review->user->pfp,
                'date' => $review->created_at->format('d M Y'),
            ];
        }));
    }
}
