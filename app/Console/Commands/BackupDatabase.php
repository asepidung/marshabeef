<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'app:backup-db
        {--dir= : Folder tujuan backup (default: storage/app/backups)}
        {--keep=30 : Jumlah backup terbaru yang disimpan}';

    protected $description = 'Buat salinan konsisten database SQLite dan hapus backup lama';

    public function handle(): int
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite') {
            $this->error('Perintah ini hanya untuk SQLite. Untuk database lain gunakan alat backup bawaannya.');

            return self::FAILURE;
        }

        $dir = rtrim((string) ($this->option('dir') ?: storage_path('app/backups')), '\\/');
        File::ensureDirectoryExists($dir);

        $file = $dir.DIRECTORY_SEPARATOR.'db-'.now()->format('Ymd-His').'.sqlite';

        // VACUUM INTO menghasilkan salinan utuh walau database sedang dipakai (mode WAL).
        $connection->statement('VACUUM INTO ?', [$file]);

        $this->info("Backup dibuat: {$file}");

        $old = collect(File::glob($dir.DIRECTORY_SEPARATOR.'db-*.sqlite'))->sort()->values();
        $excess = $old->count() - max(1, (int) $this->option('keep'));

        if ($excess > 0) {
            $old->take($excess)->each(fn (string $path) => File::delete($path));
            $this->line("Backup lama dihapus: {$excess}");
        }

        return self::SUCCESS;
    }
}
