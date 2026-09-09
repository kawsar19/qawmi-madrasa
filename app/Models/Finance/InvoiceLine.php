<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ইনভয়েসের এক খাতের লাইন।
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $invoice_id
 * @property int|null $fee_head_id
 * @property string $fee_head_name
 * @property string $rate
 * @property string $discount_amount
 * @property string $amount
 * @property string|null $discount_type
 */
class InvoiceLine extends Model implements TenantScoped
{
    use BelongsToTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** @return BelongsTo<FeeHead, $this> */
    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class);
    }
}
