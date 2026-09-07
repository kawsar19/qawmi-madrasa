<?php

declare(strict_types=1);

/**
 * বেফাক কনভেনশন অনুসারে গ্রেড।
 *
 * Grades resolve from an overall percentage:
 *   sum(obtained) / sum(full_marks) * 100
 * (not an average of per-kitab GPAs).
 */
return [
    ['code' => 'mumtaz', 'name' => 'মুমতায', 'name_ar' => 'ممتاز', 'min_percent' => 80, 'max_percent' => 100, 'point' => 5.00, 'sort_order' => 10],
    ['code' => 'jayyid_jiddan', 'name' => 'জায়্যিদ জিদ্দান', 'name_ar' => 'جيد جدا', 'min_percent' => 65, 'max_percent' => 79.99, 'point' => 4.00, 'sort_order' => 20],
    ['code' => 'jayyid', 'name' => 'জায়্যিদ', 'name_ar' => 'جيد', 'min_percent' => 50, 'max_percent' => 64.99, 'point' => 3.00, 'sort_order' => 30],
    ['code' => 'maqbul', 'name' => 'মাকবুল', 'name_ar' => 'مقبول', 'min_percent' => 33, 'max_percent' => 49.99, 'point' => 2.00, 'sort_order' => 40],
    ['code' => 'rasib', 'name' => 'রাসিব (অকৃতকার্য)', 'name_ar' => 'راسب', 'min_percent' => 0, 'max_percent' => 32.99, 'point' => 0.00, 'sort_order' => 50, 'is_fail' => true],
];
