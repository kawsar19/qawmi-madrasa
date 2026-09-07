# কওমি মাদরাসা ম্যানেজমেন্ট সিস্টেম (SaaS) — Implementation Plan

## Context

`/Users/kawsarahmed/Desktop/Qawmi-madrasa/` সম্পূর্ণ খালি (শুধু একটি ফাঁকা `requirement.md`)। এটি greenfield প্রজেক্ট।

**লক্ষ্য:** বাংলাদেশের কওমি মাদরাসাগুলোর জন্য একটি multi-tenant SaaS — যেখানে সুপার অ্যাডমিন সব মাদরাসা নিয়ন্ত্রণ করবে, আর প্রতিটি মাদরাসা নিজের ডোমেইনে লগইন করে ছাত্র/শিক্ষক CRUD, ভর্তি, হাজিরা, পরীক্ষা, ফি/হিসাব, হিফজ ট্র্যাকিং, বোর্ডিং ও দান-যাকাত ব্যবস্থাপনা করবে। প্রতিটি মাদরাসার আলাদা ল্যান্ডিং ওয়েবসাইট থাকবে (subdomain + custom domain), কিন্তু সব ডেটা একই ডাটাবেসে `tenant_id` দিয়ে আলাদা থাকবে।

এই ফেজে দুটি deliverable: (১) প্রজেক্টের `requirement.md` (বাংলা, সম্পূর্ণ স্পেসিফিকেশন), (২) Phase 0 + Phase 1 এর কোড।

---

## Locked Decisions

| বিষয়          | সিদ্ধান্ত                                                                                                                                 |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| Stack          | Laravel 11, PHP 8.3, MySQL 8, Redis                                                                                                       |
| Tenancy        | `stancl/tenancy` v3 — **single-database mode** (`DatabaseTenancyBootstrapper` বাদ), `tenant_id` কলাম + global scope                       |
| Tenant key     | `unsignedBigInteger` (index-friendly), পাশাপাশি `tenants.uuid` public identifier                                                          |
| Frontend       | Livewire 3 + Tailwind + Alpine; পাবলিক সাইট plain Blade                                                                                   |
| Domain         | Subdomain (`madrasa.app.com`) **এবং** custom domain (`madrasa.edu.bd`) — `InitializeTenancyByDomain` (full hostname), Caddy on-demand TLS |
| Language       | শুধু বাংলা (`lang/bn`), বাংলা সংখ্যা, Gregorian + Hijri দুটোই প্রদর্শন                                                                    |
| Billing        | ম্যানুয়াল — সুপার অ্যাডমিন প্ল্যান/মেয়াদ বসাবে, অফলাইন bKash/ব্যাংক পেমেন্ট রেকর্ড করবে                                                 |
| Landing site   | ৪টি রেডি Blade টেমপ্লেট + DB-driven কনটেন্ট + CSS-variable theming (page builder নয়)                                                     |
| Accounts (MVP) | **Single-entry `transactions`** — `ledger_accounts` schema এখনই রাখা হবে, double-entry Phase 3                                            |
| PDF            | **mPDF** (receipt/invoice/list) + **Browsershot** (marksheet/ID card/sanad) hybrid                                                        |
| Staff          | **একটি `employees` টেবিল**, `is_teacher` flag                                                                                             |
| Multi-branch   | এখন feature নয়, কিন্তু nullable `branch_id` কলাম রাখা হবে                                                                                |
| Modules        | ❌ `nwidart/laravel-modules` — flat `app/` + domain subfolders                                                                            |

---

## Directory Structure

```
app/
├── Models/{Central,Academic,People,Attendance,Exam,Hifz,Finance,Boarding,Cms}/
├── Livewire/{Central,Academic,People,Attendance,Exam,Hifz,Finance,Boarding,Cms,Shared}/
├── Http/Controllers/{Central,Tenant,Public}/
├── Http/Middleware/  # EnsureSubscriptionActive, SetCurrentAcademicSession,
│                     # ResolvePublicSite, SetPermissionsTeam, EnsureSuperAdmin
├── Services/{Academic,Exam,Finance,Hifz,Boarding,Pdf,Tenancy}/
├── Support/{Bn.php, HijriDate.php, Enums/}
├── Traits/BelongsToTenant.php
├── Policies/  Observers/  Providers/

routes/{web.php, tenant.php, tenant-public.php}
resources/views/{central,tenant,public/templates/{classic,modern,minimal,heritage},pdf,components,layouts}
lang/bn/
database/{migrations,seeders/data/}
```

> **Single-DB nuance:** `database/migrations/tenant/` ব্যবহার হবে **না**। সব টেবিল (central + tenant-scoped) `database/migrations/`-এ, একবারই রান হবে। Isolation পুরোপুরি `tenant_id` + global scope নির্ভর। `tenants:migrate` চালানো যাবে না।

---

## Database Schema (সারসংক্ষেপ)

