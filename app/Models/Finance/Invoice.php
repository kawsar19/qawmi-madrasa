<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\People\Student;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * মাসিক বিল — এক ছাত্রের এক মাসের ইনভয়েস।
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $student_id
 * @property int $academic_session_id
 * @property int|null $jamaat_id
 * @property string $invoice_no
 * @property string $billing_month
 * @property string $gross_amount
 * @property string $discount_amount
 * @property string $net_amount
 * @property string $paid_amount
 * @property string $status
 * @property Carbon|null $issued_on
 * @property Carbon|null $due_on
 */
class Invoice extends Model implements TenantScoped
{
    use BelongsToTenant;

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'due_on' => 'date',
            'gross_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    /**
     * বকেয়া — আলাদা কলামে জমা রাখা হয় না, গণনা করা হয়।
     *
     * দুই জায়গায় সত্য রাখলে তারা একদিন আলাদা হয়ে যায়।
     */
    public function dueAmount(): float
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return 0.0;
        }

        return round(max((float) $this->net_amount - (float) $this->paid_amount, 0), 2);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * allocation থেকে আদায় ও অবস্থা পুনর্গণনা করে সংরক্ষণ করে।
     *
     * পেমেন্ট বসানো ও বাতিল — দুই দিকেই একই হিসাব চলে, তাই এক জায়গায়।
     */
    public function recalculatePaid(): void
    {
        // বাতিল ইনভয়েস আদায়ের হিসাবের বাইরে।
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        $paid = round((float) $this->allocations()->sum('amount'), 2);
        $net = round((float) $this->net_amount, 2);

        $this->update([
            'paid_amount' => $paid,
            // ০ টাকার বিল (পূর্ণ মওকুফ) সাথে সাথেই পরিশোধিত।
            'status' => match (true) {
                $paid >= $net => self::STATUS_PAID,
                $paid > 0 => self::STATUS_PARTIAL,
                default => self::STATUS_UNPAID,
            },
        ]);
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

    /** @return BelongsTo<Jamaat, $this> */
    public function jamaat(): BelongsTo
    {
        return $this->belongsTo(Jamaat::class);
    }

    /** @return HasMany<InvoiceLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    /** @return HasMany<PaymentAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * বকেয়া আছে এমন বিল — পুরনোটা আগে, কারণ আদায় সেই ক্রমেই বসে।
     *
     * @param  Builder<self>  $query
     */
    public function scopeOutstanding(Builder $query): void
    {
        $query->whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL])
            ->orderBy('billing_month');
    }
}
