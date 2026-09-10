@php
    use App\Support\Bn;

    $phone = config('sales.phone');
    $whatsapp = 'https://wa.me/'.config('sales.whatsapp').'?text='.rawurlencode('আসসালামু আলাইকুম। মাদরাসা ম্যানেজমেন্ট সিস্টেমের ডেমো দেখতে চাই।');
    $email = config('sales.email');

    // মডিউল কার্ডগুলো — প্রতিটি বাস্তবে বিদ্যমান মডিউলের সাথে মেলে।
    $modules = [
        ['ছাত্র ও ভর্তি', 'ভর্তি ফরম থেকে ছাত্র প্রোফাইল, অভিভাবক, স্থায়ী ছাত্র-আইডি ও বছরভিত্তিক রোল — সব এক জায়গায়।', 'M12 4.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7zM4.5 19a7.5 7.5 0 0115 0'],
        ['শিক্ষা কার্যক্রম', 'মারহালা, জামাত, শাখা ও কিতাব — বেফাকের ১০ মারহালা ও ১৮৯ কিতাব আগে থেকেই দেওয়া, নিজের মতো সম্পাদনাও করা যায়।', 'M4 5.5A1.5 1.5 0 015.5 4H10a2 2 0 012 2 2 2 0 012-2h4.5A1.5 1.5 0 0120 5.5v11a1.5 1.5 0 01-1.5 1.5H14a2 2 0 00-2 2 2 2 0 00-2-2H5.5A1.5 1.5 0 014 16.5v-11zM12 6v14'],
        ['পরীক্ষা ও ফলাফল', 'নম্বর এন্ট্রি, গ্রেড, মেধাক্রম ও বাংলা মার্কশিট। নম্বর জামাত-কিতাবে বাঁধা, তাই পুরনো বছরের ফলাফল কখনো বদলায় না।', 'M9 12l2 2 4-4M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z'],
        ['ফি ও হিসাব', 'ফি স্ট্রাকচার, মাসিক বিল, আদায়, ছাড় ও বকেয়া — টাকার হিসাব দশমিক-নির্ভুল, রসিদ নম্বর স্বয়ংক্রিয়।', 'M12 6v12m3-9.5c0-1.4-1.3-2.5-3-2.5s-3 1.1-3 2.5 1.3 2.2 3 2.5 3 1.1 3 2.5-1.3 2.5-3 2.5-3-1.1-3-2.5'],
        ['দান ও যাকাত', 'লিল্লাহ, যাকাত ও খাতভিত্তিক দান, আদায়কারী ও রসিদ বই। রসিদ বাতিল হয়, মুছে যায় না — দাতার কাছে কাগজ থাকে।', 'M12 20s-7-4.4-7-9a4 4 0 017-2.6A4 4 0 0119 11c0 4.6-7 9-7 9z'],
        ['হিফজ ট্র্যাকিং', 'সবক, সবকীনা ও আমুখতার দৈনিক অগ্রগতি — কে কতদূর, কোথায় আটকে আছে এক নজরে।', 'M6 4h9l3 3v13a1 1 0 01-1 1H6a1 1 0 01-1-1V5a1 1 0 011-1zM8 11h8M8 15h5'],
        ['বোর্ডিং ও খানা', 'দারুল ইকামার ভবন, রুম, সিট বরাদ্দ, খানা বিল, ছুটি ও অভিভাবকের সাক্ষাৎ।', 'M4 20V9l8-5 8 5v11a1 1 0 01-1 1h-4v-6H9v6H5a1 1 0 01-1-1z'],
        ['পাবলিক ওয়েবসাইট', 'প্রতিটি মাদরাসার নিজস্ব ঠিকানায় ল্যান্ডিং সাইট — পরিচিতি, নোটিশ, শিক্ষকমণ্ডলী ও গ্যালারি।', 'M12 3a9 9 0 100 18 9 9 0 000-18zM3.5 9h17M3.5 15h17M12 3c2.5 2.6 3.8 5.7 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.7-3.8-9S9.5 5.6 12 3z'],
    ];

    $roles = [
        ['মুহতামিম', 'পুরো মাদরাসার চিত্র — ভর্তি, হাজিরা, আয়-ব্যয় ও ফলাফল এক ড্যাশবোর্ডে।'],
        ['শিক্ষা সচিব', 'জামাত, কিতাব, রুটিন, পরীক্ষা ও ফলাফল প্রকাশ।'],
        ['হিসাব রক্ষক', 'ফি আদায়, দান, রসিদ ও খরচের হিসাব।'],
        ['উস্তাদ', 'নিজের জামাতের হাজিরা, নম্বর ও হিফজ অগ্রগতি।'],
        ['নাযেমে দারুল ইকামা', 'বোর্ডিং, সিট, খানা বিল ও ছুটি।'],
        ['আদায়কারী', 'মাঠে দান সংগ্রহ ও রসিদ প্রদান।'],
    ];

    $faqs = [
        ['আমার মাদরাসার ডেটা কি অন্য মাদরাসা দেখতে পাবে?', 'না। প্রতিটি মাদরাসার ডেটা আলাদা টেন্যান্ট পরিচয়ে বাঁধা এবং প্রতিটি প্রশ্নে স্বয়ংক্রিয়ভাবে ছেঁকে দেওয়া হয়, তাই এক মাদরাসার প্যানেল থেকে অন্যের তথ্য পড়া যায় না।'],
        ['আমরা কি নিজস্ব ডোমেইন ব্যবহার করতে পারব?', 'পারবেন। ডিফল্টে একটি সাবডোমেইন দেওয়া হয়, তবে চাইলে নিজের ডোমেইন (যেমন madrasa.edu.bd) যুক্ত করা যায় — প্যানেল ও পাবলিক সাইট দুটোই সেখানে চলবে।'],
        ['বেফাকের কিতাব তালিকা কি আবার নতুন করে তুলতে হবে?', 'না। ১০ মারহালা, ১৮৯ কিতাব, গ্রেড, ফি খাত ও দানের খাত আগে থেকেই দেওয়া থাকে। প্রতিটি মাদরাসা নিজের কপি পায়, তাই নিজের মতো সম্পাদনা করলে অন্য কারো সিলেবাস বদলায় না।'],
        ['মার্কশিট ও রসিদ কি বাংলায় ছাপা যাবে?', 'যাবে। মার্কশিট, রসিদ ও ভর্তি ফরম বাংলা যুক্তাক্ষরসহ সঠিকভাবে PDF-এ ছাপা হয়, সংখ্যাও বাংলায়। আরবি ইবারতের জন্য আলাদা ফন্ট রয়েছে।'],
        ['হিজরি তারিখ কি দেখা যাবে?', 'যায়। তারিখ ইংরেজি সনে সংরক্ষিত হয় আর হিজরি দেখানোর সময় হিসাব হয়, প্রতিটি মাদরাসা নিজের চাঁদ দেখার সাথে মিলিয়ে ±১ দিন সমন্বয় করতে পারে।'],
        ['ইন্টারনেট ছাড়া চলবে?', 'সিস্টেমটি অনলাইনভিত্তিক, তাই ইন্টারনেট প্রয়োজন। তবে পাতাগুলো হালকা, তাই সাধারণ মোবাইল ইন্টারনেটেও ভালো চলে এবং সব রিপোর্ট PDF করে রাখা যায়।'],
    ];