সব tenant-scoped টেবিলে: `id`, `tenant_id BIGINT UNSIGNED`, `branch_id` (nullable), `timestamps`, প্রয়োজনে `deleted_at`।
**সব index composite, `tenant_id` প্রথমে** — global scope প্রতিটি query-তে `WHERE tenant_id = ?` যোগ করে, তাই single-column index অকেজো।

### Central (tenant_id নেই)

- `tenants` — name, slug, eiin, madrasa_type, address, logo, status(pending/active/suspended/expired), trial_ends_at, data json
- `domains` — domain (unique, full hostname), is_primary, type(subdomain/custom), ssl_status, verified_at
- `plans` — price, billing_cycle, max_students/teachers/storage_mb, `features` json
- `subscriptions` — tenant_id, plan_id, starts_at, ends_at, status; index `(ends_at)` expiry sweep-এর জন্য
- `subscription_payments` — amount, method(bkash/nagad/bank/cash), txn_ref, attachment, recorded_by
- `users` — **tenant_id nullable** (null ⇒ super admin), unique`(tenant_id,email)` ও `(tenant_id,username)`; morph `profileable` → Student/Employee/Guardian
- `quran_paras` (৩০), `quran_surahs` (১১৪), `site_templates` — immutable reference, সব tenant শেয়ার করে

### Academic

- `academic_sessions` — name, hijri_year, gregorian_year, is_current, **is_locked**
- `marhalas` — ৬ স্তর (ইবতেদাইয়্যাহ→তাকমিল) + `track` enum(kitab/hifz/nazera/qirat/ifta)
- `jamaats` → `sections` (section-এ in-charge `employee_id`)
- `kitabs` — মাস্টার কিতাব লিস্ট (subject নয়), category, name_ar
- `jamaat_kitabs` — **session-scoped কারিকুলাম**: session+jamaat+kitab, full/pass marks, written/oral split, teacher_id
  > সবচেয়ে গুরুত্বপূর্ণ Qawmi ডিজাইন পয়েন্ট — marks `jamaat_kitab_id` রেফার করে, `kitab_id` নয়, তাই প্রতি বছরের পূর্ণমান ইতিহাসে ফ্রিজ থাকে।

### People

- `students` — `student_uid` (স্থায়ী), name/father/mother, residency_type(abasik/onabasik/dine_esho), is_orphan, fee_waiver_percent, status
- `student_enrollments` — **roll_no এখানেই থাকে**; unique`(tenant_id, academic_session_id, jamaat_id, roll_no)`
- `guardians` + `guardian_student` pivot
- `employees` — teacher ও non-teaching একসাথে, `is_teacher` flag, designation(মুহতামিম/শিক্ষা সচিব/উস্তাদ/মুহাদ্দিস), takmil_year, salary
- `teacher_section_assignments` — row-level scoping-এর ভিত্তি
- `admissions` — application → shortlist → test → approve → `AdmissionService::enroll()` (student + enrollment + uid + roll, এক transaction-এ)
- `id_card_templates`, `id_cards`

### Attendance

- `student_attendances` — unique`(tenant_id, student_id, date, period)`; `period` enum(fajr/morning/zuhr/asr/maghrib/isha/full_day) — কওমিতে ওয়াক্তভিত্তিক হাজিরা হয়
- `employee_attendances`, `holidays`, `attendance_settings`

### Exam & Result

- `exam_terms` (সাময়িক/অর্ধবার্ষিক/বার্ষিক), `exam_schedules`
- `marks` — written/oral/practical, unique`(tenant_id, exam_term_id, student_id, jamaat_kitab_id)`
- `grade_scales` — মুমতায ৮০+, জায়্যিদ জিদ্দান ৬৫+, জায়্যিদ ৫০+, মাকবুল ৩৩+, রাসিব <৩৩
- `exam_results` — computed summary, percentage, grade, position (মেধাক্রম)
- `result_settings` — `fail_any_kitab_fails_all` (বেফাক কনভেনশন), pass_mark_percent
  > গ্রেড হিসাব: `sum(obtained)/sum(full_marks) × 100` → grade resolve (per-kitab GPA গড় নয়)

### Hifz / Nazera

- `hifz_enrollments` — type(hifz/nazera/qirat/tajweed), teacher, current para/surah/ayah, completed_paras (cached), khatam_count
- `hifz_daily_entries` — `entry_type` enum(**sabak** সবক / **sabqi** সবকীনা / **manzil** আমুখতা / nazera / tilawat), para+surah+ayah range, lines/pages, quality, mistakes_count, teacher_remarks; unique`(tenant_id, enrollment_id, date, entry_type)`
- `hifz_milestones` — para_complete / half_quran / full_quran / sanad
- `hifz_settings` — daily_target_lines, manzil_cycle_days, use_lines_or_pages

### Finance (single-entry MVP)

