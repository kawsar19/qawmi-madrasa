<?php

declare(strict_types=1);

namespace Database\Factories\Central;

use App\Models\Central\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = 'madrasa-'.Str::lower(Str::random(6));

        return [
            'name' => 'জামিয়া '.fake()->word(),
            'slug' => $slug,
            'madrasa_type' => 'kitab',
            'address' => 'ঢাকা, বাংলাদেশ',
            'status' => Tenant::STATUS_ACTIVE,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => ['status' => Tenant::STATUS_SUSPENDED]);
    }

    public function onTrial(int $days = 14): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->addDays($days),
        ]);
    }

    /**
     * ডোমেইন সহ tenant — বেশিরভাগ ফিচার টেস্টে এটাই লাগে।
     */
    public function withDomain(?string $domain = null): static
    {
        return $this->afterCreating(function (Tenant $tenant) use ($domain): void {
            $tenant->domains()->create([
                'domain' => $domain ?? $tenant->slug.'.localhost',
                'is_primary' => true,
                'type' => 'subdomain',
            ]);
        });
    }
}
