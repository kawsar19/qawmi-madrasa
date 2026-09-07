# কওমি মাদরাসা ম্যানেজমেন্ট সিস্টেম

Multi-tenant SaaS for Qawmi madrasas in Bangladesh. Laravel 13 + Livewire 3 +
`stancl/tenancy` (**single-database mode**). UI language is Bengali.

Full spec: [requirement.md](requirement.md)

## Commands

```bash
php artisan migrate:fresh          # rebuild schema (SQLite in dev)
php artisan tenant:create          # provision a madrasa (interactive)
php artisan permissions:sync       # reconcile PermissionRegistry -> DB
php artisan pdf:spike              # regenerate the Bengali PDF proof

./vendor/bin/pest                  # tests
./vendor/bin/pint                  # format
./vendor/bin/phpstan analyse --memory-limit=1G   # level 5
```

## Non-negotiable constraints

These each cost real debugging time. Do not undo them.

### Tenancy

- **Single database.** `DatabaseTenancyBootstrapper` is deliberately disabled in
  `config/tenancy.php`. Isolation is `tenant_id` + a global scope, nothing else.
  Never run `tenants:migrate`; there is no `database/migrations/tenant/`.
- Tenant-scoped models use `App\Traits\BelongsToTenant` **and** implement
  `App\Contracts\TenantScoped`. The trait alone is not enough — `TenantScope`
  keys off the interface.
- **`DB::table()` bypasses the global scope.** On tenant-scoped tables either
  avoid it or add `->where('tenant_id', ...)` by hand.
- Every index on a tenant-scoped table starts with `tenant_id`; the global scope
  puts it in every query, so single-column indexes are dead weight.
- `users.tenant_id` is nullable and NULL means *super admin*, which is why
  `User` does **not** use `BelongsToTenant`.
- Laravel's `unique` validation rule ignores the global scope. Scope it
  manually: `Rule::unique(...)->where('tenant_id', tenant('id'))`.
- Queued jobs lose tenant context. Wrap console-dispatched work in
  `tenancy()->runForMultiple(...)`.

### Routing

- Central routes in `routes/web.php` are bound to `config('tenancy.central_domains')`.
  Without that binding, tenant `/` and central `/` are the same route
  (method + URI) and whichever registers last silently replaces the other —
  `routes/tenant.php` is registered later, on boot.
- `InitializeTenancyByDomain` (not `...BySubdomain`): `domains.domain` holds a
  full hostname so subdomains and custom domains resolve the same way.
- `routes/tenant.php` (panel) must be registered **before**
  `routes/tenant-public.php`, which owns `/` and may add wildcards.

### Livewire

- `livewire/update` and `livewire/upload-file` are wired through
  `InitializeTenancyByDomain` (in `AppServiceProvider` and `config/livewire.php`).
  Without this every `wire:click` runs with tenancy uninitialized, the global
  scope becomes a no-op, and components read across madrasas.

### Bengali PDF (mPDF)

Verified visually via `php artisan pdf:spike` — re-run it after any font change.

- `dompdf` breaks conjuncts. Never use it.
- Noto Sans Bengali and Amiri **fail to parse** in mPDF
  ("GPOS Lookup Type 5, Format 3 not supported"). Use the bundled
  Hind Siliguri + XB Riyaz.
- **Do not merge mPDF's default `fontdata`.** Doing so makes bold Bengali
  resolve through built-in substitution to DejaVu, which has no Bengali glyphs,
  and every heading prints as tofu boxes (□□□).
- `useOTL => 0xFF` on every font, or conjuncts do not form.
- Convert digits with `Bn::num()` in PHP; never rely on font features.

### Domain rules

- Money is `decimal(12,2)` with a `decimal:2` cast. Never float.
- Sequential numbers (roll, receipt) come from `DocumentNumberService`, which
  locks with `SELECT ... FOR UPDATE`. Never `MAX(col)+1` in PHP.
- Roll number belongs to the *enrollment*, not the student. `student_uid` is
  permanent; roll is reassigned yearly, often by merit.
- Marks reference `jamaat_kitab_id`, never `kitab_id`, so each year's full marks
  stay frozen in history.
- Dates are stored Gregorian; Hijri is render-time only (`HijriDate`). Never do
  arithmetic in Hijri — Bangladesh follows moon sighting, so a per-tenant
  ±offset exists.
- Receipts are **cancelled, never deleted** — the donor holds a paper copy.
- Mobile numbers are not globally unique; siblings share one.

## Layout

```
app/
  Contracts/TenantScoped.php     Models/Scopes/TenantScope.php
  Traits/BelongsToTenant.php     Support/{Bn,HijriDate,PermissionRegistry}.php
  Models/{Central,Academic,People,Exam,Hifz,Finance,Boarding,Cms}/
  Services/{Tenancy,Academic,Pdf,Support}/
database/seeders/data/           git-versioned Befaq reference data
                                 (10 marhalas, 189 kitabs, grades, fee heads,
                                  donation khats, chart of accounts)
storage/fonts/                   Hind Siliguri (SIL OFL)
```

Reference data is **copied into** each tenant on provisioning, so every madrasa
can edit its own curriculum independently.