- `fee_heads` (মাসিক বেতন, ভর্তি ফি, পরীক্ষা ফি, খানা বিল, সিট ভাড়া) → `fee_structures` (session+jamaat+head+residency) → `student_fee_overrides` (এতিম/গরিব/হাফেজ ছাড়)
- `invoices` + `invoice_lines` — unique`(tenant_id, student_id, billing_month)` ⇒ idempotent billing run
- `fee_payments` + `fee_payment_allocations` — receipt_no, method, **is_cancelled (কখনো delete নয়)**
- `transactions` — **single-entry**: type(income/expense), `ledger_account_id`, amount, date, reference morph
- `ledger_accounts` — chart of accounts এখনই সিড হবে (double-entry upgrade path খোলা রাখতে)
- Phase 2: `salary_payments`

### Donation / Zakat / Lillah

- `donation_khats` — যাকাত, লিল্লাহ, সাধারণ দান, কুরবানির চামড়া, ফিতরা, এতিম ফান্ড, নির্মাণ, ওয়াকফ; is_restricted, target_amount
- `donors` — donor_code, mobile, is_regular, monthly_pledge, total_donated (cached)
- `collectors` (মুহাসসিল/আদায়কারী) + `receipt_books` (ছাপানো রসিদ বই কন্ট্রোল: from/to serial, issued_to, status)
- `donations` — receipt_no, khat, collector, amount, cash/goods/kind, method, hijri_year, `zakat_year`, receipt_pdf_path, is_cancelled
- ডোনার বার্ষিক স্টেটমেন্ট = `donations` group by hijri_year → PDF

### Boarding / Khana

- `buildings` → `rooms` → `seats` → `seat_allocations`
  > MySQL 8-এ partial unique নেই। "এক seat-এ একটাই active allocation" এর জন্য generated column: `active_seat_key AS (CASE WHEN status='active' THEN seat_id END) STORED` + `UNIQUE(tenant_id, active_seat_key)`। একই কৌশল student-এর জন্যও।
- `meal_plans`, `student_meal_subscriptions`, `meal_charts` (সাপ্তাহিক খাদ্য তালিকা)
- `khana_bills` — calculation worksheet; ফলাফল **`invoices`-এ `invoice_line` হিসেবে পোস্ট হয়** (`fee_head.type='boarding'`), যাতে বকেয়া রিপোর্ট এক query-তে হয়
- `guardian_visits` (অভিভাবক সাক্ষাৎ লগ), `student_leaves` (বাড়ি যাওয়ার ছুটি)
- `boarding_settings` — khana_billing_mode(fixed_monthly/per_day_present/per_meal)

### CMS / Landing site

- `site_settings` (per tenant, ১ row) — template_slug, logo, primary/secondary/accent color, font_family, about/mission/founder_message, contact, socials, admission_open, custom_css, seo_meta
- `pages`, `sliders`, `notices`, `gallery_albums`+`gallery_items`, `public_teacher_profiles`, `menu_items`, `contact_messages`, `online_admission_settings`
- Phase 2: `page_blocks` (reorderable home sections)

### System

- `settings` (key-value), `media` (+tenant_id), `activity_log` (+tenant_id), `sms_logs`
- **`document_numbers`** — সব human-facing নম্বরের sequence: `entity` + `scope_key` + `last_number`; `SELECT … FOR UPDATE` দিয়ে race-safe

---

## Roles & Permissions

`spatie/laravel-permission` **teams mode**, `team_foreign_key = 'tenant_id'`। Tenancy init-এর পরপরই middleware-এ `setPermissionsTeamId(tenant('id'))`।

Permission naming: `module.entity.action` (~১২০-১৫০টি)। একটি `PermissionRegistry` PHP array = single source of truth + `permissions:sync` artisan command।

| রোল                                 | স্কোপ                                                                                 |
| ----------------------------------- | ------------------------------------------------------------------------------------- |
| সুপার অ্যাডমিন                      | central; `Gate::before` দিয়ে সব bypass                                               |
| মাদরাসা অ্যাডমিন                    | সব tenant permission (user, settings, CMS, delete সহ)                                 |
| মুহতামিম                            | user-management ও destructive settings ছাড়া সব; সম্পূর্ণ আর্থিক _view_ + অনুমোদন     |
| শিক্ষা সচিব                         | একাডেমিক (মারহালা/জামাত/কিতাব/পরীক্ষা/ফলাফল/হাজিরা/ভর্তি/হিফজ)। ফাইন্যান্স নেই        |
| হিসাবরক্ষক                          | ফি, বিল, আদায়, লেজার, দান, বেতন, আর্থিক রিপোর্ট। ছাত্র read-only                     |
| উস্তাদ/শিক্ষক                       | **row-level scoped** — নিজের section-এর হাজিরা, নিজের কিতাবের নম্বর, নিজের হিফজ ছাত্র |
| নাযেমে দারুল ইকামা                  | রুম/সিট/খাদ্য তালিকা/খানা বিল/সাক্ষাৎ/ছুটি                                            |
| আদায়কারী                           | শুধু নিজের রসিদ বইয়ে দান এন্ট্রি + নিজের আদায় রিপোর্ট                               |
| অভিভাবক (Phase 2) / ছাত্র (Phase 3) | পোর্টাল — নিজের সন্তানের/নিজের তথ্য                                                   |

