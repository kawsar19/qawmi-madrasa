<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Models\User;
use Database\Factories\Central\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * একটি মাদরাসা (tenant).
 *
 * Single-database tenancy: this is the only model with a real "tenant identity";
 * every other tenant-scoped model carries a tenant_id column instead.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property string $status
 * @property Carbon|null $trial_ends_at
 * @property array<string, mixed>|null $data
 */
class Tenant extends BaseTenant
{
    use HasDomains;

    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_EXPIRED = 'expired';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * Real table columns. Anything NOT listed here gets serialised into the
     * `data` JSON column by stancl's VirtualColumn trait, so this list must
     * stay in sync with the tenants migration.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'uuid',
            'name',
            'slug',
            'eiin',
            'madrasa_type',
            'address',
            'logo_path',
            'status',
            'trial_ends_at',
            'deleted_at',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $tenant): void {
            $tenant->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * stancl's HasDomains::domains() has no return type, so static analysis
     * cannot see the relation. Declaring it here types it properly.
     *
     * @return HasMany<Domain, $this>
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'tenant_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
