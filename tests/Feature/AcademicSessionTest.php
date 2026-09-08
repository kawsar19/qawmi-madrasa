<?php

declare(strict_types=1);

use App\Livewire\Tenant\Academic\SessionList;
use App\Models\Academic\AcademicSession;
use App\Models\Central\Tenant;
use App\Models\User;
use App\Services\Tenancy\TenantProvisioner;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

afterEach(fn () => tenancy()->end());

/**
 * Provisioned (not factory-made) because the screen is behind
 * `can:academic.session.view`, and only provisioning creates the roles.
 */
function sessionTenant(string $slug = 'darul-ulum'): Tenant
{
    $tenant = app(TenantProvisioner::class)->provision(
        ['name' => 'দারুল উলুম মাদরাসা', 'slug' => $slug, 'madrasa_type' => 'kitab'],
        "{$slug}.localhost",
        [
            'name' => 'মুহতামিম সাহেব',
            'email' => "admin@{$slug}.test",
            'password' => 'password',
            'mobile' => '01712345678',
        ],
    );

    // Provisioning leaves no trial and no subscription, which the panel
    // treats as expired — give it a trial so the screen is reachable.
    $tenant->update(['trial_ends_at' => now()->addMonth()]);

    return $tenant;
}

function sessionAdmin(Tenant $tenant): User
{
    tenancy()->initialize($tenant);
    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
    tenancy()->end();

    return $user;
}

it('shows the sessions screen to a permitted user', function () {
    $tenant = sessionTenant();

    $this->actingAs(sessionAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/academic/sessions')
        ->assertOk()
        ->assertSee('শিক্ষাবর্ষ')
        ->assertSee('এখনো কোনো শিক্ষাবর্ষ নেই।');
});

it('creates a session and makes the first one current', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);
    tenancy()->initialize($tenant);

    Livewire::actingAs($user)
        ->test(SessionList::class)
        ->set('name', '১৪৪৬-১৪৪৭ হিজরি')
        ->set('hijriYear', '1446')
        ->call('save')
        ->assertHasNoErrors();

    $session = AcademicSession::firstOrFail();

    // The very first session becomes current on its own, otherwise the
    // dashboard would keep warning that no session is set.
    expect($session->name)->toBe('১৪৪৬-১৪৪৭ হিজরি')
        ->and($session->is_current)->toBeTrue();
});

it('does not auto-current a second session', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);
    tenancy()->initialize($tenant);

    AcademicSession::create(['name' => 'প্রথম বর্ষ', 'is_current' => true]);

    Livewire::actingAs($user)
        ->test(SessionList::class)
        ->set('name', 'দ্বিতীয় বর্ষ')
        ->call('save')
        ->assertHasNoErrors();

    expect(AcademicSession::where('name', 'দ্বিতীয় বর্ষ')->firstOrFail()->is_current)->toBeFalse();
});

it('keeps exactly one current session when switching', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);
    tenancy()->initialize($tenant);

    $old = AcademicSession::create(['name' => 'পুরনো বর্ষ', 'is_current' => true]);
    $new = AcademicSession::create(['name' => 'নতুন বর্ষ']);

    Livewire::actingAs($user)
        ->test(SessionList::class)
        ->call('makeCurrent', $new->id);

    expect($new->refresh()->is_current)->toBeTrue()
        ->and($old->refresh()->is_current)->toBeFalse()
        ->and(AcademicSession::where('is_current', true)->count())->toBe(1);
});

it('rejects a duplicate name within the same madrasa', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);
    tenancy()->initialize($tenant);

    AcademicSession::create(['name' => '১৪৪৬-১৪৪৭ হিজরি']);

    Livewire::actingAs($user)
        ->test(SessionList::class)
        ->set('name', '১৪৪৬-১৪৪৭ হিজরি')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

it('allows the same session name in a different madrasa', function () {
    $a = sessionTenant('madrasa-a');
    $b = sessionTenant('madrasa-b');

    tenancy()->initialize($a);
    AcademicSession::create(['name' => '১৪৪৬-১৪৪৭ হিজরি']);
    tenancy()->end();

    $userOfB = sessionAdmin($b);
    tenancy()->initialize($b);

    // The unique rule is scoped by hand — Laravel's `unique` ignores the
    // global scope, so without that scoping this would wrongly fail.
    Livewire::actingAs($userOfB)
        ->test(SessionList::class)
        ->set('name', '১৪৪৬-১৪৪৭ হিজরি')
        ->call('save')
        ->assertHasNoErrors();

    expect(AcademicSession::count())->toBe(1);
});

