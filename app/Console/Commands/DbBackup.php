<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * `php artisan db:backup` — ডেটাবেসের একটি টাইমস্ট্যাম্প করা কপি রাখে।
 *
 * Exists because `migrate:fresh` drops every table with no undo and the dev
 * SQLite file is gitignored, so a mistaken rebuild is unrecoverable. Cheap
 * insurance: one file copy.
 */
class DbBackup extends Command
{
    protected $signature = 'db:backup {--keep=20 : সর্বোচ্চ কতগুলো ব্যাকআপ রাখা হবে}';

    protected $description = 'ডেটাবেসের ব্যাকআপ কপি তৈরি করে';

    public function handle(): int
    {
        $database = config('database.default');

        if ($database !== 'sqlite') {
            $this->error("db:backup শুধু sqlite-এর জন্য। বর্তমান কানেকশন: {$database}");

            return self::FAILURE;
        }

        /** @var string $source */
        $source = config('database.connections.sqlite.database');

        if (! File::exists($source)) {
            $this->error("ডেটাবেস ফাইল পাওয়া যায়নি: {$source}");

            return self::FAILURE;
        }

        $directory = storage_path('backups');
        File::ensureDirectoryExists($directory);

        $target = $directory.'/database-'.now()->format('Y-m-d_His').'.sqlite';

        File::copy($source, $target);

        $this->info('✓ ব্যাকআপ: '.$target);
        $this->line('  আকার: '.number_format(File::size($target) / 1024, 1).' KB');

        $this->prune($directory, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    /**
     * পুরনো ব্যাকআপ মুছে ফেলা — নইলে ডিস্ক ভরে যাবে।
     */
    private function prune(string $directory, int $keep): void
    {
        if ($keep < 1) {
            return;
        }

        $backups = collect(File::glob($directory.'/database-*.sqlite'))
            ->sortDesc()
            ->values();

        $backups->slice($keep)->each(static fn (string $path) => File::delete($path));

        if ($backups->count() > $keep) {
            $this->line('  পুরনো '.($backups->count() - $keep).'টি ব্যাকআপ মুছে ফেলা হয়েছে।');
        }
    }
}