Permission-এর উপরে **Policies** দিয়ে row-level enforcement: `MarkPolicy` (`jamaat_kitabs.teacher_id` মিল), `StudentAttendancePolicy` (`teacher_section_assignments`), `HifzDailyEntryPolicy` (`hifz_enrollments.teacher_id`), `DonationPolicy` (নিজের `receipt_book_id`)।

সুপার অ্যাডমিন impersonation: `tenancy()->impersonate()`, প্রতিবার `activity_log`-এ লগ।

---

## Routing

`config/tenancy.php` → `central_domains = ['app.example.com', 'localhost']`। বাকি সব হোস্ট tenant হিসেবে resolve।

**`InitializeTenancyByDomain`** ব্যবহার হবে, `...BySubdomain` নয় — কারণ subdomain ও custom domain দুটোই সাপোর্ট করতে হবে; `domains.domain`-এ **পূর্ণ hostname** থাকবে (একই tenant-এর দুটি row)।

Middleware groups (`bootstrap/app.php`):

- `tenant.public` → InitializeTenancyByDomain, PreventAccessFromCentralDomains, ResolvePublicSite, SetTenantLocale
- `tenant.panel` → উপরেরগুলো + auth, SetPermissionsTeam, EnsureSubscriptionActive, SetCurrentAcademicSession
- `central.admin` → auth, EnsureSuperAdmin

Route files:

- `routes/web.php` — SaaS marketing site + `/admin/*` সুপার অ্যাডমিন প্যানেল (tenants, plans, subscriptions, payments, domains, impersonate)
- `routes/tenant.php` — `/panel/*` মাদরাসা প্যানেল + `/panel/print/*` PDF endpoints
- `routes/tenant-public.php` — `/`, `/about`, `/notices`, `/gallery`, `/teachers`, `/admission`, `/result`, `/contact`, `/sitemap.xml`

**Coexistence:** পাবলিক সাইট `/` এর মালিক, প্যানেল `/panel/*` এর। `tenant.php` **আগে** রেজিস্টার হবে, `tenant-public.php` (wildcard সহ) পরে — Laravel registration order-এ ম্যাচ করে।

> **⚠️ #১ stancl+Livewire বাগ:** `AppServiceProvider`-এ `Livewire::setUpdateRoute()` ও `setScriptRoute()`-এ `InitializeTenancyByDomain` যোগ করতেই হবে। না করলে প্রতিটি Livewire action tenant context ছাড়া চলবে → global scope ভেঙে যাবে বা নীরবে central ডেটা ফেরত দেবে। Livewire temporary upload route-ও tenant-aware disk-এ।

**Auth:** এক `users` টেবিল, এক `web` guard, কিন্তু custom UserProvider যা `retrieveByCredentials`-এ `tenant_id` scope যোগ করে। প্রতিটি authenticated request-এ assert `auth()->user()->tenant_id === tenant('id')`। **`session.domain = null`** (host-only cookie) — না হলে `madrasa-a.app.com` ও `madrasa-b.app.com` সেশন কুকি শেয়ার করবে।

---

## Landing Site Template System

তিন স্তর:

1. **Structure** — `resources/views/public/templates/{classic,modern,minimal,heritage}/`, প্রতিটি একই view contract মানে (`layout, home, page, notices, gallery, teachers, contact, admission, result`)।
2. **Theme** — `site_settings` row → layout head-এ CSS custom properties inject:
   ```blade
   <style>:root{--brand:{{ $site->primary_color }};--brand-2:{{ $site->secondary_color }}}</style>
   ```
   Tailwind config-এ `brand: 'rgb(var(--brand) / <alpha-value>)'` ⇒ **একটাই compiled CSS bundle সব tenant-এর সব রঙে কাজ করে** — per-tenant CSS build লাগে না। এটাই টেমপ্লেট সিস্টেম সস্তা রাখার মূল সিদ্ধান্ত। বাংলা ফন্ট (কালপুরুষ/সোলাইমানলিপি/হিন্দ সিলিগুড়ি) self-hosted, `<body>` class swap।
3. **Content** — `pages`, `sliders`, `notices`, `gallery`, `public_teacher_profiles`, `menu_items` + `site_settings` scalar fields। Rich text: Trix/TipTap, save-এ `mews/purifier` দিয়ে sanitize।

