<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ExportDatabase extends Command
{
    protected $signature = 'db:export';

    protected $description = 'Exporte les tables utiles de la base de données';

    public function handle()
    {
        $database = config('database.connections.mysql');

        $backupPath = database_path('backups/series_tracker.sql');
        $temporaryPath = database_path('backups/series_tracker.sql.tmp');

        $mysqldump = '/Applications/MAMP/Library/bin/mysqldump';

        $tables = [
            'series',
            'seasons',
            'episodes',
            'watched_episodes',
        ];

        $command = sprintf(
            '%s --add-drop-table --user=%s --password=%s --host=%s --port=%s %s %s > %s',
            escapeshellarg($mysqldump),
            escapeshellarg($database['username']),
            escapeshellarg($database['password']),
            escapeshellarg($database['host']),
            escapeshellarg($database['port']),
            escapeshellarg($database['database']),
            implode(' ', array_map('escapeshellarg', $tables)),
            escapeshellarg($temporaryPath)
        );

        $result = Process::run($command);

        if ($result->failed()) {
            if (file_exists($temporaryPath)) {
                unlink($temporaryPath);
            }

            $this->error('❌ Impossible d’exporter la base de données.');
            $this->error($result->errorOutput());

            return self::FAILURE;
        }

        if (file_exists($backupPath)) {
            unlink($backupPath);
        }

        rename($temporaryPath, $backupPath);

        $this->info('✅ Base de données exportée avec succès !');
        $this->info('📁 database/backups/series_tracker.sql');

        return self::SUCCESS;
    }
}
