<?php

declare(strict_types=1);

/**
 * দান/অনুদানের খাত।
 *
 * is_restricted = true মানে এই টাকা শুধু নির্দিষ্ট খাতেই ব্যয় করা যাবে
 * (যাকাত, ফিতরা ইত্যাদি শরীয়াহ-নির্ধারিত)।
 */
return [
    ['code' => 'zakat', 'name' => 'যাকাত', 'is_restricted' => true, 'sort_order' => 10],
    ['code' => 'lillah', 'name' => 'লিল্লাহ', 'is_restricted' => true, 'sort_order' => 20],
    ['code' => 'general_donation', 'name' => 'সাধারণ দান', 'is_restricted' => false, 'sort_order' => 30],
    ['code' => 'fitra', 'name' => 'ফিতরা', 'is_restricted' => true, 'sort_order' => 40],
    ['code' => 'qurbani_skin', 'name' => 'কুরবানির চামড়া', 'is_restricted' => true, 'sort_order' => 50],
    ['code' => 'orphan_fund', 'name' => 'এতিম ফান্ড', 'is_restricted' => true, 'sort_order' => 60],
    ['code' => 'construction', 'name' => 'নির্মাণ ফান্ড', 'is_restricted' => true, 'sort_order' => 70],
    ['code' => 'waqf', 'name' => 'ওয়াকফ', 'is_restricted' => true, 'sort_order' => 80],
    ['code' => 'sadaqah', 'name' => 'সাদাকা', 'is_restricted' => false, 'sort_order' => 90],
];