**Preview:** `/?_preview_template=modern&_preview_token=…` signed URL — সেভ না করেই দেখা যাবে।
**Cache:** `Cache::tags(["tenant:{$id}","site"])` দিয়ে rendered page cache, CMS model save-এ observer দিয়ে bust।
**Security:** `custom_css` অবশ্যই sanitize (`</style>`, `@import`, `expression(`, `javascript:` strip) — পাবলিক সাইট আর `/panel` একই origin, তাই XSS হলে অ্যাডমিন সেশন চুরি যাবে।

---

## Packages

| প্যাকেজ                                             | কারণ                                                                  |
| --------------------------------------------------- | --------------------------------------------------------------------- |
| `stancl/tenancy` ^3.8                               | Domain resolution, cache/filesystem/queue bootstrapper, impersonation |
| `livewire/livewire` ^3                              | UI                                                                    |
| `spatie/laravel-permission` ^6                      | teams mode = tenant-scoped roles                                      |
| `spatie/laravel-medialibrary` ^11                   | ছবি, গ্যালারি; custom `TenantPathGenerator` লাগবে                     |
| `spatie/laravel-activitylog` ^4                     | টাকার টেবিলে audit trail (কে রসিদ বাতিল করল)                          |
| `spatie/laravel-backup` ^9                          | রাত্রিকালীন ডাম্প                                                     |
| `mpdf/mpdf` ^8.2                                    | বাংলা যুক্তাক্ষর সঠিক (`useOTL => 0xFF`); volume documents            |
| `spatie/browsershot`                                | marksheet/ID card/sanad — headless Chrome, বাংলা+আরবি নিখুঁত          |
| ext-intl (`IntlDateFormatter`, islamic-umalqura)    | Hijri — আলাদা প্যাকেজ নয়, `App\Support\HijriDate` wrapper            |
| `maatwebsite/excel` ^3.1                            | ছাত্র bulk import, রিপোর্ট export                                     |
| `simplesoftwareio/simple-qrcode`                    | ID card QR, রসিদ verification                                         |
| `laravel/horizon` + Redis                           | billing run, PDF, SMS, backup queue                                   |
| `mews/purifier`                                     | CMS rich text sanitize                                                |
| `laravel/pint`, `larastan` (lvl 5+), `pestphp/pest` | Quality gates                                                         |

**বাদ:** `nwidart/laravel-modules`, `barryvdh/laravel-dompdf` (বাংলা ভাঙে), Cashier/Stripe (ম্যানুয়াল বিলিং), Jetstream/Breeze (tenant-scoped auth-এর সাথে সংঘর্ষ — auth Livewire component হাতে লেখা হবে), PowerGrid (নিজস্ব `Shared\DataTable` base class ~২০০ লাইন, বাংলা সংখ্যা ও export-এর পূর্ণ নিয়ন্ত্রণের জন্য)।

---

## Build Order

### Phase 0 — Foundation (সপ্তাহ ১–২)

1. Laravel 11 install; Pint/Larastan/Pest
2. stancl/tenancy — **`DatabaseTenancyBootstrapper` বাদ**, `central_domains`, bigint tenant key
3. Central migrations: `tenants`, `domains`, `plans`, `subscriptions`, `subscription_payments`
4. `BelongsToTenant` trait + `TenantScope` + **`TenantIsolationTest`** (সব tenant model লুপ করে cross-tenant leakage fail প্রমাণ করবে)
5. `users` (nullable tenant_id) + custom UserProvider + central/tenant login
6. spatie/permission teams + `PermissionRegistry` + `permissions:sync`
7. **Livewire update/script/upload route tenancy wiring — প্রথম দিনেই**
8. Tailwind + বাংলা ফন্ট + `Bn::num()` + `HijriDate` + `<x-bn-number>`, `<x-bn-date>`, `<x-taka>`
9. Layouts: central shell, tenant panel shell (বাংলা sidebar), public skeleton
10. `TenantProvisioner` — tenant → domain → roles/permissions/marhalas/kitabs/grades/fee_heads/khats/ledger_accounts/site_settings সিড → অ্যাডমিন ইউজার
11. **PDF spike:** বাস্তব marksheet mock দিয়ে mPDF বনাম Browsershot যাচাই (বাংলা যুক্তাক্ষর + `হেদায়া (الهداية)` মিশ্র লাইন)

### Phase 1 — MVP (সপ্তাহ ৩–১৪)

প্রতিটি ব্লক পরেরটিকে unblock করে:

