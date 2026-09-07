<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Central\Tenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * ব্যবহারকারী — সুপার অ্যাডমিন (tenant_id = null) অথবা কোনো মাদরাসার ইউজার।
 *
 * Deliberately does NOT use BelongsToTenant: a NULL tenant_id identifies a
 * super admin, and the global scope would hide those rows from the central
 * panel. Tenant scoping for auth happens in TenantUserProvider instead.
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $email
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function profileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * সুপার অ্যাডমিন — কোনো মাদরাসার সাথে যুক্ত নয়, সব tenant দেখতে পারে।
     */
    public function isSuperAdmin(): bool
    {
        return $this->tenant_id === null;
    }

    public function belongsToTenant(int|string|null $tenantId): bool
    {
        return $tenantId !== null && (int) $this->tenant_id === (int) $tenantId;
    }

    /** @param Builder<self> $query */
    public function scopeSuperAdmins(Builder $query): void
    {
        $query->whereNull('tenant_id');
    }

    /** @param Builder<self> $query */
    public function scopeForTenant(Builder $query, int $tenantId): void
    {
        $query->where('tenant_id', $tenantId);
    }
}
