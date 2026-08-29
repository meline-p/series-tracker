<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ImportDatabase extends Command
{
    protected $signature = 'db:import';

    protected $description = 'Importe la sauvegarde de la base de données';

    public function handle()
    {
        $database = config('database.connections.mysql');

        $backupPath = database_path('backups/series_tracker.sql');
        $safetyPath = database_path('backups/before_import.sql');

        $mysql = '/Applications/MAMP/Library/bin/mysql';

        if (!file_exists($backupPath)) {
            $this->error('❌ Aucun fichier de sauvegarde trouvé.');

            return self::FAILURE;
        }

        $tables = [
            'series',
            'seasons',
            'episodes',
            'watched_episodes',
        ];

        /*
         * 1. Sauvegarde de la BDD actuelle
         */
        $this->info('🛡️ Sauvegarde de la BDD actuelle...');

        $backupCommand = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s %s > %s',
            escapeshellarg('/Applications/MAMP/Library/bin/mysqldump'),
            escapeshellarg($database['username']),
            escapeshellarg($database['password']),
            escapeshellarg($database['host']),
            escapeshellarg($database['port']),
            escapeshellarg($database['database']),
            implode(' ', array_map('escapeshellarg', $tables)),
            escapeshellarg($safetyPath)
        );

        $backupResult = Process::run($backupCommand);

        if ($backupResult->failed()) {
            if (file_exists($safetyPath)) {
                unlink($safetyPath);
            }

            $this->error('❌ Impossible de sauvegarder la BDD actuelle.');
            $this->error($backupResult->errorOutput());

            return self::FAILURE;
        }

        /*
         * 2. Import
         */
        $this->info('📥 Import de la sauvegarde...');

        $importCommand = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s < %s',
            escapeshellarg($mysql),
            escapeshellarg($database['username']),
            escapeshellarg($database['password']),
            escapeshellarg($database['host']),
            escapeshellarg($database['port']),
            escapeshellarg($database['database']),
            escapeshellarg($backupPath)
        );

        $importResult = Process::run($importCommand);

        if ($importResult->failed()) {
            $this->error('❌ L’import a échoué.');
            $this->error($importResult->errorOutput());

            $this->warn('⚠️ La sauvegarde de sécurité est disponible dans :');
            $this->warn('database/backups/before_import.sql');

            return self::FAILURE;
        }

        $this->info('✅ Base de données restaurée avec succès !');

        return self::SUCCESS;
    }
}