| #   | ব্লক                                                                                                                                                                                       | নির্ভরতা |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | -------- |
| ১   | **সুপার অ্যাডমিন প্যানেল** — tenant CRUD + provisioning, plans, ম্যানুয়াল সাবস্ক্রিপশন + মেয়াদ, অফলাইন পেমেন্ট রেকর্ড, impersonation, expiry sweep job                                   | Phase 0  |
| ২   | **একাডেমিক সেটআপ** — sessions (+ topbar session switcher), marhala, jamaat, section, kitab, `jamaat_kitabs` কারিকুলাম বিল্ডার                                                              | ১        |
| ৩   | **People** — ছাত্র CRUD (ছবি, অভিভাবক), employees, `document_numbers` service, `student_uid`+`roll_no` generation, Excel bulk import                                                       | ২        |
| ৪   | **ভর্তি** — আবেদন ফর্ম (প্যানেল + পাবলিক), টেস্ট/অনুমোদন workflow, এক ক্লিকে enroll                                                                                                        | ৩        |
| ৫   | **আইডি কার্ড** — টেমপ্লেট, single + batch PDF, QR                                                                                                                                          | ৩        |
| ৬   | **হাজিরা** — ছাত্র দৈনিক (section-wise fast grid, keyboard-driven Livewire), শিক্ষক দৈনিক, ছুটির দিন, মাসিক রিপোর্ট                                                                        | ৩        |
| ৭   | **পরীক্ষা/ফলাফল** — exam_terms, schedule, grade_scales, নম্বর এন্ট্রি grid, `ResultCalculator`, ট্যাবুলেশন শিট, মার্কশিট PDF, publish workflow, পাবলিক ফলাফল খোঁজ                          | ২,৩      |
| ৮   | **ফি/হিসাব** — fee_heads, structures, overrides, `BillingRunner` (idempotent মাসিক বিল), আদায় স্ক্রিন + রসিদ PDF, বকেয়া রিপোর্ট, single-entry `transactions`, ক্যাশ বই, মাসিক সারসংক্ষেপ | ৩        |
| ৯   | **দান/যাকাত/লিল্লাহ** — খাত, ডোনার, আদায়কারী, রসিদ বই, দান এন্ট্রি, রসিদ PDF, ডোনার বার্ষিক স্টেটমেন্ট, খাত ও আদায়কারী-ভিত্তিক রিপোর্ট                                                   | ৮        |
| ১০  | **হিফজ/নাযেরা** — পারা/সূরা সিড, enrollment, দৈনিক সবক/সবকীনা/আমুখতা এন্ট্রি (দ্রুত ফর্ম), প্রগ্রেস বার, শিক্ষক-ভিত্তিক দৈনিক রিপোর্ট, milestone, মাসিক হিফজ রিপোর্ট PDF                   | ৩        |
| ১১  | **বোর্ডিং/খানা** — ভবন/রুম/সিট, allocation + খালি সিট ম্যাপ, meal plan + খাদ্য তালিকা, `KhanaBillRunner` → invoice lines, অভিভাবক সাক্ষাৎ লগ, ছুটি                                         | ৮        |
| ১২  | **ল্যান্ডিং সাইট** — site_settings + টেমপ্লেট পিকার, ৪টি Blade টেমপ্লেট, pages/sliders/notices/gallery/teachers/contact, পাবলিক ভর্তি ও ফলাফল পেজ, caching                                 | ৪,৭      |
| ১৩  | **ড্যাশবোর্ড ও রিপোর্ট** — মারহালা-ভিত্তিক ছাত্র সংখ্যা, আজকের হাজিরা %, মাসিক আদায়, বকেয়া, দান; printable report pack                                                                   | সব       |
| ১৪  | **Hardening** — row-level teacher policies, টাকার টেবিলে activity log, backup, `SessionPromoter` (বার্ষিক প্রমোশন উইজার্ড), `tenant:export`                                                | সব       |

### Phase 2 (লঞ্চ-পরবর্তী, ~২ মাস)

অভিভাবক পোর্টাল (মোবাইল+OTP), SMS integration (অনুপস্থিতি/ফলাফল/বকেয়া/দান), বেতন-পেরোল + শিক্ষক ছুটি workflow, সনদ (সনদ/প্রশংসাপত্র/ছাড়পত্র/চারিত্রিক), লাইব্রেরি + স্টোর (চাল-ডাল ক্রয়), custom domain self-service + স্বয়ংক্রিয় Let's Encrypt, `page_blocks`, ডোনার bulk SMS + pledge reminder।

### Phase 3

বোর্ড পরীক্ষা (বেফাক/হাইআতুল উলইয়া) রেজিস্ট্রেশন export + ফলাফল import, পেমেন্ট গেটওয়ে (bKash/SSLCommerz), মোবাইল অ্যাপ (Sanctum API — শিক্ষকের জন্য offline-first হাজিরা/হিফজ এন্ট্রি), double-entry ledger upgrade, advanced analytics, multi-branch activation, alumni, ওয়াকফ সম্পত্তি রেজিস্টার।

---

## Critical Risks & Gotchas

1. **রোল নম্বর** — roll ছাত্রের নয়, _enrollment_-এর বৈশিষ্ট্য। `UNIQUE(tenant_id, session_id, jamaat_id, roll_no)`। কখনো PHP-তে `MAX(roll)+1` নয় — `document_numbers` + `SELECT … FOR UPDATE`। কওমিতে প্রতি বছর মেধাক্রম অনুযায়ী roll পুনঃনির্ধারণ হয়, তাই `RollAssigner` তিন strategy সাপোর্ট করবে: `serial | merit | manual` + bulk "রোল পুনঃনির্ধারণ" স্ক্রিন। `student_uid` স্থায়ী পরিচয়, roll disposable।

