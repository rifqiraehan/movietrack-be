<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $guarded = [];

    public function watchlists(): HasMany
    {
        return $this->hasMany(WatchList::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