it('rejects an end date before the start date', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);
    tenancy()->initialize($tenant);

    Livewire::actingAs($user)
        ->test(SessionList::class)
        ->set('name', 'উল্টো বর্ষ')
        ->set('startsOn', '2026-06-01')
        ->set('endsOn', '2026-01-01')
        ->call('save')
        ->assertHasErrors(['endsOn']);
});

it('refuses to delete the current session', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);
    tenancy()->initialize($tenant);

    $session = AcademicSession::create(['name' => 'চলতি বর্ষ', 'is_current' => true]);

    Livewire::actingAs($user)
        ->test(SessionList::class)
        ->call('delete', $session->id);

    expect(AcademicSession::find($session->id))->not->toBeNull();
});

it('refuses to edit or delete a locked session', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);
    tenancy()->initialize($tenant);

    $session = AcademicSession::create(['name' => 'লক করা বর্ষ', 'is_locked' => true]);

    Livewire::actingAs($user)
        ->test(SessionList::class)
        ->call('edit', $session->id)
        ->set('name', 'বদলানো নাম')
        ->call('save');

    expect($session->refresh()->name)->toBe('লক করা বর্ষ');

    Livewire::actingAs($user)
        ->test(SessionList::class)
        ->call('delete', $session->id);

    expect(AcademicSession::find($session->id))->not->toBeNull();
});

it('locks and unlocks a session', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);
    tenancy()->initialize($tenant);

    $session = AcademicSession::create(['name' => 'বর্ষ']);

    $component = Livewire::actingAs($user)->test(SessionList::class);

    $component->call('toggleLock', $session->id);
    expect($session->refresh()->is_locked)->toBeTrue();

    $component->call('toggleLock', $session->id);
    expect($session->refresh()->is_locked)->toBeFalse();
});

it('authorizes actions over the real livewire endpoint', function () {
    $tenant = sessionTenant();
    $user = sessionAdmin($tenant);

    // Livewire::test() calls the component directly, so it never exercises
    // /livewire/update — the endpoint where SetPermissionsTeam was missing
    // and every wire:click died with "This action is unauthorized".
    //
    $html = $this->actingAs($user)
        ->get('http://darul-ulum.localhost/panel/academic/sessions')
        ->assertOk()
        ->getContent();

    expect($html)->toBeString();

    preg_match('/wire:snapshot="([^"]*)"/', (string) $html, $m);
    expect($m)->not->toBeEmpty();

    $snapshot = html_entity_decode($m[1], ENT_QUOTES);

    // A real browser's next request starts with no team id and loads the user
    // from the session cookie, with no roles relation cached. Reproducing both
    // is what makes this test able to fail: with a primed $user the roles are
    // already in memory and the check passes even with no team set.
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    tenancy()->initialize($tenant);
    $freshUser = User::query()->findOrFail($user->getKey());
    tenancy()->end();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    $response = $this->actingAs($freshUser)
        ->withHeaders(['X-Livewire' => 'true', 'Referer' => 'http://darul-ulum.localhost/panel/academic/sessions'])
        ->postJson('http://darul-ulum.localhost/livewire/update', [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => [],
                'calls' => [['method' => 'create', 'params' => []]],
            ]],
        ])
        ->assertOk();

    // Asserting the *effect*, not just the status: Livewire answers 200 even
    // when it discards a call, so a status-only assertion would prove nothing.
    // The form only renders if create() actually ran past its authorize().
    $rendered = $response->json('components.0.effects.html');

    expect($rendered)->toBeString()
        ->and($rendered)->toContain('নতুন শিক্ষাবর্ষ');
});

it('never shows another madrasa\'s sessions', function () {
    $a = sessionTenant('madrasa-a');
    $b = sessionTenant('madrasa-b');

    tenancy()->initialize($a);
    AcademicSession::create(['name' => 'ক-মাদরাসার বর্ষ']);
    tenancy()->end();

    $this->actingAs(sessionAdmin($b))
        ->get('http://madrasa-b.localhost/panel/academic/sessions')
        ->assertOk()
        ->assertDontSee('ক-মাদরাসার বর্ষ');
});
