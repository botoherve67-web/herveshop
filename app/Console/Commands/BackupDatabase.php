<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=7 : Nombre de sauvegardes à conserver}';

    protected $description = 'Crée une sauvegarde de la base de données';

    public function handle(): int
    {
        $driver = config('database.default');
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);
        $extension = $driver === 'sqlite' ? 'sqlite' : 'sql';
        $filename = 'database-'.now()->format('Y-m-d_H-i-s').'.'.$extension;
        $destination = $directory.DIRECTORY_SEPARATOR.$filename;

        if ($driver === 'sqlite') {
            $source = config('database.connections.sqlite.database');
            if (! is_string($source) || $source === ':memory:' || ! File::exists($source)) {
                throw new RuntimeException('Le fichier SQLite de production est introuvable.');
            }
            File::copy($source, $destination);
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            File::put($destination, $this->dumpMysql());
        } else {
            throw new RuntimeException('Moteur de base non supporté pour la sauvegarde : '.$driver);
        }

        $this->removeOldBackups($directory, max(1, (int) $this->option('keep')));
        $this->info('Sauvegarde créée : '.$destination);

        return self::SUCCESS;
    }

    protected function removeOldBackups(string $directory, int $keep): void
    {
        $backups = collect(File::files($directory))
            ->filter(fn ($file) => str_starts_with($file->getFilename(), 'database-') && in_array($file->getExtension(), ['sqlite', 'sql'], true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $backups->slice($keep)->each(fn ($file) => File::delete($file->getPathname()));
    }

    protected function dumpMysql(): string
    {
        $pdo = DB::connection()->getPdo();
        $lines = [
            '-- HerveShop database backup',
            '-- Generated at '.now()->toDateTimeString(),
            'SET FOREIGN_KEY_CHECKS=0;',
        ];

        foreach (DB::select('SHOW TABLES') as $tableRow) {
            $table = (string) array_values((array) $tableRow)[0];
            $quotedTable = '`'.str_replace('`', '``', $table).'`';
            $createRow = (array) DB::selectOne('SHOW CREATE TABLE '.$quotedTable);
            $createStatement = (string) end($createRow);
            $lines[] = 'DROP TABLE IF EXISTS '.$quotedTable.';';
            $lines[] = $createStatement.';';

            foreach (DB::table($table)->get() as $row) {
                $values = collect((array) $row)->map(function ($value) use ($pdo) {
                    return $value === null ? 'NULL' : $pdo->quote((string) $value);
                })->implode(', ');
                $lines[] = 'INSERT INTO '.$quotedTable.' VALUES ('.$values.');';
            }
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return implode(PHP_EOL, $lines).PHP_EOL;
    }
}