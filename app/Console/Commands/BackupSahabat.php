<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupSahabat extends Command
{
    protected $signature = 'sahabat:backup';

    protected $description = 'Backs up the SQLite database and local storage';

    public function handle(): int
    {
        $timestamp = now()->format('Ymd_His');
        $backupDirectory = storage_path('app/backups');
        File::ensureDirectoryExists($backupDirectory);
        $database = database_path('database.sqlite');
        if (File::exists($database)) {
            File::copy($database, $backupDirectory.'/database_'.$timestamp.'.sqlite');
        }

        $archivePath = $backupDirectory.'/storage_'.$timestamp.'.zip';
        $archive = new ZipArchive;
        if ($archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $archive->addFromString('backup-manifest.txt', 'SAHABAT SEKOLAH BACKUP\nCreated: '.now()->toIso8601String().'\n');
            $storagePath = storage_path('app');
            foreach (File::allFiles($storagePath) as $file) {
                if (str_starts_with($file->getPathname(), $backupDirectory)) {
                    continue;
                }
                $archive->addFile($file->getPathname(), 'app/'.$file->getRelativePathname());
            }
            $archive->close();
        }

        $this->info('Backup selesai: '.$backupDirectory);

        return self::SUCCESS;
    }
}
