<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Services\Support\DocumentNumberService;

function numberTenant(string $slug): Tenant
{
    return Tenant::create([
        'name' => $slug,
        'slug' => $slug,
        'status' => Tenant::STATUS_ACTIVE,
    ]);
}

afterEach(fn () => tenancy()->end());

it('allocates sequential numbers', function () {
    tenancy()->initialize(numberTenant('a'));
    $service = app(DocumentNumberService::class);

    expect($service->next('receipt'))->toBe(1)
        ->and($service->next('receipt'))->toBe(2)
        ->and($service->next('receipt'))->toBe(3);
});

it('keeps separate sequences per entity and scope', function () {
    tenancy()->initialize(numberTenant('a'));
    $service = app(DocumentNumberService::class);

    $service->next('receipt');
    $service->next('receipt');

    // A different entity starts fresh.
    expect($service->next('invoice'))->toBe(1);

    // Roll numbers are scoped per session+jamaat, so each scope restarts.
    expect($service->next('roll', 'session:1|jamaat:1'))->toBe(1)
        ->and($service->next('roll', 'session:1|jamaat:2'))->toBe(1)
        ->and($service->next('roll', 'session:1|jamaat:1'))->toBe(2);
});

it('keeps sequences separate per tenant', function () {
    $a = numberTenant('a');
    $b = numberTenant('b');
    $service = app(DocumentNumberService::class);

    tenancy()->initialize($a);
    $service->next('receipt');
    $service->next('receipt');

    // Tenant B must not continue A's sequence.
    tenancy()->initialize($b);
    expect($service->next('receipt'))->toBe(1);

    tenancy()->initialize($a);
    expect($service->next('receipt'))->toBe(3);
});

it('formats numbers with a prefix and padding', function () {
    tenancy()->initialize(numberTenant('a'));
    $service = app(DocumentNumberService::class);

    expect($service->nextFormatted('invoice', '', 'INV-', 6))->toBe('INV-000001')
        ->and($service->nextFormatted('invoice', '', 'INV-', 6))->toBe('INV-000002');
});

it('peeks without allocating', function () {
    tenancy()->initialize(numberTenant('a'));
    $service = app(DocumentNumberService::class);

    expect($service->peek('receipt'))->toBe(0);

    $service->next('receipt');

    expect($service->peek('receipt'))->toBe(1)
        ->and($service->peek('receipt'))->toBe(1);
});

it('refuses to allocate without a tenant context', function () {
    app(DocumentNumberService::class)->next('receipt');
})->throws(RuntimeException::class);
