<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `php artisan db:show` — ডেটাবেস ব্রাউজ করার সহজ উপায়।
 *
 * Exists because tenant-scoped tables are awkward to inspect: every real query
 * needs a tenant_id filter, and remembering column names for 30+ tables costs
 * more time than it should. Read-only by design — it never writes.
 *
 *   php artisan db:show                       সব টেবিল ও সারি-সংখ্যা
 *   php artisan db:show students              টেবিলের ডেটা
 *   php artisan db:show students --columns    শুধু কলাম তালিকা
 *   php artisan db:show students --tenant=1   নির্দিষ্ট মাদরাসার সারি
 */
class DbShow extends Command
{
    protected $signature = 'db:show
        {table? : কোন টেবিল দেখতে চান}
        {--columns : ডেটার বদলে কলামের গঠন দেখায়}
        {--tenant= : শুধু এই tenant_id-এর সারি}
        {--limit=25 : সর্বোচ্চ কত সারি}
        {--where= : বাড়তি শর্ত, যেমন "status = \'active\'"}';

    protected $description = 'ডেটাবেসের টেবিল, কলাম ও ডেটা দেখায় (read-only)';

    public function handle(): int
    {
        $table = $this->argument('table');

        if (! is_string($table) || $table === '') {
            return $this->listTables();
        }

        if (! Schema::hasTable($table)) {
            $this->error("টেবিল পাওয়া যায়নি: {$table}");
            $this->line('  সব টেবিল দেখতে: php artisan db:show');

            return self::FAILURE;
        }

        return $this->option('columns')
            ? $this->showColumns($table)
            : $this->showRows($table);
    }

    /** সব টেবিল ও প্রতিটিতে কত সারি আছে। */
    private function listTables(): int
    {
        $rows = [];

        foreach (Schema::getTableListing(schema: null, schemaQualified: false) as $name) {
            if (str_starts_with($name, 'sqlite_')) {
                continue;
            }

            $rows[] = [
                $name,
                number_format(DB::table($name)->count()),
                Schema::hasColumn($name, 'tenant_id') ? '✓' : '',
            ];
        }

        $this->table(['টেবিল', 'সারি', 'tenant-scoped'], $rows);
        $this->newLine();
        $this->line('  ডেটা দেখতে:  <fg=cyan>php artisan db:show <টেবিল></>');
        $this->line('  কলাম দেখতে:  <fg=cyan>php artisan db:show <টেবিল> --columns</>');

        return self::SUCCESS;
    }

    /** টেবিলের কলাম, ধরন ও nullable কিনা। */
    private function showColumns(string $table): int
    {
        $rows = array_map(fn (array $c): array => [
            $c['name'],
            $c['type'],
            $c['nullable'] ? 'হ্যাঁ' : '',
            is_scalar($c['default']) ? (string) $c['default'] : '',
        ], Schema::getColumns($table));

        $this->info("টেবিল: {$table}  —  ".count($rows).'টি কলাম');
        $this->table(['কলাম', 'ধরন', 'nullable', 'default'], $rows);

        return self::SUCCESS;
    }

    /**
     * সারিগুলো দেখায়। DB::table() ব্যবহার করা হয়েছে বলে global scope চলে না,
     * তাই --tenant হাতে প্রয়োগ করতে হয়।
     */
    private function showRows(string $table): int
    {
        $query = DB::table($table);

        $tenant = $this->option('tenant');

        if (is_string($tenant) && $tenant !== '') {
            if (! Schema::hasColumn($table, 'tenant_id')) {
                $this->warn("{$table} টেবিলে tenant_id নেই — --tenant উপেক্ষা করা হলো।");
            } else {
                $query->where('tenant_id', (int) $tenant);
            }
        }

        $where = $this->option('where');

        if (is_string($where) && $where !== '') {
            $query->whereRaw($where);
        }

        $total = $query->count();
        $limit = max(1, (int) $this->option('limit'));
        $rows = $query->limit($limit)->get();

        if ($rows->isEmpty()) {
            $this->warn("{$table} — কোনো সারি নেই।");

            return self::SUCCESS;
        }

        // Long text and null read badly in a terminal table, so trim and mark them.
        $records = $rows->map(fn ($row): array => array_map(
            fn ($value): string => match (true) {
                $value === null => '—',
                is_string($value) && mb_strlen($value) > 32 => mb_substr($value, 0, 29).'…',
                default => (string) $value,
            },
            (array) $row,
        ))->all();

        $this->info("টেবিল: {$table}  —  মোট {$total}টি সারি".
            ($total > $limit ? " (প্রথম {$limit}টি দেখানো হলো)" : ''));

        $this->table(array_keys($records[0]), $records);

        return self::SUCCESS;
    }
}
