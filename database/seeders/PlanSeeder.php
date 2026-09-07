<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Central\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'ফ্রি ট্রায়াল',
                'slug' => 'trial',
                'price' => 0,
                'billing_cycle' => 'yearly',
                'max_students' => 50,
                'max_teachers' => 10,
                'max_storage_mb' => 512,
                'features' => ['academic', 'people', 'attendance'],
                'sort_order' => 10,
            ],
            [
                'name' => 'স্ট্যান্ডার্ড',
                'slug' => 'standard',
                'price' => 12000,
                'billing_cycle' => 'yearly',
                'max_students' => 500,
                'max_teachers' => 50,
                'max_storage_mb' => 5120,
                'features' => ['academic', 'people', 'attendance', 'exam', 'finance', 'hifz', 'cms'],
                'sort_order' => 20,
            ],
            [
                'name' => 'প্রিমিয়াম',
                'slug' => 'premium',
                'price' => 24000,
                'billing_cycle' => 'yearly',
                'max_students' => null,
                'max_teachers' => null,
                'max_storage_mb' => 20480,
                'features' => ['academic', 'people', 'attendance', 'exam', 'finance', 'hifz', 'cms', 'boarding', 'donation'],
                'sort_order' => 30,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
