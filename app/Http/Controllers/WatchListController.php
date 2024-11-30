<?php

namespace App\Http\Controllers;

use App\Http\Resources\WatchList\WatchListCollection;
use App\Models\WatchList;
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

        return (new WatchListCollection($watchlist))->additional([
            'success' => true,
            'message' => 'All movies in current user’s watchlist',
        ]);
    }

    // Get Current User’s Watchlist by Status. Endpoint: GET /watchlist/{status_id}
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

        return (new WatchListCollection($watchlist))->additional([
            'success' => true,
            'message' => 'All movies in current user’s watchlist with status',
        ]);
    }
}