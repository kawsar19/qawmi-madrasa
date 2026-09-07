<?php

declare(strict_types=1);

/**
 * হিসাবের চার্ট (chart of accounts)।
 *
 * MVP uses single-entry `transactions` that reference these accounts. The
 * chart is seeded now so the Phase 3 double-entry upgrade does not require a
 * data migration.
 *
 * type: asset | liability | income | expense | equity
 */
return [
    // সম্পদ
    ['code' => '1000', 'name' => 'নগদ তহবিল', 'type' => 'asset', 'sort_order' => 10],
    ['code' => '1010', 'name' => 'ব্যাংক হিসাব', 'type' => 'asset', 'sort_order' => 20],
    ['code' => '1020', 'name' => 'মোবাইল ব্যাংকিং (বিকাশ/নগদ)', 'type' => 'asset', 'sort_order' => 30],
    ['code' => '1100', 'name' => 'ছাত্রদের নিকট বকেয়া', 'type' => 'asset', 'sort_order' => 40],

    // আয়
    ['code' => '4000', 'name' => 'বেতন আয়', 'type' => 'income', 'sort_order' => 100],
    ['code' => '4010', 'name' => 'ভর্তি ফি আয়', 'type' => 'income', 'sort_order' => 110],
    ['code' => '4020', 'name' => 'পরীক্ষা ফি আয়', 'type' => 'income', 'sort_order' => 120],
    ['code' => '4030', 'name' => 'খানা বিল আয়', 'type' => 'income', 'sort_order' => 130],
    ['code' => '4100', 'name' => 'যাকাত আয়', 'type' => 'income', 'sort_order' => 140],
    ['code' => '4110', 'name' => 'লিল্লাহ আয়', 'type' => 'income', 'sort_order' => 150],
    ['code' => '4120', 'name' => 'সাধারণ দান আয়', 'type' => 'income', 'sort_order' => 160],

    // ব্যয়
    ['code' => '5000', 'name' => 'শিক্ষক বেতন', 'type' => 'expense', 'sort_order' => 200],
    ['code' => '5010', 'name' => 'কর্মচারী বেতন', 'type' => 'expense', 'sort_order' => 210],
    ['code' => '5020', 'name' => 'খাদ্য ক্রয়', 'type' => 'expense', 'sort_order' => 220],
    ['code' => '5030', 'name' => 'বিদ্যুৎ ও পানি', 'type' => 'expense', 'sort_order' => 230],
    ['code' => '5040', 'name' => 'নির্মাণ ও মেরামত', 'type' => 'expense', 'sort_order' => 240],
    ['code' => '5050', 'name' => 'কিতাব ও শিক্ষা উপকরণ', 'type' => 'expense', 'sort_order' => 250],
    ['code' => '5060', 'name' => 'যাতায়াত', 'type' => 'expense', 'sort_order' => 260],
    ['code' => '5070', 'name' => 'বিবিধ ব্যয়', 'type' => 'expense', 'sort_order' => 270],
];
