<?php

declare(strict_types=1);

namespace App\Support;

/**
 * বাংলা সংখ্যা ও টাকার ফরম্যাটিং।
 *
 * Digits are converted in PHP rather than relying on font features, because
 * mPDF/Browsershot output must show Bengali numerals reliably.
 */
final class Bn
{
    /** @var list<string> */
    private const DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    /**
     * ইংরেজি সংখ্যা → বাংলা সংখ্যা. ("2024" → "২০২৪")
     */
    public static function num(int|float|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return strtr((string) $value, self::digitMap());
    }

    /**
     * বাংলা সংখ্যা → ইংরেজি সংখ্যা (ইনপুট পার্স করার জন্য)।
     */
    public static function toEnglish(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return strtr($value, array_flip(self::digitMap()));
    }

    /**
     * হাজার বিভাজক সহ বাংলা সংখ্যা। বাংলাদেশে লাখ/কোটি রীতি ব্যবহৃত হয়
     * (১২,৩৪,৫৬৭), পশ্চিমা ১,২৩৪,৫৬৭ নয়।
     */
    public static function money(int|float|string|null $amount, int $decimals = 2): string
    {
        $amount = (float) ($amount ?? 0);
        $negative = $amount < 0;
        $amount = abs($amount);

        $whole = (string) intval($amount);
        $fraction = $decimals > 0
            ? substr(number_format($amount - intval($amount), $decimals, '.', ''), 2)
            : '';

        $grouped = self::groupLakhCrore($whole);
        $result = self::num($grouped);

        if ($decimals > 0) {
            $result .= '.'.self::num($fraction);
        }

        return ($negative ? '-' : '').$result;
    }

    /**
     * টাকা চিহ্ন সহ। ("৳ ১,২৩,৪৫৬.০০")
     */
    public static function taka(int|float|string|null $amount, int $decimals = 2): string
    {
        return '৳ '.self::money($amount, $decimals);
    }

    /**
     * ভারতীয়/বাংলাদেশি রীতিতে গ্রুপিং: শেষ ৩ অঙ্ক, তারপর প্রতি ২ অঙ্ক।
     */
    private static function groupLakhCrore(string $number): string
    {
        if (strlen($number) <= 3) {
            return $number;
        }

        $last3 = substr($number, -3);
        $rest = substr($number, 0, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) ?? $rest;

        return $rest.','.$last3;
    }

    /**
     * strtr() accepts int-keyed arrays fine; PHP normalises the numeric string
     * keys to ints anyway, so the honest return type is array<int, string>.
     *
     * @return array<int, string>
     */
    private static function digitMap(): array
    {
        return self::DIGITS;
    }
}
