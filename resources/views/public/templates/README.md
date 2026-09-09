# পাবলিক সাইটের টেমপ্লেট

নতুন টেমপ্লেট বানাতে **PHP লিখতে হয় না** — শুধু Blade ফাইল।

```bash
cp -r classic modern
# তারপর modern/-এর ভেতরের 'public.templates.classic.' গুলো
# 'public.templates.modern.' করে দিন, আর ক্লাস বদলান।
```

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
│   └── contact.blade.php
└── partials/               ঐ টেমপ্লেটের নিজস্ব টুকরো
```

কোনো পেজ না থাকলে classic-এর ঐ পেজটি দেখানো হয় — সাইট ভাঙে না।

## প্রতিটি পেজ যে ভেরিয়েবল পায়

সব পেজে: `$settings`, `$menu`, `$designationLabels`

| পেজ | অতিরিক্ত |
|---|---|
| home | `$stats`, `$notices`, `$teachers`, `$departments` |
| about | `$departments` |
| notices | `$notices` (paginated), `$categories`, `$activeCategory` |
| notice | `$notice`, `$related` |
| teachers | `$teachers` |
| contact | — |

## রঙ

`--site-brand` ও `--site-accent` CSS ভেরিয়েবল প্রতি মাদরাসার সেটিংস থেকে
আসে। হার্ডকোড না করে `bg-[var(--site-brand)]` ব্যবহার করুন, তাহলে এক
টেমপ্লেটই সব মাদরাসার রঙে কাজ করবে।

## শেয়ার করা কম্পোনেন্ট

`<x-site.heading>`, `<x-site.notice-row>`, `<x-site.teacher-card>`,
`<x-site.page-header>`, `<x-site.empty>` — সব টেমপ্লেটে ব্যবহার করা যায়,
অথবা নিজের বানিয়ে নিন।
