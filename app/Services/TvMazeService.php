<?php

namespace App\Services;

use App\Models\Series;
use Illuminate\Support\Facades\Http;

class TvMazeService
{
    public function syncSeries(Series $series): array
    {
        $newSeasons = [];
        $newEpisodes = [];

        $response = Http::get(
            "https://api.tvmaze.com/shows/{$series->tvmaze_id}/seasons"
        );

        $seasons = $response->json();

        foreach ($seasons as $seasonData) {

            $season = $series->seasons()
                ->where('tvmaze_id', $seasonData['id'])
                ->first();

            $isNewSeason = false;

            if (!$season) {

                $season = $series->seasons()->create([
                    'tvmaze_id' => $seasonData['id'],
                    'season_number' => $seasonData['number'],
                ]);

                $newSeasons[] = $season;
                $isNewSeason = true;
            }

            $episodesResponse = Http::get(
                "https://api.tvmaze.com/seasons/{$season->tvmaze_id}/episodes"
            );

            $episodes = $episodesResponse->json();

            foreach ($episodes as $episodeData) {

                $episodeExists = $season->episodes()
                    ->where('tvmaze_id', $episodeData['id'])
                    ->exists();

                if (!$episodeExists) {

                    $episode = $season->episodes()->create([
                        'tvmaze_id' => $episodeData['id'],
                        'episode_number' => $episodeData['number'],
                        'name' => $episodeData['name'],
                        'air_date' => !empty($episodeData['airdate'])
                            ? $episodeData['airdate']
                            : null,
                    ]);

                    if (!$isNewSeason) {
                        $newEpisodes[] = $episode;
                    }
                }
            }
        }

        return [
            'new_seasons' => $newSeasons,
            'new_episodes' => $newEpisodes,
        ];
    }
}
