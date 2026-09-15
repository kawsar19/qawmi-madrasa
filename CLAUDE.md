# কওমি মাদরাসা ম্যানেজমেন্ট সিস্টেম

Multi-tenant SaaS for Qawmi madrasas in Bangladesh. Laravel 13 + Livewire 3 +
`stancl/tenancy` (**single-database mode**). UI language is Bengali.

Full spec: [requirement.md](requirement.md)

## Starting a session

```bash
php artisan migrate                     # apply new migrations, keep data
npm run build                           # or `npm run dev` for HMR
php artisan serve --port=8000
php artisan dev:info --port=8000        # every login URL and account
```

> **NEVER run `migrate:fresh`, `migrate:refresh` or `db:wipe` without asking
> the user first.** The dev database holds hand-entered work and is gitignored,
> so a rebuild destroys it with no undo. To check that a new migration is
> correct, `php artisan migrate` is enough. A pre-flight backup runs
> automatically (see below), but that is a safety net, not permission.

Demo password is always `password`. Logins appear in an amber box on the
login page itself (local/testing only — never production).

**Which URL to log in at.** The host decides which user pool is searched:

| Who | Where |
|---|---|
| Super admin | `app.localhost:8000/login` |
| Madrasa admin | `<slug>.app.localhost:8000/panel/login` |

A madrasa admin cannot log in on the central domain, and vice versa.
`*.localhost` resolves automatically on macOS — no /etc/hosts entries needed.

## Commands

```bash
php artisan migrate                # apply new migrations (keeps data)
php artisan db:show                # browse tables, columns and data (read-only)
php artisan db:backup              # timestamped copy -> storage/backups/ (sqlite + pgsql)
php artisan db:restore             # bring one back (interactive picker)
php artisan migrate:fresh --seed   # DESTRUCTIVE rebuild — ask the user first
php artisan dev:info               # login URLs and demo accounts
php artisan tenant:create          # provision a madrasa (interactive)
php artisan storage:link-tenants   # per-tenant public/storage symlinks
php artisan permissions:sync       # reconcile PermissionRegistry -> DB
php artisan pdf:spike              # regenerate the Bengali PDF proof

./vendor/bin/pest                  # tests
./vendor/bin/pint                  # format
./vendor/bin/phpstan analyse --memory-limit=1G   # level 5
```

## Non-negotiable constraints

These each cost real debugging time. Do not undo them.

### Dev data

- **`migrate:fresh` is destructive and the SQLite file is gitignored.** There
  is no Time Machine, no WAL, no committed copy — a rebuild is permanent.
  Ask before running it; use `php artisan migrate` to verify a new migration.
- `AppServiceProvider::guardDestructiveMigrations()` takes an automatic
  backup before `migrate:fresh` / `migrate:refresh` / `db:wipe`. Do not
  remove it, and do not treat it as a licence to rebuild freely.

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

### Uploaded files

- **Never `asset('storage/'.$path)`.** `FilesystemTenancyBootstrapper`
  suffixes `storage_path()`, so uploads land in `storage/tenant{id}/app/public`
  while `public/storage` points at `storage/app/public` — the two never meet
  and every image 404s. Use `media($path)` (`App\Support\Media`), which also
  keeps R2/Cloudinary one `.env` change away.
- Store through `Media::store($file, $folder)`, not `$file->store(...)`: on a
  remote disk nothing separates tenants, so the tenant id has to go into the
  key itself.
- After creating a tenant, run `php artisan storage:link-tenants` or its
  uploads have nowhere to be served from.
- **A tenant needs its whole storage tree, not just `app/public`.** Because
  `storage_path()` is suffixed, Laravel looks for `framework/cache`,
  `framework/views`, `framework/sessions` and `app/livewire-tmp` under
  `storage/tenant{id}/`. If `framework/cache` is missing, the first real-time
  facade write calls `tempnam()` on a directory that isn't there — PHP falls
  back to the system temp dir and raises a warning that becomes a 500 on
  `livewire/update`. `Media::prepareTenantStorage()` creates all of them, and
  the `TenantCreated` listener calls it.

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
- **Never `->where($col, 'like', ...)` for a user-facing search.** SQLite's
  `LIKE` is case-insensitive, Postgres' is not, so "abdul" stops matching
  "Abdul" the moment prod runs on Postgres — and the tests, which run on
  SQLite, never catch it. Use `App\Support\Search`, which picks `ilike` or
  `like` from the driver and escapes the `%`/`_` a user typed.

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
