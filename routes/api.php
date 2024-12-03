<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WatchListController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
 */

Route::post('/users/', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

// Get all users
Route::get('/users/all', [UserController::class, 'getAll']);

/*
|--------------------------------------------------------------------------
| Search for Users
|--------------------------------------------------------------------------
| URL: /users/search?query={search_query}
| Method: GET
| Description: Search for users by email or username
*/
Route::get('/users/search', [UserController::class, 'search']);

/*
|--------------------------------------------------------------------------
| Reset Password the user
|--------------------------------------------------------------------------
| URL: /users/{user_id}/reset-password
| Method: PATCH
| Description: Reset the password of a user
*/
Route::patch('/users/{id}/reset-password', [UserController::class, 'resetPassword']);

Route::middleware(ApiAuthMiddleware::class)->group(function () {
    Route::get('/users', [UserController::class, 'get']);
    Route::patch('/users', [UserController::class, 'update']);
    Route::post('/users/logout', [UserController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Get All Movies in Current User’s Watchlist
    |--------------------------------------------------------------------------
    | URL: /watchlists
    | Method: GET
    | Description: Fetch all movies in the current user's watchlist
    */
    Route::get('/watchlists', [WatchListController::class, 'get']);

    /*
    |--------------------------------------------------------------------------
    | Get Current User’s Watchlist by Status
    |--------------------------------------------------------------------------
    | URL: /watchlists/{status_id}
    | Method: GET
    | Description: Fetch all movies in the current user's watchlist by status
    */
    Route::get('/watchlists/{status_id}', [WatchListController::class, 'getBasedStatus']);

    // Get the count of each genres in all movies in the current user watchlist
    Route::get('/watchlist/genres', [WatchListController::class, 'getGenres']);

    // Get the recommended movies based on the current user watchlist
    Route::get('/recs/dynamic', [WatchListController::class, 'getRecs']);

    // Get the count of the most genre in the current user watchlist
    Route::get('/watchlist/most-genre', [WatchListController::class, 'getMostGenre']);

    // Get the count of each status in the current user watchlist
    Route::get('/watchlist/status-count', [WatchListController::class, 'getStatusCount']);

    // add to watchlist
    Route::post('/watchlists', [WatchListController::class, 'store']);
    Route::patch('/watchlists/id/', [WatchListController::class, 'update']);
    Route::delete('/watchlists/id/', [WatchListController::class, 'destroy']);

    Route::get('/movies/{movie_id}/reviews/user', [ReviewController::class, 'getReviewByUser']);
    
    Route::post('/review', [ReviewController::class, 'store']);
    Route::patch('/movies/{movie_id}/reviews', [WatchListController::class, 'update']);
    Route::delete('/movies/{movie_id}/reviews', [WatchListController::class, 'destroy']);

});

/*
|--------------------------------------------------------------------------
| Get All Movies (with optional search)
|--------------------------------------------------------------------------
| URL: /api/movies?query={search_query}
| Method: GET
| Description: Fetch all movies from the database and TMDB API
*/
Route::get('/movies', [MovieController::class, 'searchMovies']);
Route::post('/movies', [MovieController::class, 'createMovies']);
Route::patch('/movies/{id}', [MovieController::class, 'updateMovies']);
Route::delete('/movies/{id}', [MovieController::class, 'deleteMovies']);

/*
|--------------------------------------------------------------------------
| Get Movie Details
|--------------------------------------------------------------------------
| URL: /api/movies/{id}
| Method: GET
| Description: Fetch the details of a movie by ID
*/
Route::get('/movies/{id}', [MovieController::class, 'getMovie']);

/*
|--------------------------------------------------------------------------
| Get All Reviews for a Movie
|--------------------------------------------------------------------------
| URL: /movies/{id}/reviews
| Method: GET
| Description: Fetch all reviews for a specific movie
*/
Route::get('/movies/{movie_id}/reviews', [ReviewController::class, 'getMovieReviews']);
Route::get('/movies/{movie_id}/reviews/latest', [ReviewController::class, 'getLatestMovieReviews']);

/*
|--------------------------------------------------------------------------
| Get All Reviews for all Movies
|--------------------------------------------------------------------------
| URL: /reviews
| Method: GET
| Description: Fetch all reviews for all movies
*/
Route::get('/reviews', [ReviewController::class, 'index']);


/*
|--------------------------------------------------------------------------
| Get All Recommended Movies
|--------------------------------------------------------------------------
| URL: /recs/{type}
| Method: GET
| Description: Fetch all recommended movies based on the type
*/
Route::get('/recs/top-rated', [MovieController::class, 'getTopRatedMovies']);
Route::get('/recs/popular', [MovieController::class, 'getPopularMovies']);
Route::get('/recs/upcoming', [MovieController::class, 'getUpcomingMovies']);
Route::get('/recs/now-playing', [MovieController::class, 'getNowPlayingMovies']);
Route::get('/recs', function () {
    return response()->json([
        'success' => false,
        'message' => 'You need to specify the type of recommendation.',
    ], 400);
});
Route::get('/movies/{movie_id}/recommendations', [MovieController::class, 'getMovieRecommendations']);

