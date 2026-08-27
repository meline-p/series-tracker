<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'series_id',
        'tvmaze_id',
        'season_number',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function sortedEpisodes()
    {
        return $this->episodes
            ->sortBy(function ($episode) {
                return $episode->episode_number === null
                    ? PHP_INT_MAX
                    : $episode->episode_number;
            });
    }
}
