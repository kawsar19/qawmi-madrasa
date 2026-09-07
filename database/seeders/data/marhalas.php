<?php

declare(strict_types=1);

/**
 * বেফাকুল মাদারিসিল আরাবিয়া বাংলাদেশ-এর মারহালা কাঠামো।
 *
 * Master reference data, git-versioned. Copied INTO each tenant on
 * provisioning so every madrasa can edit its own copy freely.
 *
 * track: kitab | hifz | nazera | qirat | ifta
 */
return [
    // ---- কিতাব বিভাগ (৬ স্তর) ----
    ['code' => 'ibtedaiyyah', 'name' => 'ইবতেদাইয়্যাহ', 'name_ar' => 'الابتدائية', 'track' => 'kitab', 'sort_order' => 10, 'duration_years' => 5],
    ['code' => 'mutawassitah', 'name' => 'মুতাওয়াসসিতাহ', 'name_ar' => 'المتوسطة', 'track' => 'kitab', 'sort_order' => 20, 'duration_years' => 3],
    ['code' => 'sanabiyyah_ama', 'name' => 'সানাবিয়্যাহ আম্মাহ', 'name_ar' => 'الثانوية العامة', 'track' => 'kitab', 'sort_order' => 30, 'duration_years' => 2],
    ['code' => 'sanabiyyah_ulya', 'name' => 'সানাবিয়্যাহ উলইয়া', 'name_ar' => 'الثانوية العليا', 'track' => 'kitab', 'sort_order' => 40, 'duration_years' => 2],
    ['code' => 'fazilat', 'name' => 'ফযীলত', 'name_ar' => 'الفضيلة', 'track' => 'kitab', 'sort_order' => 50, 'duration_years' => 2],
    ['code' => 'takmil', 'name' => 'তাকমিল (দাওরায়ে হাদীস)', 'name_ar' => 'التكميل', 'track' => 'kitab', 'sort_order' => 60, 'duration_years' => 1],

    // ---- হিফজ ও সংশ্লিষ্ট বিভাগ ----
    ['code' => 'nazera', 'name' => 'নাযেরা', 'name_ar' => 'الناظرة', 'track' => 'nazera', 'sort_order' => 70, 'duration_years' => 2],
    ['code' => 'hifz', 'name' => 'হিফজুল কুরআন', 'name_ar' => 'حفظ القرآن', 'track' => 'hifz', 'sort_order' => 80, 'duration_years' => 3],
    ['code' => 'qirat', 'name' => 'কিরাআত', 'name_ar' => 'القراءات', 'track' => 'qirat', 'sort_order' => 90, 'duration_years' => 2],

    // ---- উচ্চতর গবেষণা বিভাগ ----
    ['code' => 'ifta', 'name' => 'ইফতা (উচ্চতর ফিকহ)', 'name_ar' => 'الإفتاء', 'track' => 'ifta', 'sort_order' => 100, 'duration_years' => 1],
];
