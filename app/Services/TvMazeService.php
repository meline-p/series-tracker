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

        // Informations générales de la série
        $showResponse = Http::get(
            "https://api.tvmaze.com/shows/{$series->tvmaze_id}"
        );

        if ($showResponse->successful()) {
            $show = $showResponse->json();

            if (is_array($show) && !empty($show['language'])) {
                $series->update([
                    'language' => $show['language'],
                ]);
            }
        }

        // Récupération des saisons
        $response = Http::get(
            "https://api.tvmaze.com/shows/{$series->tvmaze_id}/seasons"
        );

        if (!$response->successful()) {
            return [
                'new_seasons' => $newSeasons,
                'new_episodes' => $newEpisodes,
            ];
        }

        $seasons = $response->json();

        if (!is_array($seasons)) {
            return [
                'new_seasons' => $newSeasons,
                'new_episodes' => $newEpisodes,
            ];
        }

        foreach ($seasons as $seasonData) {

            if (!is_array($seasonData) || !isset($seasonData['id'])) {
                continue;
            }

            $season = $series->seasons()
                ->where('tvmaze_id', $seasonData['id'])
                ->first();

            $isNewSeason = false;

            if (!$season) {

                $season = $series->seasons()->create([
                    'tvmaze_id' => $seasonData['id'],
                    'season_number' => $seasonData['number'] ?? null,
                ]);

                $newSeasons[] = $season;
                $isNewSeason = true;
            }

            // Récupération des épisodes
            $episodesResponse = Http::get(
                "https://api.tvmaze.com/seasons/{$season->tvmaze_id}/episodes"
            );

            if (!$episodesResponse->successful()) {
                continue;
            }

            $episodes = $episodesResponse->json();

            if (!is_array($episodes)) {
                continue;
            }

            foreach ($episodes as $episodeData) {

                if (!is_array($episodeData) || !isset($episodeData['id'])) {
                    continue;
                }

                $episodeExists = $season->episodes()
                    ->where('tvmaze_id', $episodeData['id'])
                    ->exists();

                if (!$episodeExists) {

                    $episode = $season->episodes()->create([
                        'tvmaze_id' => $episodeData['id'],
                        'episode_number' => $episodeData['number'] ?? null,
                        'name' => $episodeData['name'] ?? 'TBA',
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
