<?php

namespace App\Console\Commands;

use App\Models\Series;
use App\Services\TvMazeService;
use Illuminate\Console\Command;

class SyncSeries extends Command
{
    protected $signature = 'series:sync';

    protected $description = 'Met à jour les séries depuis TVmaze';

    public function handle(TvMazeService $tvMaze): int
    {
        $series = Series::all();

        if ($series->isEmpty()) {
            $this->info('Aucune série à synchroniser.');

            return self::SUCCESS;
        }

        foreach ($series as $serie) {

            $this->info("Synchronisation de : {$serie->name}");

            $tvMaze->syncSeries($serie);
        }

        $this->info('Synchronisation terminée !');

        return self::SUCCESS;
    }
}
