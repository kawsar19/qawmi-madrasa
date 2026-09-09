<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * এক রসিদের টাকা এক ইনভয়েসে কতটা বসল।
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $payment_id
 * @property int $invoice_id
 * @property string $amount
 */
class PaymentAllocation extends Model implements TenantScoped
{
    use BelongsToTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    /** @return BelongsTo<Payment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
