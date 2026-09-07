<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;
use IntlDateFormatter;

/**
 * হিজরি তারিখ — শুধুমাত্র প্রদর্শনের জন্য।
 *
 * IMPORTANT RULES (see plan, risk #3):
 *  - All dates are STORED as Gregorian `date` columns. Hijri is render-time only.
 *  - Never do date arithmetic in Hijri.
 *  - Bangladesh follows moon sighting, so Umm al-Qura can be off by ±1 day;
 *    a per-tenant offset (-2..+2) corrects it.
 *  - Month names are hand-written Bengali — ICU's Bengali transliteration of
 *    Islamic month names is wrong.
 */
final class HijriDate
{
    /** @var array<int, string> */
    public const MONTHS_BN = [
        1 => 'মুহাররম',
        2 => 'সফর',
        3 => 'রবিউল আউয়াল',
        4 => 'রবিউস সানি',
        5 => 'জুমাদাল ঊলা',
        6 => 'জুমাদাস সানি',
        7 => 'রজব',
        8 => 'শাবান',
        9 => 'রমজান',
        10 => 'শাওয়াল',
        11 => 'জিলকদ',
        12 => 'জিলহজ',
    ];

    /**
     * @return array{year:int, month:int, day:int}
     */
    public static function fromGregorian(DateTimeInterface $date, int $offsetDays = 0): array
    {
        $date = (clone Carbon::instance($date))->addDays($offsetDays);

        $formatter = new IntlDateFormatter(
            'en_US@calendar=islamic-umalqura',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            'Asia/Dhaka',
            IntlDateFormatter::TRADITIONAL,
            'yyyy-MM-dd'
        );

        $formatted = $formatter->format($date->getTimestamp());

        if ($formatted === false) {
            return ['year' => 0, 'month' => 0, 'day' => 0];
        }

        [$year, $month, $day] = array_map(intval(...), explode('-', $formatted));

        return ['year' => $year, 'month' => $month, 'day' => $day];
    }

    /**
     * পূর্ণ বাংলা হিজরি তারিখ। ("১৫ রমজান ১৪৪৬ হিজরি")
     */
    public static function format(DateTimeInterface $date, int $offsetDays = 0, bool $withSuffix = true): string
    {
        $h = self::fromGregorian($date, $offsetDays);

        if ($h['month'] === 0) {
            return '';
        }

        return Bn::num($h['day'])
            .' '.(self::MONTHS_BN[$h['month']] ?? '')
            .' '.Bn::num($h['year'])
            .($withSuffix ? ' হিজরি' : '');
    }

    /**
     * হিজরি সন। ("১৪৪৬")
     */
    public static function year(DateTimeInterface $date, int $offsetDays = 0): int
    {
        return self::fromGregorian($date, $offsetDays)['year'];
    }

    public static function monthName(int $month): string
    {
        return self::MONTHS_BN[$month] ?? '';
    }
}
