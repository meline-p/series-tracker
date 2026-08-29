<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Season;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\TvMazeService;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        $series = Series::with([
            'seasons.episodes.watchedEpisode',
        ])
            ->when($request->filled('language'), function ($query) use ($request) {
                $query->where('language', $request->language);
            })
            ->get();

        // Filtre par statut
        if ($request->filled('status')) {
            $series = $series->filter(function ($serie) use ($request) {
                return $serie->status === $request->status;
            });
        }

        $series = $this->sortSeries($series);

        $languages = Series::whereNotNull('language')
            ->where('language', '!=', '')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        return view('series.index', compact('series', 'languages'));
    }

    private function sortSeries($series)
    {
        return $series
            ->sort(function ($a, $b) {

                // 1. Statut : en cours → à jour → terminée
                if ($a->status_order !== $b->status_order) {
                    return $a->status_order <=> $b->status_order;
                }

                // 2. Même statut : ordre chronologique adapté
                if ($a->status === 'watching') {
                    return $this->compareLastWatched($a, $b);
                }

                // À jour + terminées :
                // dernière date d'épisode la plus récente en premier
                return $this->compareLastEpisode($a, $b);
            })
            ->values();
    }

    private function compareLastWatched($seriesA, $seriesB)
    {
        $dateA = $this->getLastWatchedDate($seriesA);
        $dateB = $this->getLastWatchedDate($seriesB);

        return ($dateB?->timestamp ?? 0) <=> ($dateA?->timestamp ?? 0);
    }

    private function compareLastEpisode($seriesA, $seriesB)
    {
        $dateA = $seriesA->lastEpisode?->air_date?->timestamp ?? 0;
        $dateB = $seriesB->lastEpisode?->air_date?->timestamp ?? 0;

        return $dateB <=> $dateA;
    }

    private function getLastWatchedDate($series)
    {
        return $series->seasons
            ->flatMap(fn($season) => $season->episodes)
            ->filter(fn($episode) => $episode->watchedEpisode)
            ->max(fn($episode) => $episode->watchedEpisode->watched_at)
            ?? 0;
    }

    public function search(Request $request)
    {
        $results = [];

        if ($request->filled('query')) {

            $response = Http::get(
                'https://api.tvmaze.com/search/shows',
                [
                    'q' => $request->input('query'),
                ]
            );

            $results = $response->json();

            foreach ($results as &$result) {

                $result['already_added'] = Series::where(
                    'tvmaze_id',
                    $result['show']['id']
                )->exists();
            }
        }

        return view('series.search', [
            'results' => $results,
        ]);
    }

    public function store(Request $request, TvMazeService $tvMaze)
    {
        $series = Series::firstOrCreate(
            [
                'tvmaze_id' => $request->tvmaze_id,
            ],
            [
                'name' => $request->name,
                'image_url' => $request->image_url,
            ]
        );

        $tvMaze->syncSeries($series);

        return redirect('/')->with('success', 'Série ajoutée !');
    }

    public function show(Series $series)
    {
        $series->load('seasons.episodes');

        return view('series.show', [
            'series' => $series,
        ]);
    }

    public function toggleWatched(Episode $episode)
    {
        $watchedEpisode = $episode->watchedEpisode;

        if ($watchedEpisode) {
            $watchedEpisode->delete();
        } else {
            $episode->watchedEpisode()->create([
                'watched_at' => now(),
            ]);
        }

        return back();
    }

    public function toggleSeasonWatched(Season $season)
    {
        $season->load('episodes.watchedEpisode');

        $episodes = $season->episodes;

        $allWatched = $episodes->isNotEmpty()
            && $episodes->every(fn($episode) => $episode->watchedEpisode);

        if ($allWatched) {
            foreach ($episodes as $episode) {
                if ($episode->watchedEpisode) {
                    $episode->watchedEpisode->delete();
                }
            }
        } else {
            foreach ($episodes as $episode) {
                if (!$episode->watchedEpisode) {
                    $episode->watchedEpisode()->create([
                        'watched_at' => now(),
                    ]);
                }
            }
        }

        return back();
    }

    public function toggleSeriesWatched(Series $series)
    {
        $series->load('seasons.episodes.watchedEpisode');

        $episodes = $series->seasons
            ->flatMap(fn($season) => $season->episodes);

        $allWatched = $episodes->isNotEmpty()
            && $episodes->every(fn($episode) => $episode->watchedEpisode);

        if ($allWatched) {
            foreach ($episodes as $episode) {
                if ($episode->watchedEpisode) {
                    $episode->watchedEpisode->delete();
                }
            }
        } else {
            foreach ($episodes as $episode) {
                if (!$episode->watchedEpisode) {
                    $episode->watchedEpisode()->create([
                        'watched_at' => now(),
                    ]);
                }
            }
        }

        return back();
    }

    private function getNextEpisode(Series $series)
    {
        $episodes = $series->seasons
            ->sortBy('season_number')
            ->flatMap(function ($season) {
                return $season->episodes->sortBy('episode_number');
            });

        return $episodes->first(function ($episode) {
            return !$episode->watchedEpisode;
        });
    }

    public function sync(TvMazeService $tvMaze)
    {
        $series = Series::all();

        $updates = [];

        foreach ($series as $serie) {
            $result = $tvMaze->syncSeries($serie);

            if (
                count($result['new_seasons']) > 0 ||
                count($result['new_episodes']) > 0
            ) {
                $updates[] = [
                    'series' => $serie,
                    'new_seasons' => $result['new_seasons'],
                    'new_episodes' => $result['new_episodes'],
                ];
            }
        }

        return back()->with('updates', $updates);
    }

    public function destroy(Series $series)
    {
        $series->delete();

        return redirect()
            ->route('series.index')
            ->with('success', 'Série supprimée avec succès.');
    }
}
