<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * `php artisan db:backup` — ডেটাবেসের একটি টাইমস্ট্যাম্প করা কপি রাখে।
 *
 * Exists because `migrate:fresh` drops every table with no undo and the dev
 * database is gitignored, so a mistaken rebuild is unrecoverable.
 *
 * SQLite-এ এটা একটা ফাইল কপি; Postgres-এ `pg_dump`. দুই ক্ষেত্রেই ফাইল
 * যায় storage/backups/-এ, আর `db:restore` সেখান থেকেই ফেরায়।
 */
class DbBackup extends Command
{
    protected $signature = 'db:backup {--keep=20 : সর্বোচ্চ কতগুলো ব্যাকআপ রাখা হবে}';

    protected $description = 'ডেটাবেসের ব্যাকআপ কপি তৈরি করে';

    public function handle(): int
    {
        $connection = config('database.default');

        $directory = storage_path('backups');
        File::ensureDirectoryExists($directory);

        $stamp = now()->format('Y-m-d_His');

        $result = match ($connection) {
            'sqlite' => $this->backupSqlite($directory, $stamp),
            'pgsql' => $this->backupPostgres($directory, $stamp),
            default => null,
        };

        if ($result === null) {
            $this->error("db:backup শুধু sqlite ও pgsql-এর জন্য। বর্তমান কানেকশন: {$connection}");

            return self::FAILURE;
        }

        if ($result === false) {
            return self::FAILURE;
        }

        $this->info('✓ ব্যাকআপ: '.$result);
        $this->line('  আকার: '.number_format(File::size($result) / 1024, 1).' KB');

        $this->prune($directory, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    /**
     * SQLite — ফাইলটাই কপি।
     *
     * @return string|false সফল হলে ব্যাকআপের পথ
     */
    private function backupSqlite(string $directory, string $stamp): string|false
    {
        /** @var string $source */
        $source = config('database.connections.sqlite.database');

        if (! File::exists($source)) {
            $this->error("ডেটাবেস ফাইল পাওয়া যায়নি: {$source}");

            return false;
        }

        $target = $directory.'/database-'.$stamp.'.sqlite';

        File::copy($source, $target);

        return $target;
    }

    /**
     * Postgres — pg_dump-এর custom format (-Fc), কারণ সেটা pg_restore-এ
     * বেছে বেছে ফেরানো যায় এবং নিজেই compressed.
     *
     * @return string|false সফল হলে ব্যাকআপের পথ
     */
    private function backupPostgres(string $directory, string $stamp): string|false
    {
        $target = $directory.'/database-'.$stamp.'.dump';

        /** @var array{host?: string, port?: string|int, database?: string, username?: string, password?: string} $config */
        $config = config('database.connections.pgsql');

        $process = new Process([
            'pg_dump',
            '--format=custom',
            '--no-owner',
            '--no-privileges',
            '--file='.$target,
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? '5432'),
            '--username='.($config['username'] ?? ''),
            $config['database'] ?? '',
        ], env: ['PGPASSWORD' => (string) ($config['password'] ?? '')] + $_ENV);

        // বড় ডেটাবেসে ডাম্প সময় নিতে পারে; ডিফল্ট ৬০ সেকেন্ড যথেষ্ট নয়।
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('pg_dump ব্যর্থ হয়েছে:');
            $this->line('  '.trim($process->getErrorOutput()));

            // অসম্পূর্ণ ফাইল রেখে দিলে পরে সেটাই "ব্যাকআপ" ভেবে ফেরানো হতে পারে।
            File::delete($target);

            return false;
        }

        return $target;
    }

    /**
     * পুরনো ব্যাকআপ মুছে ফেলা — নইলে ডিস্ক ভরে যাবে।
     */
    private function prune(string $directory, int $keep): void
    {
        if ($keep < 1) {
            return;
        }

        $backups = collect(File::glob($directory.'/database-*.{sqlite,dump}', GLOB_BRACE))
            ->sortDesc()
            ->values();

        $backups->slice($keep)->each(static fn (string $path) => File::delete($path));

        if ($backups->count() > $keep) {
            $this->line('  পুরনো '.($backups->count() - $keep).'টি ব্যাকআপ মুছে ফেলা হয়েছে।');
        }
    }
}
