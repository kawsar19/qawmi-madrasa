<?php

declare(strict_types=1);

namespace App\Models\Academic;

use App\Contracts\TenantScoped;
use App\Models\People\Employee;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * শাখা — জামাতের ভেতরের সেকশন (ক, খ)।
 *
 * @property int $id
 * @property string $name
 */
class Section extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function jamaat(): BelongsTo
    {
        return $this->belongsTo(Jamaat::class);
    }

    /**
     * শাখা ইনচার্জ।
     *
     * @return BelongsTo<Employee, $this>
     */
    public function inCharge(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
