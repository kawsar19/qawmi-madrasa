<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * কেস নির্বিশেষে টেক্সট খোঁজা — ড্রাইভার যেটাই হোক।
 *
 * SQLite-এর LIKE ASCII-তে case-insensitive, Postgres-এর নয়। একই কোড দুই
 * জায়গায় চালাতে হলে তাই ড্রাইভার দেখে অপারেটর বাছতে হয়: Postgres-এ ILIKE,
 * বাকিদের জন্য LIKE। নাহলে "abdul" লিখে "Abdul" পাওয়া যায় না — আর সেটা
 * প্রোডাকশনে গিয়ে ধরা পড়ে, কারণ টেস্ট চলে SQLite-এ।
 *
 * বাংলা নামে case নেই, তাই সেখানে দুটোই সমান — ভাঙে ইংরেজি নাম,
 * student_uid, application_no এসব ক্ষেত্রে।
 */
class Search
{
    /**
     * খোঁজার শব্দটি `%...%` দিয়ে মুড়ে দেয়, LIKE-এর ওয়াইল্ডকার্ড escape করে।
     *
     * escape না করলে ব্যবহারকারীর লেখা `%` বা `_` ওয়াইল্ডকার্ড হয়ে যায় —
     * `_` মানে "যেকোনো এক অক্ষর", তাই `100_` সার্চে `1000`ও চলে আসে।
     */
    public static function term(string $search): string
    {
        return '%'.addcslashes($search, '%_\\').'%';
    }

    /**
     * কলামটিতে case নির্বিশেষে খোঁজে।
     */
    public static function where(Builder $query, string $column, string $term): Builder
    {
        return $query->where($column, self::operator(), $term);
    }

    /**
     * আগের শর্তের সাথে OR দিয়ে যোগ করে।
     */
    public static function orWhere(Builder $query, string $column, string $term): Builder
    {
        return $query->orWhere($column, self::operator(), $term);
    }

    /**
     * একাধিক কলামের যেকোনোটিতে মিললেই হবে — OR দিয়ে জোড়া।
     *
     * @param  list<string>  $columns
     */
    public static function anyOf(Builder $query, array $columns, string $term): Builder
    {
        return $query->where(static function (Builder $inner) use ($columns, $term): void {
            foreach ($columns as $index => $column) {
                $index === 0
                    ? self::where($inner, $column, $term)
                    : self::orWhere($inner, $column, $term);
            }
        });
    }

    /**
     * চলতি ড্রাইভারের জন্য সঠিক অপারেটর।
     */
    private static function operator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