2. **শিক্ষাবর্ষ সুইচিং** — current session container singleton-এ (`SetCurrentAcademicSession` middleware), শুধু `session()` নয়। বিপদ: ইউজার গত বছরে dropdown রেখে হাজিরা দিয়ে ফেলবে ⇒ non-current session দেখলে **জোরালো persistent banner** + write ব্লক। `is_locked` session-এ model observer লেভেলে write reject (শুধু UI নয়)। `SessionPromoter` উইজার্ড idempotent + preview-সহ।

3. **হিজরি তারিখ** — বাংলাদেশে চাঁদ দেখার উপর নির্ভর, Umm al-Qura থেকে ±১ দিন সরে। নিয়ম: **সব তারিখ Gregorian `date` কলামে সংরক্ষণ**, হিজরি শুধু render-time-এ; tenant-level `hijri_offset` (−২..+২) সেটিং। হিজরিতে কখনো date arithmetic নয়। বাংলা হিজরি মাসের নাম (মুহাররম, সফর, রবিউল আউয়াল…) হাতে লেখা array — ICU-র বাংলা transliteration ভুল দেখায়।

4. **বাংলা PDF** — সবচেয়ে বড় বিব্রতকর বাগের উৎস। dompdf যুক্তাক্ষর ভাঙে, ব্যবহার করা যাবে না। mPDF-এ TTF `fontdata`-তে `useOTL => 0xFF`, `autoScriptToLang`, `autoLangToFont`। মিশ্র বাংলা+আরবি লাইনে (`হেদায়া (الهداية)`) আরবি ফন্টও রেজিস্টার করতে হবে, নইলে বক্স দেখাবে। সংখ্যা PHP-তে `Bn::num()` দিয়ে convert, ফন্ট ফিচারের উপর নির্ভর নয়। ক্ষ, ঞ্চ, ষ্ট্র, র‍্যা টেস্ট করতে হবে। প্রতিটি PDF-এর Pest test + মার্কশিটের visual regression snapshot।

5. **Per-tenant file storage** — public assets (লোগো, স্লাইডার, গ্যালারি) → S3/Spaces, `tenants/{id}/` prefix, public URL। Private (ছাত্রের ছবি, ডকুমেন্ট, রসিদ) → local tenant disk, `tenant_asset()` দিয়ে policy check সহ stream। `storage/app/public/tenantX` symlink কাজ করে না। Livewire temp upload অবশ্যই tenant-aware disk-এ (নইলে tenant B-র ফাইল leak)। Medialibrary-তে `TenantPathGenerator`। Plan storage quota রাতে হিসাব, ৮০%-এ সতর্ক, ১০০%-এ ব্লক। **Tenant delete-এ storage prefix-ও delete — এবং সেটা টেস্ট করতে হবে।**

6. **Custom domain SSL** — **Caddy on-demand TLS** সুপারিশ। `ask` endpoint (`/api/internal/domain-allowed?domain=`) বাধ্যতামূলক — শুধু `domains` টেবিলে verified ডোমেইনে 200 ফেরত দেবে। না থাকলে যে কেউ DNS পয়েন্ট করে unlimited cert issuance ট্রিগার করবে ⇒ Let's Encrypt rate limit (৫০ cert/সপ্তাহ)। Wildcard `*.app.example.com` cert-এ DNS-01 challenge লাগে — DNS provider আগেই ঠিক করতে হবে। `VerifyCustomDomain` queued job DNS poll করবে।

7. **কিতাব/মারহালা সিড ডেটা** — **adoption-এর make-or-break**। কোনো মুহতামিম ২০০টি কিতাবের নাম টাইপ করবেন না। **বেফাকুল মাদারিসিল আরাবিয়া** কনভেনশন অনুসরণ করে ৬ মারহালা + সাধারণ জামাত + ~১৫০-২৫০ কিতাব (নূরানী কায়দা, আম্মাপারা → মিযানুস সরফ, নাহবেমীর → হেদায়া, নূরুল আনওয়ার → জালালাইন, মিশকাত → বুখারী, মুসলিম, তিরমিযী, আবু দাউদ, নাসাঈ, ইবনে মাজাহ, তাহাবী, শামায়েল)। মাস্টার `database/seeders/data/{marhalas,kitabs}.php`-এ git-versioned array; provisioning-এ tenant-scoped টেবিলে **কপি** হবে যাতে প্রতিটি মাদরাসা স্বাধীনভাবে edit করতে পারে। `code` মিলিয়ে merge করা "মাস্টার তালিকা আপডেট" action। **দুটি preset:** `kitab_madrasa` ও `hifz_madrasa`।

