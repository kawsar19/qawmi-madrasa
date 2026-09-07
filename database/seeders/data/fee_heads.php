<?php

declare(strict_types=1);

/**
 * ফি খাত।
 *
 * type: monthly       — প্রতি মাসে বিল হয়
 *       one_time      — ভর্তির সময় একবার
 *       exam          — পরীক্ষার সময়
 *       boarding      — খানা/সিট; KhanaBillRunner এখানে পোস্ট করে
 */
return [
    ['code' => 'monthly_fee', 'name' => 'মাসিক বেতন', 'type' => 'monthly', 'sort_order' => 10],
    ['code' => 'admission_fee', 'name' => 'ভর্তি ফি', 'type' => 'one_time', 'sort_order' => 20],
    ['code' => 'exam_fee', 'name' => 'পরীক্ষার ফি', 'type' => 'exam', 'sort_order' => 30],
    ['code' => 'khana_bill', 'name' => 'খানা বিল', 'type' => 'boarding', 'sort_order' => 40],
    ['code' => 'seat_rent', 'name' => 'সিট ভাড়া', 'type' => 'boarding', 'sort_order' => 50],
    ['code' => 'form_fee', 'name' => 'ফরম ফি', 'type' => 'one_time', 'sort_order' => 60],
    ['code' => 'library_fee', 'name' => 'লাইব্রেরি ফি', 'type' => 'one_time', 'sort_order' => 70],
    ['code' => 'electricity_bill', 'name' => 'বিদ্যুৎ বিল', 'type' => 'monthly', 'sort_order' => 80],
];