@endphp

<x-layouts.marketing title="মাদরাসা ম্যানেজমেন্ট সিস্টেম">

    {{-- ═══════════ Header ═══════════ --}}
    <header class="sticky top-0 z-40 border-b border-gray-200/80 bg-white/85 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6">
            <a href="#" class="flex items-center gap-2.5 font-bold text-gray-900">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-white shadow-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20V9l8-5 8 5v11a1 1 0 01-1 1h-4v-6H9v6H5a1 1 0 01-1-1z" />
                    </svg>
                </span>
                <span class="text-[15px] leading-tight">মাদরাসা<br class="sm:hidden"><span class="sm:mr-1"></span>ম্যানেজমেন্ট</span>
            </a>

            <nav class="mr-auto hidden items-center gap-1 lg:flex">
                @foreach ([['#features', 'সুবিধাসমূহ'], ['#modules', 'মডিউল'], ['#pricing', 'প্যাকেজ'], ['#faq', 'জিজ্ঞাসা']] as [$href, $label])
                    <a href="{{ $href }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="mr-auto lg:mr-0"></div>

            <a href="{{ route('central.login') }}" class="hidden rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 sm:block">লগইন</a>
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
               class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                ডেমো দেখুন
            </a>
        </div>
    </header>

    {{-- ═══════════ ১. Hero ═══════════ --}}
    <section class="relative overflow-hidden">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute inset-0 bg-gradient-to-b from-brand-50/70 via-white to-white"></div>
            <div class="absolute -top-24 left-1/2 h-72 w-[46rem] -translate-x-1/2 rounded-full bg-brand-100/50 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-6xl px-4 pb-20 pt-16 sm:px-6 sm:pb-24 sm:pt-24">
            <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white px-3 py-1 text-xs font-semibold text-brand-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                        বাংলাদেশের কওমি মাদরাসার জন্য তৈরি
                    </span>

                    <h1 class="mt-5 text-4xl font-bold leading-[1.25] tracking-tight text-gray-900 sm:text-5xl sm:leading-[1.2]">
                        মাদরাসার সব হিসাব<br>
                        <span class="text-brand-700">এক জায়গায়, নির্ভুলভাবে</span>
                    </h1>

                    <p class="mt-5 max-w-xl text-lg leading-relaxed text-gray-600">
                        ভর্তি, হাজিরা, পরীক্ষা, ফি, দান, হিফজ ও বোর্ডিং — খাতার বদলে
                        একটি সিস্টেমে। পুরোপুরি বাংলায়, মার্কশিট ও রসিদ ছাপার সুবিধাসহ।
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-brand-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 004.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2zm5.8 14.16c-.24.68-1.2 1.26-1.96 1.42-.52.11-1.2.2-3.5-.75-2.94-1.22-4.83-4.2-4.98-4.4-.14-.19-1.19-1.58-1.19-3.02s.76-2.14 1.03-2.44c.27-.29.58-.37.78-.37h.56c.18 0 .42-.03.65.5.24.57.82 1.98.89 2.12.07.15.12.32.02.51-.09.19-.14.31-.28.48-.14.16-.3.36-.42.49-.14.14-.29.29-.12.57.16.29.73 1.2 1.56 1.95 1.07.95 1.98 1.25 2.26 1.39.28.14.44.12.6-.07.17-.19.7-.81.88-1.09.18-.29.36-.24.61-.14.25.09 1.6.75 1.87.89.28.14.46.21.53.32.07.12.07.67-.17 1.35z"/></svg>
                            ফ্রি ডেমো নিন
                        </a>
                        <a href="tel:{{ $phone }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3.5 text-base font-semibold text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-gray-50">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 5.5C4 4.7 4.7 4 5.5 4h2c.7 0 1.3.5 1.5 1.2l.7 3c.1.6-.1 1.2-.6 1.5l-1.4 1a12 12 0 005.6 5.6l1-1.4c.3-.5.9-.7 1.5-.6l3 .7c.7.2 1.2.8 1.2 1.5v2c0 .8-.7 1.5-1.5 1.5C10.6 20 4 13.4 4 5.5z"/></svg>
                            {{ Bn::num(config('sales.phone')) }}
                        </a>
                    </div>

                    <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-gray-200 pt-6">
                        @foreach ([['১০', 'মারহালা প্রস্তুত'], ['১৮৯', 'কিতাব তালিকাভুক্ত'], ['৯', 'মডিউল']] as [$num, $label])
                            <div>
                                <dt class="text-2xl font-bold text-gray-900">{{ $num }}</dt>
                                <dd class="mt-0.5 text-sm text-gray-500">{{ $label }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- ড্যাশবোর্ড ঝলক --}}
                <div class="relative">
                    <div class="rounded-2xl border border-gray-200 bg-white p-2 shadow-xl shadow-gray-900/5">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <div class="flex items-center gap-2 pb-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-green-400"></span>
                                <span class="mr-auto"></span>
                                <span class="rounded bg-white px-2 py-0.5 text-[11px] text-gray-400">madrasa.example.com</span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                @foreach ([
                                    ['মোট ছাত্র', '৪৮৬', 'bg-brand-100', 'bg-brand-500', '84%'],
                                    ['আজকের হাজিরা', '৯২%', 'bg-sky-100', 'bg-sky-500', '92%'],
                                    ['এ মাসের আদায়', '৳ ২,৪৫,০০০', 'bg-emerald-100', 'bg-emerald-500', '78%'],
                                    ['বকেয়া', '৳ ৬৮,৫০০', 'bg-amber-100', 'bg-amber-500', '32%'],
                                ] as [$label, $value, $track, $bar, $width])
                                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                                        <p class="text-[11px] text-gray-500">{{ $label }}</p>
                                        <p class="mt-1 text-lg font-bold text-gray-900">{{ $value }}</p>
                                        <div class="mt-2 h-1 rounded-full {{ $track }}">
                                            <div class="h-1 rounded-full {{ $bar }}" style="width: {{ $width }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3 rounded-lg border border-gray-200 bg-white p-3">
                                <p class="text-[11px] font-semibold text-gray-500">আজকের হিফজ অগ্রগতি</p>
                                <div class="mt-2 space-y-2">
                                    @foreach ([['হাফেজ আব্দুল্লাহ', 'সবক ১.৫ পৃষ্ঠা', '৯০%'], ['হাফেজ ইউসুফ', 'আমুখতা ৩ পারা', '৭৫%'], ['হাফেজ বিলাল', 'সবকীনা ৫ পৃষ্ঠা', '৬০%']] as [$name, $detail, $pct])
                                        <div class="flex items-center gap-3">
                                            <span class="h-6 w-6 shrink-0 rounded-full bg-brand-100"></span>
                                            <span class="w-28 shrink-0 truncate text-[11px] font-medium text-gray-700">{{ $name }}</span>
                                            <span class="hidden text-[11px] text-gray-400 sm:block">{{ $detail }}</span>
                                            <span class="mr-auto"></span>
                                            <div class="h-1.5 w-16 rounded-full bg-gray-100">
                                                <div class="h-1.5 rounded-full bg-brand-500" style="width: {{ $pct }}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════ ২. সমস্যা → সমাধান ═══════════ --}}
    <section id="features" class="border-y border-gray-200 bg-gray-50/70 py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">খাতা-কলমের ঝামেলা শেষ</h2>
                <p class="mt-4 text-lg text-gray-600">
                    প্রতিটি মাদরাসাতেই যে সমস্যাগুলো বারবার ফিরে আসে, সেগুলো ধরেই সিস্টেমটি সাজানো।
                </p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['হিসাবে গরমিল', 'রসিদ বইয়ের সাথে খাতার হিসাব মেলে না, বকেয়া কার কত মনে থাকে না।', 'প্রতিটি আদায় রসিদ নম্বরসহ বাঁধা, বকেয়া নিজে থেকেই হিসাব হয়। রসিদ বাতিল হয়, মুছে যায় না।'],
                    ['ফলাফল তৈরিতে দিন পার', 'নম্বর যোগ, গড়, মেধাক্রম — সব হাতে, আর একটি ভুল মানে পুরো তালিকা নতুন করে।', 'নম্বর বসালেই গ্রেড, মেধাক্রম ও মার্কশিট প্রস্তুত — বাংলায় ছাপার উপযোগী PDF।'],
                    ['পুরনো তথ্য খুঁজে পাওয়া যায় না', 'তিন বছর আগের ছাত্রের ফলাফল বা রসিদ বের করতে আলমারি হাতড়াতে হয়।', 'প্রতিটি বছর আলাদা করে সংরক্ষিত — ছাত্র-আইডি দিয়ে খুঁজলেই পুরো ইতিহাস সামনে।'],
                ] as [$title, $problem, $solution])
                    <div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900">{{ $title }}</h3>
                        <p class="mt-3 flex gap-2.5 text-sm leading-relaxed text-gray-500">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            <span>{{ $problem }}</span>
                        </p>
                        <p class="mt-3 flex gap-2.5 border-t border-gray-100 pt-3 text-sm leading-relaxed text-gray-700">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            <span>{{ $solution }}</span>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════ ৩. মডিউল ═══════════ --}}
    <section id="modules" class="py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">যা যা আছে</h2>
                <p class="mt-4 text-lg text-gray-600">মাদরাসার প্রতিটি বিভাগের জন্য আলাদা মডিউল, সবগুলো একসাথে কাজ করে।</p>
            </div>

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($modules as [$title, $desc, $path])
                    <div class="group rounded-2xl border border-gray-200 bg-white p-6 transition hover:border-brand-300 hover:shadow-lg hover:shadow-brand-900/5">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white">
                            <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}" /></svg>
                        </span>
                        <h3 class="mt-4 font-bold text-gray-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════ ৪. ভূমিকা অনুযায়ী ═══════════ --}}
    <section class="border-y border-gray-200 bg-gray-900 py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="grid gap-12 lg:grid-cols-[1fr_1.2fr] lg:items-center">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">যিনি যে দায়িত্বে,<br>তিনি ঠিক ততটুকুই দেখবেন</h2>
                    <p class="mt-5 text-lg leading-relaxed text-gray-400">
                        প্রতিটি ব্যবহারকারীর জন্য আলাদা ভূমিকা ও অনুমতি। উস্তাদ শুধু নিজের
                        জামাত দেখেন, হিসাব রক্ষক টাকার হিসাব — কারো কাজে কেউ হাত দিতে পারেন না।
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($roles as [$role, $scope])
                        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                            <h3 class="font-semibold text-white">{{ $role }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-gray-400">{{ $scope }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════ ৫. প্রাইসিং (DB থেকে) ═══════════ --}}
    <section id="pricing" class="py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">প্যাকেজ ও মূল্য</h2>
                <p class="mt-4 text-lg text-gray-600">
                    মাদরাসার আকার অনুযায়ী প্যাকেজ বেছে নিন। কোনো লুকানো খরচ নেই।
                </p>
            </div>

            <div class="mt-14 grid gap-6 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    @php $featured = $loop->index === 1; @endphp
                    <div @class([
                        'relative flex flex-col rounded-2xl border p-7',
                        'border-brand-600 bg-white shadow-xl shadow-brand-900/10 ring-1 ring-brand-600' => $featured,
                        'border-gray-200 bg-white shadow-sm' => ! $featured,
                    ])>
                        @if ($featured)
                            <span class="absolute -top-3 right-7 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white">জনপ্রিয়</span>
                        @endif

                        <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>

                        <p class="mt-4 flex items-baseline gap-1.5">
                            @if ((float) $plan->price === 0.0)
                                <span class="text-4xl font-bold tracking-tight text-gray-900">বিনামূল্যে</span>
                            @else
                                <span class="text-4xl font-bold tracking-tight text-gray-900">{{ Bn::taka($plan->price, 0) }}</span>
                                <span class="text-sm text-gray-500">/ {{ $plan->billing_cycle === 'monthly' ? 'মাস' : 'বছর' }}</span>
                            @endif
                        </p>

                        <ul class="mt-6 space-y-2.5 border-t border-gray-100 pt-6 text-sm">
                            <li class="flex items-start gap-2.5 text-gray-700">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                <span>{{ $plan->max_students ? Bn::num($plan->max_students).' জন ছাত্র পর্যন্ত' : 'ছাত্রসংখ্যায় কোনো সীমা নেই' }}</span>
                            </li>
                            <li class="flex items-start gap-2.5 text-gray-700">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                <span>{{ $plan->max_teachers ? Bn::num($plan->max_teachers).' জন শিক্ষক ও কর্মচারী' : 'শিক্ষকসংখ্যায় কোনো সীমা নেই' }}</span>
                            </li>
                            @if ($plan->max_storage_mb)
                                <li class="flex items-start gap-2.5 text-gray-700">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    <span>{{ Bn::num(intdiv($plan->max_storage_mb, 1024) ?: $plan->max_storage_mb) }} {{ $plan->max_storage_mb >= 1024 ? 'জিবি' : 'এমবি' }} ফাইল সংরক্ষণ</span>
                                </li>
                            @endif

                            @foreach ($plan->features ?? [] as $feature)
                                <li class="flex items-start gap-2.5 text-gray-700">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    <span>{{ $featureLabels[$feature] ?? $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                           @class([
                               'mt-8 block rounded-xl px-5 py-3 text-center text-sm font-semibold transition',
                               'bg-brand-600 text-white shadow-sm hover:bg-brand-700' => $featured,
                               'border border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50' => ! $featured,
                           ])>
                            {{ (float) $plan->price === 0.0 ? 'ট্রায়াল শুরু করুন' : 'এই প্যাকেজ নিন' }}
                        </a>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 text-center text-sm text-gray-500">
                প্রতিষ্ঠান বড় হলে বা বিশেষ প্রয়োজন থাকলে
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="font-semibold text-brand-700 underline underline-offset-2 hover:text-brand-800">আমাদের সাথে কথা বলুন</a>।
            </p>
        </div>
    </section>

    {{-- ═══════════ ৬. কীভাবে শুরু ═══════════ --}}
    <section class="border-y border-gray-200 bg-gray-50/70 py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">শুরু করতে তিন ধাপ</h2>
                <p class="mt-4 text-lg text-gray-600">সেটআপের ঝামেলা আমরাই সামলাই — আপনি শুধু ব্যবহার শুরু করেন।</p>
            </div>

            <ol class="mt-14 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['যোগাযোগ ও ডেমো', 'ফোন বা হোয়াটসঅ্যাপে জানান। আমরা মাদরাসার প্রয়োজন বুঝে পুরো সিস্টেম দেখিয়ে দিই।'],
                    ['মাদরাসা প্রস্তুত', 'আপনার নিজস্ব ঠিকানা, লোগো ও রঙসহ প্যানেল তৈরি হয়। বেফাকের মারহালা ও কিতাব আগে থেকেই বসানো থাকে।'],
                    ['তথ্য তুলে কাজ শুরু', 'ছাত্র ও শিক্ষকের তালিকা তোলার পর প্রথম দিন থেকেই হাজিরা, ফি ও ফলাফল চালু।'],
                ] as $i => [$title, $desc])
                    <li class="relative rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 text-base font-bold text-white">{{ Bn::num($i + 1) }}</span>
                        <h3 class="mt-4 font-bold text-gray-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $desc }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ═══════════ ৭. জিজ্ঞাসা ═══════════ --}}
    <section id="faq" class="py-20 sm:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">সচরাচর জিজ্ঞাসা</h2>

            <div class="mt-12 divide-y divide-gray-200 border-y border-gray-200">
                @foreach ($faqs as [$q, $a])
                    <details class="group py-5" @if ($loop->first) open @endif>
                        <summary class="flex cursor-pointer list-none items-start gap-4 font-semibold text-gray-900 marker:content-none">
                            <span class="flex-1">{{ $q }}</span>
                            <svg class="mt-1 h-5 w-5 shrink-0 text-gray-400 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <p class="mt-3 pl-0 pr-9 text-[15px] leading-relaxed text-gray-600">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════ ৮. শেষ CTA ═══════════ --}}
    <section class="bg-brand-700 py-20 sm:py-24">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">আপনার মাদরাসার জন্য দেখে নিন</h2>
            <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-brand-100">
                কোনো খরচ ছাড়াই পুরো সিস্টেম দেখুন। পছন্দ হলে তবেই সিদ্ধান্ত নিন।
            </p>
            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                   class="rounded-xl bg-white px-7 py-3.5 text-base font-semibold text-brand-800 shadow-sm transition hover:bg-brand-50">
                    হোয়াটসঅ্যাপে বার্তা দিন
                </a>
                <a href="tel:{{ $phone }}"
                   class="rounded-xl border border-white/30 px-7 py-3.5 text-base font-semibold text-white transition hover:bg-white/10">
                    {{ Bn::num(config('sales.phone')) }}
                </a>
            </div>
        </div>
    </section>

    {{-- ═══════════ Footer ═══════════ --}}
    <footer class="border-t border-gray-200 bg-white py-12">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 sm:px-6 md:flex-row md:items-center">
            <div class="md:mr-auto">
                <p class="font-bold text-gray-900">মাদরাসা ম্যানেজমেন্ট সিস্টেম</p>
                <p class="mt-1 text-sm text-gray-500">বাংলাদেশের কওমি মাদরাসাগুলোর জন্য তৈরি</p>
            </div>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <a href="mailto:{{ $email }}" class="text-gray-600 transition hover:text-gray-900">{{ $email }}</a>
                <a href="tel:{{ $phone }}" class="text-gray-600 transition hover:text-gray-900">{{ Bn::num(config('sales.phone')) }}</a>
                <a href="{{ route('central.login') }}" class="text-gray-600 transition hover:text-gray-900">লগইন</a>
            </div>
        </div>
        <p class="mx-auto mt-8 max-w-6xl px-4 text-sm text-gray-400 sm:px-6">
            © {{ Bn::num(now()->year) }} — সর্বস্বত্ব সংরক্ষিত।
        </p>
    </footer>

</x-layouts.marketing>
