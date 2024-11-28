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
        $reviews = Review::latest()->get();

        return new ReviewResource(true, 'All reviews', $reviews);
    }
}
