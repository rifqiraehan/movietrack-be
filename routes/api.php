<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
 */

Route::post('/users/', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::middleware(ApiAuthMiddleware::class)->group(function () {
    Route::get('/users', [UserController::class, 'get']);
    Route::patch('/users', [UserController::class, 'update']);
    Route::post('/users/logout', [UserController::class, 'logout']);

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


/*
|--------------------------------------------------------------------------
| Get Movie Details
|--------------------------------------------------------------------------
| URL: /api/movies/{id}
| Method: GET
| Description: Fetch the details of a movie by ID
*/
Route::get('/movies/{id}', [MovieController::class, 'getMovie']);

