<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\TvMazeService;

class SeriesController extends Controller
{
    public function index()
    {
        $series = Series::with(['seasons.episodes.watchedEpisode'])
            ->get()
            ->sortBy('status_order')
            ->values();

        return view('series.index', compact('series'));
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
}
