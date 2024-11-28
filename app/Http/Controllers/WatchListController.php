<?php

namespace App\Http\Controllers;

use App\Http\Resources\WatchList\WatchListResource;
use App\Models\WatchList;
use Illuminate\Http\Request;

class WatchListController extends Controller
{
    /*
    Watchlist have columns: id, user_id, movie_id, and status_id. I want to Get All Movies in Current User’s Watchlist. Endpoint: GET /watchlist
    */

    public function index(Request $request)
    {
        $user = auth()->user();
        $token = $request->bearerToken();

        if ($user && $user->token() && $user->token()->id === $token) {
            $watchlist = WatchList::where('user_id', $user->id)->get();
            return new WatchListResource(true, 'All movies in current user’s watchlist', $watchlist);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /* public function index()
    {
        $watchlist = WatchList::where('user_id', auth()->id())->get();
        return new WatchListResource(true, 'All movies in current user’s watchlist', $watchlist);
    } */

    // Get Current User’s Watchlist by Status. Endpoint: GET /watchlist/{status_id}
    public function show($status_id)
    {
        $watchlist = WatchList::where('user_id', auth()->id())->where('status_id', $status_id)->get();
        return new WatchListResource(true, 'All movies in current user’s watchlist by status', $watchlist);
    }
}
