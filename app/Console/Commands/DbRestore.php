<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * `php artisan db:restore` — db:backup-এর কোনো একটি কপি ফিরিয়ে আনে।
 *
 * Takes a safety copy of the *current* file first, so restoring the wrong
 * backup is itself undoable.
 */
class DbRestore extends Command
{
    protected $signature = 'db:restore {file? : ব্যাকআপ ফাইলের নাম}';

    protected $description = 'ব্যাকআপ থেকে ডেটাবেস ফিরিয়ে আনে';

    public function handle(): int
    {
        if (config('database.default') !== 'sqlite') {
            $this->error('db:restore শুধু sqlite-এর জন্য।');

            return self::FAILURE;
        }

        $directory = storage_path('backups');

        /** @var list<string> $backups */
        $backups = collect(File::glob($directory.'/database-*.sqlite'))
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

        /** @var string $target */
        $target = config('database.connections.sqlite.database');

        // বর্তমান ফাইলটাও রেখে দেওয়া — ভুল ব্যাকআপ ফেরালে যেন ফেরত যাওয়া যায়।
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
