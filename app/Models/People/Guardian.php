<?php

declare(strict_types=1);

namespace App\Models\People;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * অভিভাবক। একজন অভিভাবক একাধিক ছাত্রের সাথে যুক্ত হতে পারেন (ভাইবোন)।
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string|null $relation
 * @property string|null $mobile
 */
class Guardian extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const RELATION_FATHER = 'father';

    public const RELATION_MOTHER = 'mother';

    public const RELATION_UNCLE = 'uncle';

    public const RELATION_BROTHER = 'brother';

    public const RELATION_OTHER = 'other';

    protected $guarded = [];

    /** @return BelongsToMany<Student, $this> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}
