<?php

namespace App\Http\Resources\WatchList;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Movie\MovieResource;
use App\Http\Resources\Status\StatusResource;

class WatchListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'movie_id' => $this->movie_id,
            'status_id' => $this->status_id,
            'movie' => new MovieResource(true, 'Movie details', $this->whenLoaded('movie')),
            'status' => new StatusResource(true, 'Status details', $this->whenLoaded('status')),
        ];
    }
}