<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * বাংলা নাম থেকে পড়ার উপযোগী ইংরেজি slug।
 *
 * Laravel's Str::slug() romanises Bengali poorly — it maps য় to "z" and drops
 * inherent vowels, so "জামিয়া ইসলামিয়া পটিয়া" becomes "jamiza-islamiza-ptiza".
 * A madrasa would have to retype every slug and domain by hand.
 *
 * This transliterates the common Bengali letters the way Bangladeshi
 * institutions actually spell their names in English, then falls back to
 * Str::slug() for anything left (Latin text, digits, punctuation).
 */
final class BnSlug
{
    /**
     * Longest keys first — multi-character sequences (conjuncts, vowel signs
     * attached to a consonant) must match before their parts.
     *
     * @var array<string, string>
     */
    private const MAP = [
        // যুক্তাক্ষর ও বিশেষ সমন্বয়
        'ক্ষ' => 'kh', 'জ্ঞ' => 'gg', 'ঞ্চ' => 'nch', 'ঞ্জ' => 'nj',
        'ষ্ট' => 'sht', 'ষ্ঠ' => 'shth', 'স্ট' => 'st', 'স্থ' => 'sth',
        'ন্ত' => 'nt', 'ন্দ' => 'nd', 'ম্ব' => 'mb', 'ঙ্গ' => 'ng',
        'দ্ধ' => 'ddh', 'দ্দ' => 'dd', 'ত্ত' => 'tt', 'ল্ল' => 'll',
        'ক্ত' => 'kt', 'ত্র' => 'tr', 'প্র' => 'pr', 'ব্র' => 'br',
        'ক্র' => 'kr', 'গ্র' => 'gr', 'শ্র' => 'shr', 'স্র' => 'sr',

        // স্বরবর্ণ
        'অ' => 'o', 'আ' => 'a', 'ই' => 'i', 'ঈ' => 'i', 'উ' => 'u', 'ঊ' => 'u',
        'ঋ' => 'ri', 'এ' => 'e', 'ঐ' => 'oi', 'ও' => 'o', 'ঔ' => 'ou',

        // ব্যঞ্জনবর্ণ
        'ক' => 'k', 'খ' => 'kh', 'গ' => 'g', 'ঘ' => 'gh', 'ঙ' => 'ng',
        'চ' => 'ch', 'ছ' => 'chh', 'জ' => 'j', 'ঝ' => 'jh', 'ঞ' => 'n',
        'ট' => 't', 'ঠ' => 'th', 'ড' => 'd', 'ঢ' => 'dh', 'ণ' => 'n',
        'ত' => 't', 'থ' => 'th', 'দ' => 'd', 'ধ' => 'dh', 'ন' => 'n',
        'প' => 'p', 'ফ' => 'ph', 'ব' => 'b', 'ভ' => 'bh', 'ম' => 'm',
        'য' => 'j', 'র' => 'r', 'ল' => 'l',
        'শ' => 'sh', 'ষ' => 'sh', 'স' => 's', 'হ' => 'h',
        'ড়' => 'r', 'ঢ়' => 'rh', 'য়' => 'y', 'ৎ' => 't',

        // কার (vowel signs)
        'া' => 'a', 'ি' => 'i', 'ী' => 'i', 'ু' => 'u', 'ূ' => 'u',
        'ৃ' => 'ri', 'ে' => 'e', 'ৈ' => 'oi', 'ো' => 'o', 'ৌ' => 'ou',

        // চিহ্ন
        'ং' => 'ng', 'ঃ' => '', 'ঁ' => '', '্' => '',

        // সংখ্যা
        '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
        '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
    ];

    public static function make(string $value): string
    {
        return Str::slug(strtr($value, self::MAP));
    }
}
