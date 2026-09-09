<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Models\Academic\AcademicSession;
use App\Models\People\Student;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * আদায় — এক রসিদ, একাধিক ইনভয়েসে বণ্টিত হতে পারে।
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $student_id
 * @property int $academic_session_id
 * @property string $receipt_no
 * @property string $amount
 * @property string $method
 * @property string|null $reference
 * @property int|null $received_by
 * @property string|null $cancel_reason
 * @property Carbon|null $paid_on
 * @property Carbon|null $cancelled_at
 */
class Payment extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const METHOD_CASH = 'cash';

    public const METHOD_BANK = 'bank';

    public const METHOD_MOBILE = 'mobile';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'cancelled_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    /**
     * যত টাকা ইনভয়েসে বসানো হয়েছে।
     */
    public function allocatedAmount(): float
    {
        return round((float) $this->allocations()->sum('amount'), 2);
    }

    /**
     * অগ্রিম জমা — যে টাকা এখনো কোনো ইনভয়েসে বসেনি।
     *
     * অভিভাবক বিলের চেয়ে বেশি দিলে বাকিটা এখানে থাকে; পরের মাসের বিল
     * তৈরি হলে তখন বসানো যাবে।
     */
    public function unallocatedAmount(): float
    {
        if ($this->isCancelled()) {
            return 0.0;
        }

        return round(max((float) $this->amount - $this->allocatedAmount(), 0), 2);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<AcademicSession, $this> */
    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    /** @return BelongsTo<User, $this> */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /** @return BelongsTo<User, $this> */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /** @return HasMany<PaymentAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('cancelled_at');
    }
}