8. **অন্যান্য**
   - **Global scope leak** — `DB::table()` scope bypass করে। tenant টেবিলে raw `DB::table()` নিষিদ্ধ (Larastan rule/review); অপরিহার্য হলে ম্যানুয়ালি `->where('tenant_id', …)`।
   - **Queue-এ tenant context হারায়** — central console command থেকে dispatch করলে `tenancy()->runForMultiple(...)` দিয়ে wrap করতে হবে। Scheduled billing/expiry command explicit-ভাবে tenant iterate করবে।
   - **`unique` validation rule global scope মানে না** — `Rule::unique(...)->where('tenant_id', tenant('id'))`। সব জায়গায় লাগবে ⇒ একটি `TenantUnique` custom rule বানাতে হবে।
   - **মোবাইল নম্বর** কখনো globally unique নয় — ভাই-বোনরা একই নম্বর শেয়ার করে। `01[3-9]\d{8}` validate + normalize (+880 → 01)।
   - **টাকা** সর্বদা `decimal(12,2)` + `decimal:2` cast — float কখনো নয়।
   - **রসিদ বাতিল হয়, ডিলিট নয়** — soft-cancel + কারণ + কে করল + activity log। ডোনারের হাতে কাগজের কপি আছে।
   - **Timezone** `Asia/Dhaka`; হাজিরা/পরীক্ষার কলাম plain `date` (TZ conversion নয়) — কোনটা `date` কোনটা `datetime` স্পষ্ট রাখতে হবে।
   - **বাংলা সার্চ** — MySQL FULLTEXT ngram বাংলায় দুর্বল। normalized name + covering index + `LIKE`; আসলে স্টাফ `student_uid`/roll/মোবাইল দিয়েই খোঁজে — সেগুলো index করতে হবে। Meilisearch Phase 2।
   - **প্রিন্ট-কেন্দ্রিক সংস্কৃতি** — প্রতিটি লিস্টে print/PDF/Excel export। `DataTable` base class-এ export বিল্ট-ইন, পরে bolt-on নয়।
   - **`tenant:export {id}`** আগেই লিখতে হবে — single-DB-তে per-tenant restore মানে filtered dump; এটাই disaster recovery + churn offboarding, পরে যোগ করা অনেক কঠিন।

---

## Deliverables of this task

1. **`/Users/kawsarahmed/Desktop/Qawmi-madrasa/requirement.md`** — সম্পূর্ণ বাংলা স্পেসিফিকেশন: ভূমিকা, ব্যবহারকারী ভূমিকা, মডিউল-ভিত্তিক ফাংশনাল রিকোয়ারমেন্ট, ডাটাবেস স্কিমা, নন-ফাংশনাল রিকোয়ারমেন্ট (নিরাপত্তা, পারফরম্যান্স, ব্যাকআপ), ফেজ পরিকল্পনা।
2. **Phase 0 কোড** — Laravel scaffold, tenancy config, `BelongsToTenant`, central migrations, `TenantProvisioner`, `Bn`/`HijriDate` helpers, Livewire tenancy wiring, `TenantIsolationTest`, PDF spike।

---

## Verification

**Phase 0 শেষে অবশ্যই সবুজ হতে হবে:**

```bash
php artisan migrate:fresh --seed
php artisan test                      # Pest
./vendor/bin/pint --test
./vendor/bin/phpstan analyse           # larastan level 5
```

নির্দিষ্ট যাচাই:

- **Tenant isolation** — `TenantIsolationTest`: দুই tenant বানিয়ে প্রতিটি tenant model-এ A-এর ডেটা B-র context-এ **অদৃশ্য** প্রমাণ করা।
- **Livewire tenancy** — tenant ডোমেইনে একটি Livewire component render + action fire করে assert করা যে `tenant()` null নয় এবং সঠিক tenant।
- **Domain resolution** — `/etc/hosts`-এ `madrasa-a.localhost` ও `madrasa-b.localhost` ম্যাপ করে দুটি ভিন্ন ল্যান্ডিং পেজ লোড হওয়া যাচাই; central domain-এ `/panel` **404/redirect** হওয়া যাচাই।
- **Session cookie isolation** — tenant A-তে লগইন করে tenant B-র ডোমেইনে গিয়ে unauthenticated হওয়া যাচাই।
- **PDF spike** — বাস্তব মার্কশিট mock generate করে চোখে দেখে যাচাই: যুক্তাক্ষর (ক্ষ, ঞ্চ, ষ্ট্র, র‍্যা), বাংলা সংখ্যা, এবং `হেদায়া (الهداية)` মিশ্র লাইন।
- **Provisioning** — `php artisan tenant:create` দিয়ে নতুন tenant বানিয়ে যাচাই: ডোমেইন, রোল, মারহালা/কিতাব/গ্রেড/ফি-খাত সিড, অ্যাডমিন ইউজার, site_settings — সব তৈরি হয়েছে এবং সেই ইউজারে `/panel`-এ লগইন হয়।
