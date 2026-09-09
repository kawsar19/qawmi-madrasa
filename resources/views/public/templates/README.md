# পাবলিক সাইটের টেমপ্লেট

নতুন টেমপ্লেট বানাতে **PHP লিখতে হয় না** — শুধু Blade ফাইল।

```bash
cp -r classic heritage
# তারপর heritage/-এর ভেতরের 'public.templates.classic.' গুলো
# 'public.templates.heritage.' করে দিন, আর ক্লাস বদলান।
```

এখন দুটি টেমপ্লেট আছে — `classic` (গম্ভীর, গাঢ় হেডার) ও `modern`
(পরিষ্কার, সাদা হেডার)। যেটির চেহারা কাছাকাছি সেটি কপি করলে কাজ কম।

`SiteTemplate::installed()` ফোল্ডার খুঁজে নেয়, তাই কপি করলেই
প্যানেলের ড্রপডাউনে চলে আসে — রুট, কন্ট্রোলার বা মডেল অপরিবর্তিত।

## প্রতিটি টেমপ্লেটে যা থাকতে হয়

```
<নাম>/
├── layout.blade.php        হেডার + ফুটার (একবার, সব পেজে)
├── pages/
│   ├── home.blade.php      ← এটি না থাকলে টেমপ্লেট ধরা পড়ে না
│   ├── about.blade.php
│   ├── notices.blade.php
│   ├── notice.blade.php
│   ├── teachers.blade.php
│   ├── gallery.blade.php
│   └── contact.blade.php
└── partials/               ঐ টেমপ্লেটের নিজস্ব টুকরো
```

কোনো পেজ না থাকলে classic-এর ঐ পেজটি দেখানো হয় — সাইট ভাঙে না।

## প্রতিটি পেজ যে ভেরিয়েবল পায়

সব পেজে: `$settings`, `$menu`, `$designationLabels`

| পেজ | অতিরিক্ত |
|---|---|
| home | `$stats`, `$slides`, `$notices`, `$teachers`, `$departments`, `$gallery` |
| about | `$departments` |
| notices | `$notices` (paginated), `$categories`, `$activeCategory` |
| notice | `$notice`, `$related` |
| teachers | `$teachers` |
| gallery | `$gallery` |
| contact | — |

## স্লাইডার ও গ্যালারি

`$slides` ও `$gallery` — দুটোই `SiteImage` মডেলের সংগ্রহ। প্রতিটিতে
`->url()` (ছবির ঠিকানা), `title`, `subtitle`, `link_label`, `link_url`,
`->hasLink()`।

হোমপেজে নিয়ম একটাই: **স্লাইড থাকলে স্লাইডার, না থাকলে স্থির হিরো** —
যে মাদরাসা এখনো ছবি দেয়নি তার সাইট যেন ফাঁকা না দেখায়।

```blade
@if ($slides->isNotEmpty())
    @include('public.templates.<নাম>.partials.slider')
@else
    @include('public.templates.<নাম>.partials.hero')
@endif
```

স্লাইডারে **JavaScript নেই** — CSS `snap-x snap-mandatory` আর নিচের
`#slide-{id}` anchor লিংক। মোবাইলে আঙুলে swipe, ডেস্কটপে নম্বরে ক্লিক।
নতুন টেমপ্লেটে classic বা modern-এর `partials/slider.blade.php` কপি করে
শুধু ক্লাস বদলালেই চলে।

## ছবি

আপলোড করা ছবির ঠিকানা সবসময় **`media($path)`** দিয়ে — `asset('storage/…')`
নয়। tenancy প্রতি মাদরাসার ফাইল আলাদা ফোল্ডারে রাখে, তাই `asset()` ভুল
জায়গায় খোঁজে আর ছবি ৪০৪ দেয়। `media()` লোকাল ডিস্ক ও R2 — দুটোতেই ঠিক
URL বানায়।

```blade
<img src="{{ media($settings->logo_path) }}" alt="">
<img src="{{ $slide->url() }}" alt="">   {{-- SiteImage-এর নিজস্ব --}}
```

## রঙ

`--site-brand` ও `--site-accent` CSS ভেরিয়েবল প্রতি মাদরাসার সেটিংস থেকে
আসে। হার্ডকোড না করে `bg-[var(--site-brand)]` ব্যবহার করুন, তাহলে এক
টেমপ্লেটই সব মাদরাসার রঙে কাজ করবে।

## শেয়ার করা কম্পোনেন্ট

`<x-site.heading>`, `<x-site.notice-row>`, `<x-site.teacher-card>`,
`<x-site.page-header>`, `<x-site.empty>` — সব টেমপ্লেটে ব্যবহার করা যায়,
অথবা নিজের বানিয়ে নিন।


## সতর্কতা

**Tailwind ক্লাস যাচাই করুন।** নতুন টেমপ্লেটের ক্লাসগুলো CSS-এ ঢুকেছে কিনা
দেখতে `npm run build` চালান। `npm run dev` চালু থাকলে Vite নতুন ফোল্ডার
সবসময় স্ক্যান করে না — তখন `public/hot` মুছে বিল্ড করা CSS ব্যবহার করুন।

**`aspect-4/3` কাজ করে না** — Tailwind v4-এ `aspect-[4/3]` লিখতে হয়।

**`col-span-*` এড়িয়ে যান** যেখানে সম্ভব; `grid-cols-[3fr_2fr]` বেশি
নির্ভরযোগ্য, কারণ span ক্লাস JIT স্ক্যানে বাদ পড়তে পারে।
