<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Episode extends Model
{
    protected $fillable = [
        'season_id',
        'tvmaze_id',
        'episode_number',
        'name',
        'air_date',
    ];

    protected $casts = [
        'air_date' => 'date',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function watchedEpisode(): HasOne
    {
        return $this->hasOne(WatchedEpisode::class);
    }
}
