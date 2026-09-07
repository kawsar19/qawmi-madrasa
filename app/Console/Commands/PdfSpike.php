<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Pdf\PdfFactory;
use App\Support\Bn;
use App\Support\HijriDate;
use Illuminate\Console\Command;

/**
 * `php artisan pdf:spike` — বাংলা PDF রেন্ডারিং যাচাই করে।
 *
 * Generates a realistic marksheet so conjuncts, Bengali numerals and mixed
 * Bengali+Arabic lines can be checked by eye before the exam module is built.
 */
class PdfSpike extends Command
{
    protected $signature = 'pdf:spike {--out= : আউটপুট ফাইল পাথ}';

    protected $description = 'বাংলা PDF রেন্ডারিং যাচাই করতে নমুনা মার্কশিট তৈরি করে';

    public function handle(PdfFactory $factory): int
    {
        $marks = [
            ['name' => 'হেদায়া', 'name_ar' => 'الهداية', 'full' => 100, 'written' => 68, 'oral' => 17, 'grade' => 'জায়্যিদ জিদ্দান'],
            ['name' => 'মিশকাতুল মাসাবীহ', 'name_ar' => 'مشكاة المصابيح', 'full' => 100, 'written' => 72, 'oral' => 18, 'grade' => 'মুমতায'],
            ['name' => 'নূরুল আনওয়ার', 'name_ar' => 'نور الأنوار', 'full' => 100, 'written' => 55, 'oral' => 15, 'grade' => 'জায়্যিদ জিদ্দান'],
            ['name' => 'তাফসীরে জালালাইন', 'name_ar' => 'تفسير الجلالين', 'full' => 100, 'written' => 61, 'oral' => 16, 'grade' => 'জায়্যিদ জিদ্দান'],
            ['name' => 'শরহে বেকায়া', 'name_ar' => 'شرح الوقاية', 'full' => 100, 'written' => 48, 'oral' => 14, 'grade' => 'জায়্যিদ'],
        ];

        $total = array_sum(array_map(static fn (array $m): int => $m['written'] + $m['oral'], $marks));
        $fullTotal = array_sum(array_column($marks, 'full'));

        $pdf = $factory->renderView('pdf.spike', [
            'madrasa' => 'জামিয়া দারুল উলুম মুইনুল ইসলাম',
            'address' => 'হাটহাজারী, চট্টগ্রাম',
            'examName' => 'বার্ষিক পরীক্ষা',
            'session' => '১৪৪৬-১৪৪৭ হিজরি',
            'student' => 'মুহাম্মাদ আব্দুল্লাহ',
            'father' => 'মুহাম্মাদ ইব্রাহীম',
            'jamaat' => 'ফযীলত প্রথম বর্ষ',
            'roll' => Bn::num(27),
            'date' => Bn::num(now()->format('d/m/Y')),
            'hijri' => HijriDate::format(now()),
            'marks' => $marks,
            'total' => $total,
            'fullTotal' => $fullTotal,
            'percent' => $total / $fullTotal * 100,
            'grade' => 'জায়্যিদ জিদ্দান',
            'position' => 3,
        ]);

        $path = $this->option('out') ?? storage_path('app/pdf-spike.pdf');
        file_put_contents($path, $pdf);

        $this->info('✓ PDF তৈরি হয়েছে: '.$path);
        $this->line('  আকার: '.number_format(strlen($pdf) / 1024, 1).' KB');

        return self::SUCCESS;
    }
}
