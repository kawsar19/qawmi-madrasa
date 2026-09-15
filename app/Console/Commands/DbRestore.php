<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * `php artisan db:restore` — db:backup-এর কোনো একটি কপি ফিরিয়ে আনে।
 *
 * Takes a safety copy of the *current* database first, so restoring the
 * wrong backup is itself undoable.
 *
 * SQLite-এ ফাইল কপি; Postgres-এ `pg_restore --clean`, যা আগে পুরনো
 * অবজেক্ট ফেলে দিয়ে তবে ফেরায়।
 */
class DbRestore extends Command
{
    protected $signature = 'db:restore {file? : ব্যাকআপ ফাইলের নাম}';

    protected $description = 'ব্যাকআপ থেকে ডেটাবেস ফিরিয়ে আনে';

    public function handle(): int
    {
        $connection = config('database.default');

        if (! in_array($connection, ['sqlite', 'pgsql'], true)) {
            $this->error("db:restore শুধু sqlite ও pgsql-এর জন্য। বর্তমান কানেকশন: {$connection}");

            return self::FAILURE;
        }

        $directory = storage_path('backups');

        // একই ফোল্ডারে দুই ফরম্যাট থাকতে পারে (ড্রাইভার বদলালে), তাই চলতি
        // ড্রাইভারের এক্সটেনশনটুকুই দেখানো হয় — অন্যটা ফেরানো যাবে না।
        $extension = $connection === 'sqlite' ? 'sqlite' : 'dump';

        /** @var list<string> $backups */
        $backups = collect(File::glob($directory.'/database-*.'.$extension))
            ->sortDesc()
            ->values()
            ->all();

        if ($backups === []) {
            $this->error('কোনো ব্যাকআপ নেই। `php artisan db:backup` চালান।');

            return self::FAILURE;
        }

        $choice = $this->resolveChoice($backups);

        if ($choice === null) {
            return self::FAILURE;
        }

        return $connection === 'sqlite'
            ? $this->restoreSqlite($choice, $directory)
            : $this->restorePostgres($choice);
    }

    /**
     * SQLite — বর্তমান ফাইল সরিয়ে রেখে ব্যাকআপটা বসানো।
     */
    private function restoreSqlite(string $choice, string $directory): int
    {
        /** @var string $target */
        $target = config('database.connections.sqlite.database');

        // বর্তমান ডেটাবেসও রেখে দেওয়া — ভুল ব্যাকআপ ফেরালে যেন ফেরত যাওয়া যায়।
        if (File::exists($target)) {
            $safety = $directory.'/pre-restore-'.now()->format('Y-m-d_His').'.sqlite';
            File::copy($target, $safety);
            $this->line('  বর্তমান ডেটাবেস রাখা হলো: '.basename($safety));
        }

        File::copy($choice, $target);

        $this->info('✓ ফিরিয়ে আনা হয়েছে: '.basename($choice));

        return self::SUCCESS;
    }

    /**
     * Postgres — আগে চলতি অবস্থার একটা ডাম্প, তারপর pg_restore --clean।
     */
    private function restorePostgres(string $choice): int
    {
        // ফেরানোর আগে চলতি ডেটাবেসের ডাম্প — sqlite-এর safety copy-র সমতুল্য।
        $this->line('  বর্তমান ডেটাবেসের ব্যাকআপ নেওয়া হচ্ছে…');

        if ($this->call('db:backup', ['--keep' => 0]) !== self::SUCCESS) {
            $this->error('চলতি অবস্থার ব্যাকআপ নেওয়া যায়নি — ফেরানো বাতিল।');

            return self::FAILURE;
        }

        /** @var array{host?: string, port?: string|int, database?: string, username?: string, password?: string} $config */
        $config = config('database.connections.pgsql');

        $process = new Process([
            'pg_restore',
            // --clean --if-exists: পুরনো টেবিল আগে ফেলে দেয়, নইলে
            // "already exists" ত্রুটিতে ফেরানো অসম্পূর্ণ থেকে যায়।
            '--clean',
            '--if-exists',
            '--no-owner',
            '--no-privileges',
            '--dbname='.($config['database'] ?? ''),
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? '5432'),
            '--username='.($config['username'] ?? ''),
            $choice,
        ], env: ['PGPASSWORD' => (string) ($config['password'] ?? '')] + $_ENV);

        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('pg_restore ব্যর্থ হয়েছে:');
            $this->line('  '.trim($process->getErrorOutput()));

            return self::FAILURE;
        }

        $this->info('✓ ফিরিয়ে আনা হয়েছে: '.basename($choice));

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $backups
     */
    private function resolveChoice(array $backups): ?string
    {
        $named = $this->argument('file');

        if (is_string($named) && $named !== '') {
            foreach ($backups as $backup) {
                if (basename($backup) === $named || $backup === $named) {
                    return $backup;
                }
            }

            $this->error("ব্যাকআপ পাওয়া যায়নি: {$named}");

            return null;
        }

        $options = [];

        foreach ($backups as $backup) {
            $size = number_format(File::size($backup) / 1024, 1);
            $options[basename($backup)] = basename($backup)."  ({$size} KB)";
        }

        $selected = $this->choice('কোন ব্যাকআপ ফেরাবেন?', $options, array_key_first($options));

        foreach ($backups as $backup) {
            if (basename($backup) === $selected) {
                return $backup;
            }
        }

        return null;
    }
}
