<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Models\Central\Plan;
use Illuminate\Contracts\View\View;

/**
 * SaaS মার্কেটিং ল্যান্ডিং পেজ (central domain-এর "/")।
 *
 * প্রাইসিং DB থেকেই আসে, তাই সুপার অ্যাডমিন /admin/plans-এ দাম বদলালে
 * ল্যান্ডিং পেজও সাথে সাথে বদলায় — কোথাও দাম হার্ডকোড করা নেই।
 */
class LandingController
{
    /**
     * plans.features-এ থাকা স্লাগগুলোর বাংলা নাম। স্লাগগুলো
     * PermissionRegistry-র মডিউল কী-র সাথে এক, তাই নতুন মডিউল যোগ
     * করলে এখানেও একটি লাইন যোগ করতে হবে।
     */
    private const FEATURE_LABELS = [
        'academic' => 'শিক্ষা কার্যক্রম',
        'people' => 'ছাত্র ও শিক্ষক',
        'attendance' => 'হাজিরা',
        'exam' => 'পরীক্ষা ও ফলাফল',
        'finance' => 'ফি ও হিসাব',
        'hifz' => 'হিফজ ট্র্যাকিং',
        'cms' => 'পাবলিক ওয়েবসাইট',
        'boarding' => 'বোর্ডিং ও খানা',
        'donation' => 'দান ও যাকাত',
    ];

    public function __invoke(): View
    {
        return view('central.landing', [
            'plans' => Plan::where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'featureLabels' => self::FEATURE_LABELS,
        ]);
    }
}
