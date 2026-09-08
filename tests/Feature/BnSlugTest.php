<?php

declare(strict_types=1);

use App\Support\BnSlug;
use Illuminate\Support\Str;

/**
 * Laravel's Str::slug() romanises Bengali badly (য় becomes "z"), so madrasa
 * names would produce slugs nobody would choose by hand.
 */
it('romanises bengali madrasa names readably', function (string $input, string $expected) {
    expect(BnSlug::make($input))->toBe($expected);
})->with([
    ['দারুল উলুম', 'darul-ulum'],
    ['মুইনুল ইসলাম', 'muinul-islam'],
    ['রাহমানিয়া', 'rahmaniya'],
    ['মাদরাসা', 'madrasa'],
    ['নূরানী', 'nurani'],
    ['আহলিয়া', 'ahliya'],
]);

it('never renders য় as the letter z', function () {
    // The specific defect in Str::slug() this helper exists to fix.
    expect(Str::slug('জামিয়া'))->toContain('z')
        ->and(BnSlug::make('জামিয়া'))->not->toContain('z')
        ->and(BnSlug::make('জামিয়া'))->toBe('jamiya');
});

it('converts bengali digits', function () {
    expect(BnSlug::make('২ নম্বর'))->toStartWith('2');
});

it('passes latin text through unchanged', function () {
    expect(BnSlug::make('Al Jamia Islamia'))->toBe('al-jamia-islamia');
});

it('produces a url-safe slug', function (string $input) {
    expect(BnSlug::make($input))->toMatch('/^[a-z0-9-]*$/');
})->with([
    'জামিয়া ইসলামিয়া পটিয়া',
    'আল-জামিয়াতুল আহলিয়া',
    'বাইতুশ শরফ ক্ষ ঞ্চ ষ্ট্র',
    'মাদরাসা — ২য় শাখা',
]);
