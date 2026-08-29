<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    protected $fillable = [
        'tvmaze_id',
        'name',
        'image_url',
        'language',
    ];

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function getNextEpisodeAttribute()
    {
        return $this->seasons
            ->sortBy('season_number')
            ->flatMap(function ($season) {
                return $season->episodes
                    ->sortBy(function ($episode) {
                        return $episode->episode_number ?? PHP_INT_MAX;
                    });
            })
            ->first(function ($episode) {
                return !$episode->watchedEpisode;
            });
    }

    public function getStatusAttribute(): string
    {
        if ($this->nextEpisode) {
            return 'watching';
        }

        if ($this->seasons->contains(fn($season) => $season->episodes->isEmpty())) {
            return 'up_to_date';
        }

        return 'completed';
    }

    public function getStatusOrderAttribute(): int
    {
        return match ($this->status) {
            'watching' => 0,
            'up_to_date' => 1,
            'completed' => 2,
        };
    }

    public function getFirstEpisodeAttribute()
    {
        return $this->seasons
            ->sortBy('season_number')
            ->flatMap(fn($season) => $season->episodes->sortBy('episode_number'))
            ->filter(fn($episode) => $episode->air_date)
            ->first();
    }

    public function getLastEpisodeAttribute()
    {
        return $this->seasons
            ->sortByDesc('season_number')
            ->flatMap(fn($season) => $season->episodes->sortByDesc('episode_number'))
            ->filter(fn($episode) => $episode->air_date)
            ->first();
    }

    public function getDateRangeAttribute(): ?string
    {
        $firstEpisode = $this->firstEpisode;
        $lastEpisode = $this->lastEpisode;

        if (!$firstEpisode || !$lastEpisode) {
            return null;
        }

        $firstYear = $firstEpisode->air_date->format('Y');
        $lastYear = $lastEpisode->air_date->format('Y');

        return $firstYear === $lastYear
            ? $firstYear
            : "{$firstYear}-{$lastYear}";
    }
}
