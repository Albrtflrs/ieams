<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--output=} {--filename=}';
    protected $description = 'Create a SQL backup of the database';

    public function handle()
    {
        $backupPath = Setting::get('backup_path', storage_path('app/backups'));

        // Ensure the directory exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = $this->option('filename') ?? 'ieams_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Backup failed. Check mysqldump availability and credentials.');
            return 1;
        }

        $this->info("Backup saved to: {$filepath}");
        return 0;
    }
}