<?php

declare(strict_types=1);

namespace App\Services\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * ক্রমিক নম্বর বরাদ্দকারী (রোল, রসিদ নম্বর, ছাত্র আইডি ইত্যাদি)।
 *
 * NEVER compute the next number with MAX(col)+1 in PHP: two concurrent
 * requests would read the same max and produce duplicates. This locks the
 * counter row with SELECT ... FOR UPDATE inside a transaction instead.
 */
class DocumentNumberService
{
    /**
     * পরবর্তী নম্বর বরাদ্দ করে ফেরত দেয়।
     */
    public function next(string $entity, string $scopeKey = '', ?string $prefix = null): int
    {
        if (! tenancy()->initialized) {
            throw new RuntimeException('Document numbers require a tenant context.');
        }

        $tenantId = tenant()->getTenantKey();

        return DB::transaction(function () use ($entity, $scopeKey, $prefix, $tenantId): int {
            $row = DB::table('document_numbers')
                ->where('tenant_id', $tenantId)
                ->where('entity', $entity)
                ->where('scope_key', $scopeKey)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                // Another request may insert concurrently; the unique index
                // makes that safe and we simply re-read below.
                DB::table('document_numbers')->insertOrIgnore([
                    'tenant_id' => $tenantId,
                    'entity' => $entity,
                    'scope_key' => $scopeKey,
                    'prefix' => $prefix,
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('document_numbers')
                    ->where('tenant_id', $tenantId)
                    ->where('entity', $entity)
                    ->where('scope_key', $scopeKey)
                    ->lockForUpdate()
                    ->first();
            }

            $next = (int) $row->last_number + 1;

            DB::table('document_numbers')
                ->where('id', $row->id)
                ->update(['last_number' => $next, 'updated_at' => now()]);

            return $next;
        });
    }

    /**
     * প্রিফিক্স ও প্যাডিং সহ ফরম্যাট করা নম্বর। ("INV-2026-000123")
     */
    public function nextFormatted(
        string $entity,
        string $scopeKey = '',
        string $prefix = '',
        int $padding = 6,
    ): string {
        $number = $this->next($entity, $scopeKey, $prefix === '' ? null : $prefix);

        return $prefix.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
    }

    /**
     * চলতি সর্বশেষ নম্বর (বরাদ্দ না করে)।
     */
    public function peek(string $entity, string $scopeKey = ''): int
    {
        $row = DB::table('document_numbers')
            ->where('tenant_id', tenant()->getTenantKey())
            ->where('entity', $entity)
            ->where('scope_key', $scopeKey)
            ->first();

        return $row === null ? 0 : (int) $row->last_number;
    }
}
