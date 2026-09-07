<?php

declare(strict_types=1);

/**
 * বেফাক কনভেনশন অনুসারে মাস্টার কিতাব তালিকা।
 *
 * Master reference data, git-versioned. Copied INTO each tenant on
 * provisioning so every madrasa can edit its own copy.
 *
 * marhala_code must match a code in marhalas.php.
 * category: quran|hadith|fiqh|aqidah|arabic_grammar|arabic_literature|tafsir|usul|history|logic|general
 */
return [
    // ==========================================================
    // ইবতেদাইয়্যাহ (প্রাথমিক স্তর) — ৫ বছর
    // ==========================================================
    ['code' => 'nurani_qaida', 'name' => 'নূরানী কায়দা', 'name_ar' => 'القاعدة النورانية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'quran', 'sort_order' => 10],
    ['code' => 'baghdadi_qaida', 'name' => 'বাগদাদী কায়দা', 'name_ar' => 'القاعدة البغدادية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'quran', 'sort_order' => 20],
    ['code' => 'amma_para', 'name' => 'আম্মাপারা', 'name_ar' => 'جزء عمّ', 'marhala_code' => 'ibtedaiyyah', 'category' => 'quran', 'sort_order' => 30],
    ['code' => 'nazera_quran_ibt', 'name' => 'নাযেরা কুরআন মাজীদ', 'name_ar' => 'الناظرة للقرآن المجيد', 'marhala_code' => 'ibtedaiyyah', 'category' => 'quran', 'sort_order' => 40],
    ['code' => 'tajweed_ibt', 'name' => 'তাজবীদ (প্রাথমিক)', 'name_ar' => 'التجويد المبتدئ', 'marhala_code' => 'ibtedaiyyah', 'category' => 'quran', 'sort_order' => 50],
    ['code' => 'masnun_dua', 'name' => 'মাসনূন দুআ ও কালিমা', 'name_ar' => 'الأدعية المسنونة', 'marhala_code' => 'ibtedaiyyah', 'category' => 'aqidah', 'sort_order' => 60],
    ['code' => 'islami_aqaid_ibt', 'name' => 'ইসলামী আকাইদ', 'name_ar' => 'العقائد الإسلامية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'aqidah', 'sort_order' => 70],
    ['code' => 'talimul_islam_1', 'name' => 'তালীমুল ইসলাম (১ম-২য় খণ্ড)', 'name_ar' => 'تعليم الإسلام', 'marhala_code' => 'ibtedaiyyah', 'category' => 'fiqh', 'sort_order' => 80],
    ['code' => 'ilmul_fiqh_ibt', 'name' => 'ইলমুল ফিকহ (প্রাথমিক)', 'name_ar' => 'علم الفقه للمبتدئين', 'marhala_code' => 'ibtedaiyyah', 'category' => 'fiqh', 'sort_order' => 90],
    ['code' => 'urdu_qaida', 'name' => 'উর্দু কায়দা', 'name_ar' => 'القاعدة الأردية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 100],
    ['code' => 'urdu_ki_pehli_kitab', 'name' => 'উর্দু কী পহেলী কিতাব', 'name_ar' => 'أردو كي پہلي كتاب', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 110],
    ['code' => 'arabi_qaida', 'name' => 'আরবী কায়দা', 'name_ar' => 'القاعدة العربية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'arabic_grammar', 'sort_order' => 120],
    ['code' => 'arabi_ki_pehli_kitab', 'name' => 'আল-আরাবিয়্যাতুল আসরিয়্যাহ (১ম)', 'name_ar' => 'العربية العصرية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'arabic_literature', 'sort_order' => 130],
    ['code' => 'bangla_ibt', 'name' => 'বাংলা', 'name_ar' => 'اللغة البنغالية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 140],
    ['code' => 'bangla_byakaran_ibt', 'name' => 'বাংলা ব্যাকরণ', 'name_ar' => 'قواعد اللغة البنغالية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 150],
    ['code' => 'onko_ibt', 'name' => 'অংক (গণিত)', 'name_ar' => 'الرياضيات', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 160],
    ['code' => 'english_ibt', 'name' => 'ইংরেজি', 'name_ar' => 'اللغة الإنجليزية', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 170],
    ['code' => 'samaj_bigyan_ibt', 'name' => 'সমাজ ও পরিবেশ পরিচিতি', 'name_ar' => 'المجتمع والبيئة', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 180],
    ['code' => 'bangladesh_porichiti', 'name' => 'বাংলাদেশ পরিচিতি', 'name_ar' => 'التعريف ببنغلاديش', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 190],
    ['code' => 'islamer_itihas_ibt', 'name' => 'ইসলামের ইতিহাস (প্রাথমিক)', 'name_ar' => 'تاريخ الإسلام للمبتدئين', 'marhala_code' => 'ibtedaiyyah', 'category' => 'history', 'sort_order' => 200],
    ['code' => 'seerat_khatimul_ambia', 'name' => 'সীরাতে খাতামুল আম্বিয়া', 'name_ar' => 'سيرة خاتم الأنبياء', 'marhala_code' => 'ibtedaiyyah', 'category' => 'history', 'sort_order' => 210],
    ['code' => 'akhlaq_adab_ibt', 'name' => 'আখলাক ও আদাব', 'name_ar' => 'الأخلاق والآداب', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 220],
    ['code' => 'khushkhati_ibt', 'name' => 'খুশখতী (হস্তলিপি)', 'name_ar' => 'الخط الحسن', 'marhala_code' => 'ibtedaiyyah', 'category' => 'general', 'sort_order' => 230],

    // ==========================================================
    // মুতাওয়াসসিতাহ (মাধ্যমিক স্তর) — ৩ বছর
    // ==========================================================
    ['code' => 'mizanus_sarf', 'name' => 'মীযানুস সরফ', 'name_ar' => 'ميزان الصرف', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_grammar', 'sort_order' => 10],
    ['code' => 'munshaib', 'name' => 'মুনশাইব', 'name_ar' => 'منشعب', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_grammar', 'sort_order' => 20],
    ['code' => 'ilmus_sigah', 'name' => 'ইলমুস সীগাহ', 'name_ar' => 'علم الصيغة', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_grammar', 'sort_order' => 30],
    ['code' => 'panj_ganj', 'name' => 'পাঞ্জেগঞ্জ', 'name_ar' => 'پنج گنج', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_grammar', 'sort_order' => 40],
    ['code' => 'nahbemir', 'name' => 'নাহবেমীর', 'name_ar' => 'نحو مير', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_grammar', 'sort_order' => 50],
    ['code' => 'sarf_bahai', 'name' => 'সরফে বাহাঈ', 'name_ar' => 'الصرف البهائي', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_grammar', 'sort_order' => 60],
    ['code' => 'tasrif_izzi', 'name' => 'তাসরীফুল ইজ্জী', 'name_ar' => 'التصريف العزي', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_grammar', 'sort_order' => 70],
    ['code' => 'talimul_islam_3', 'name' => 'তালীমুল ইসলাম (৩য়-৪র্থ খণ্ড)', 'name_ar' => 'تعليم الإسلام', 'marhala_code' => 'mutawassitah', 'category' => 'fiqh', 'sort_order' => 80],
    ['code' => 'zadut_talibin', 'name' => 'যাদুত তালিবীন', 'name_ar' => 'زاد الطالبين', 'marhala_code' => 'mutawassitah', 'category' => 'fiqh', 'sort_order' => 90],
    ['code' => 'safwatul_masadir', 'name' => 'সাফওয়াতুল মাসাদির', 'name_ar' => 'صفوة المصادر', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_grammar', 'sort_order' => 100],
    ['code' => 'arabic_first_book', 'name' => 'আল-কিরাআতুর রাশিদাহ (১ম)', 'name_ar' => 'القراءة الراشدة', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_literature', 'sort_order' => 110],
    ['code' => 'esho_arabi_shikhi', 'name' => 'এসো আরবী শিখি', 'name_ar' => 'تعال نتعلم العربية', 'marhala_code' => 'mutawassitah', 'category' => 'arabic_literature', 'sort_order' => 120],
    ['code' => 'urdu_dusri_kitab', 'name' => 'উর্দু দূসরী কিতাব', 'name_ar' => 'أردو دوسري كتاب', 'marhala_code' => 'mutawassitah', 'category' => 'general', 'sort_order' => 130],
    ['code' => 'urdu_ki_tisri_kitab', 'name' => 'উর্দু তীসরী কিতাব', 'name_ar' => 'أردو تيسري كتاب', 'marhala_code' => 'mutawassitah', 'category' => 'general', 'sort_order' => 140],
    ['code' => 'aqaid_mutawassitah', 'name' => 'আকাইদ ও ঈমানিয়াত', 'name_ar' => 'العقائد والإيمانيات', 'marhala_code' => 'mutawassitah', 'category' => 'aqidah', 'sort_order' => 150],
    ['code' => 'khasaisul_islam', 'name' => 'খাসাইসুল ইসলাম', 'name_ar' => 'خصائص الإسلام', 'marhala_code' => 'mutawassitah', 'category' => 'aqidah', 'sort_order' => 160],
    ['code' => 'tajweed_mutawassitah', 'name' => 'তাজবীদ ও নাযেরা', 'name_ar' => 'التجويد والناظرة', 'marhala_code' => 'mutawassitah', 'category' => 'quran', 'sort_order' => 170],
    ['code' => 'islamer_itihas_mut', 'name' => 'ইসলামের ইতিহাস (মাধ্যমিক)', 'name_ar' => 'تاريخ الإسلام', 'marhala_code' => 'mutawassitah', 'category' => 'history', 'sort_order' => 180],
    ['code' => 'seerat_sahaba', 'name' => 'সীরাতে সাহাবা', 'name_ar' => 'سير الصحابة', 'marhala_code' => 'mutawassitah', 'category' => 'history', 'sort_order' => 190],
    ['code' => 'bangla_mut', 'name' => 'বাংলা ও রচনা', 'name_ar' => 'اللغة البنغالية والإنشاء', 'marhala_code' => 'mutawassitah', 'category' => 'general', 'sort_order' => 200],
    ['code' => 'onko_mut', 'name' => 'অংক (গণিত)', 'name_ar' => 'الرياضيات', 'marhala_code' => 'mutawassitah', 'category' => 'general', 'sort_order' => 210],
    ['code' => 'english_mut', 'name' => 'ইংরেজি', 'name_ar' => 'اللغة الإنجليزية', 'marhala_code' => 'mutawassitah', 'category' => 'general', 'sort_order' => 220],
    ['code' => 'bhugol_mut', 'name' => 'ভূগোল ও বিজ্ঞান', 'name_ar' => 'الجغرافيا والعلوم', 'marhala_code' => 'mutawassitah', 'category' => 'general', 'sort_order' => 230],

    // ==========================================================
    // সানাবিয়্যাহ আম্মাহ — ২ বছর
    // ==========================================================
    ['code' => 'hidayatun_nahw', 'name' => 'হেদায়াতুন নাহু', 'name_ar' => 'هداية النحو', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'arabic_grammar', 'sort_order' => 10],
    ['code' => 'sharhe_miatu_amil', 'name' => 'শরহে মিআতে আমেল', 'name_ar' => 'شرح مائة عامل', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'arabic_grammar', 'sort_order' => 20],
    ['code' => 'kafiya', 'name' => 'কাফিয়া', 'name_ar' => 'الكافية', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'arabic_grammar', 'sort_order' => 30],
    ['code' => 'sharhe_tahzib', 'name' => 'শরহে তাহযীব', 'name_ar' => 'شرح التهذيب', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'logic', 'sort_order' => 40],
    ['code' => 'mirqat_mantiq', 'name' => 'মিরকাত (ইলমুল মানতিক)', 'name_ar' => 'مرقاة المنطق', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'logic', 'sort_order' => 50],
    ['code' => 'nurul_izah', 'name' => 'নূরুল ঈযাহ', 'name_ar' => 'نور الإيضاح', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'fiqh', 'sort_order' => 60],
    ['code' => 'quduri', 'name' => 'আল-কুদূরী', 'name_ar' => 'مختصر القدوري', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'fiqh', 'sort_order' => 70],
    ['code' => 'ilmus_seegah_sanabiyyah', 'name' => 'ফুসূলে আকবারী', 'name_ar' => 'فصول أكبري', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'arabic_grammar', 'sort_order' => 80],
    ['code' => 'nafhatul_arab', 'name' => 'নাফহাতুল আরব', 'name_ar' => 'نفحة العرب', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'arabic_literature', 'sort_order' => 90],
    ['code' => 'qasasun_nabiyyin', 'name' => 'কাসাসুন নাবিয়্যীন', 'name_ar' => 'قصص النبيين', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'arabic_literature', 'sort_order' => 100],
    ['code' => 'al_qiraatur_rashidah', 'name' => 'আল-কিরাআতুর রাশিদাহ (২য়-৩য়)', 'name_ar' => 'القراءة الراشدة', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'arabic_literature', 'sort_order' => 110],
    ['code' => 'tarjamatul_quran_ama', 'name' => 'তরজমাতুল কুরআন (আংশিক)', 'name_ar' => 'ترجمة القرآن', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'tafsir', 'sort_order' => 120],
    ['code' => 'zadut_talibin_sani', 'name' => 'যাদুত তালিবীন (২য় খণ্ড)', 'name_ar' => 'زاد الطالبين', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'hadith', 'sort_order' => 130],
    ['code' => 'arbaeen_nawawi', 'name' => 'আরবাঈনে নববী', 'name_ar' => 'الأربعون النووية', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'hadith', 'sort_order' => 140],
    ['code' => 'aqidatut_tahawi_ama', 'name' => 'আকীদাতুত তাহাবী', 'name_ar' => 'العقيدة الطحاوية', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'aqidah', 'sort_order' => 150],
    ['code' => 'urdu_chauthi_kitab', 'name' => 'উর্দু চৌথী কিতাব', 'name_ar' => 'أردو چوتهي كتاب', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'general', 'sort_order' => 160],
    ['code' => 'tarikhul_islam_ama', 'name' => 'তারীখুল ইসলাম', 'name_ar' => 'تاريخ الإسلام', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'history', 'sort_order' => 170],
    ['code' => 'khulafaye_rashedin', 'name' => 'খুলাফায়ে রাশেদীন', 'name_ar' => 'الخلفاء الراشدون', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'history', 'sort_order' => 180],
    ['code' => 'tajweed_jazari', 'name' => 'তাজবীদ (মুকাদ্দিমাতুল জাযারী)', 'name_ar' => 'المقدمة الجزرية', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'quran', 'sort_order' => 190],
    ['code' => 'bangla_sanabiyyah_ama', 'name' => 'বাংলা রচনা ও সাহিত্য', 'name_ar' => 'الأدب البنغالي', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'general', 'sort_order' => 200],
    ['code' => 'english_sanabiyyah_ama', 'name' => 'ইংরেজি', 'name_ar' => 'اللغة الإنجليزية', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'general', 'sort_order' => 210],
    ['code' => 'onko_sanabiyyah_ama', 'name' => 'অংক ও সাধারণ জ্ঞান', 'name_ar' => 'الرياضيات والمعلومات العامة', 'marhala_code' => 'sanabiyyah_ama', 'category' => 'general', 'sort_order' => 220],

    // ==========================================================
    // সানাবিয়্যাহ উলইয়া — ২ বছর
    // ==========================================================
    ['code' => 'sharhe_bekaya', 'name' => 'শরহে বেকায়া', 'name_ar' => 'شرح الوقاية', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'fiqh', 'sort_order' => 10],
    ['code' => 'usulush_shashi', 'name' => 'উসূলুশ শাশী', 'name_ar' => 'أصول الشاشي', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'usul', 'sort_order' => 20],
    ['code' => 'kanzud_daqaiq', 'name' => 'কানযুদ দাকাইক', 'name_ar' => 'كنز الدقائق', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'fiqh', 'sort_order' => 30],
    ['code' => 'durusul_balagah', 'name' => 'দুরূসুল বালাগাত', 'name_ar' => 'دروس البلاغة', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'arabic_literature', 'sort_order' => 40],
    ['code' => 'mukhtasarul_maani', 'name' => 'মুখতাসারুল মাআনী', 'name_ar' => 'مختصر المعاني', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'arabic_literature', 'sort_order' => 50],
    ['code' => 'duruse_balaghat_talkhis', 'name' => 'তালখীসুল মিফতাহ', 'name_ar' => 'تلخيص المفتاح', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'arabic_literature', 'sort_order' => 60],
    ['code' => 'sharhe_jami', 'name' => 'শরহে জামী', 'name_ar' => 'شرح الجامي', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'arabic_grammar', 'sort_order' => 70],
    ['code' => 'qutbi', 'name' => 'কুতবী (শরহে শামসিয়া)', 'name_ar' => 'قطبي شرح الشمسية', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'logic', 'sort_order' => 80],
    ['code' => 'sullamul_ulum', 'name' => 'সুল্লামুল উলূম', 'name_ar' => 'سلم العلوم', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'logic', 'sort_order' => 90],
    ['code' => 'mishkat_sharif_ulya', 'name' => 'মিশকাত (আংশিক)', 'name_ar' => 'مشكاة المصابيح', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'hadith', 'sort_order' => 100],
    ['code' => 'riyadus_salihin', 'name' => 'রিয়াদুস সালিহীন', 'name_ar' => 'رياض الصالحين', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'hadith', 'sort_order' => 110],
    ['code' => 'nukhbatul_fikar', 'name' => 'নুখবাতুল ফিকার', 'name_ar' => 'نخبة الفكر', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'hadith', 'sort_order' => 120],
    ['code' => 'tarjamatul_quran_ulya', 'name' => 'তরজমাতুল কুরআন (পূর্ণাঙ্গ)', 'name_ar' => 'ترجمة القرآن الكريم', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'tafsir', 'sort_order' => 130],
    ['code' => 'usulut_tafsir', 'name' => 'উসূলুত তাফসীর', 'name_ar' => 'أصول التفسير', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'usul', 'sort_order' => 140],
    ['code' => 'sharhul_aqaid', 'name' => 'শরহুল আকাইদ', 'name_ar' => 'شرح العقائد النسفية', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'aqidah', 'sort_order' => 150],
    ['code' => 'maqamat_hariri', 'name' => 'মাকামাতে হারীরী', 'name_ar' => 'مقامات الحريري', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'arabic_literature', 'sort_order' => 160],
    ['code' => 'diwanul_mutanabbi', 'name' => 'দীওয়ানুল মুতানাব্বী', 'name_ar' => 'ديوان المتنبي', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'arabic_literature', 'sort_order' => 170],
    ['code' => 'sabatul_muzahhab', 'name' => 'সাব্আতু মুআল্লাকাত', 'name_ar' => 'السبع المعلقات', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'arabic_literature', 'sort_order' => 180],
    ['code' => 'faraid_sirajiyyah', 'name' => 'আস-সিরাজী ফিল ফারাইয', 'name_ar' => 'السراجي في الفرائض', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'fiqh', 'sort_order' => 190],
    ['code' => 'tarikhut_tabari_ulya', 'name' => 'তারীখে ইসলাম (উমাইয়া ও আব্বাসী)', 'name_ar' => 'تاريخ الدولة الأموية والعباسية', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'history', 'sort_order' => 200],
    ['code' => 'ilmul_faraid_ulya', 'name' => 'ইলমুল ফারাইয ও হিসাব', 'name_ar' => 'علم الفرائض والحساب', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'fiqh', 'sort_order' => 210],
    ['code' => 'urdu_adab_ulya', 'name' => 'উর্দু আদব ও তরজমা', 'name_ar' => 'الأدب الأردي', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'general', 'sort_order' => 220],
    ['code' => 'insha_arabiyyah_ulya', 'name' => 'ইনশা ও তামরীন (আরবী রচনা)', 'name_ar' => 'الإنشاء والتمرين', 'marhala_code' => 'sanabiyyah_ulya', 'category' => 'arabic_literature', 'sort_order' => 230],

    // ==========================================================
    // ফযীলত — ২ বছর
    // ==========================================================
    ['code' => 'al_hidayah', 'name' => 'হেদায়া (১ম-২য় খণ্ড)', 'name_ar' => 'الهداية', 'marhala_code' => 'fazilat', 'category' => 'fiqh', 'sort_order' => 10],
    ['code' => 'al_hidayah_akhirain', 'name' => 'হেদায়া (৩য়-৪র্থ খণ্ড)', 'name_ar' => 'الهداية الأخيرين', 'marhala_code' => 'fazilat', 'category' => 'fiqh', 'sort_order' => 20],
    ['code' => 'nurul_anwar', 'name' => 'নূরুল আনওয়ার', 'name_ar' => 'نور الأنوار', 'marhala_code' => 'fazilat', 'category' => 'usul', 'sort_order' => 30],
    ['code' => 'husami', 'name' => 'হুসামী (আল-মুন্তাখাব)', 'name_ar' => 'الحسامي', 'marhala_code' => 'fazilat', 'category' => 'usul', 'sort_order' => 40],
    ['code' => 'tawzih_talwih', 'name' => 'তাওযীহ ওয়া তালবীহ', 'name_ar' => 'التوضيح والتلويح', 'marhala_code' => 'fazilat', 'category' => 'usul', 'sort_order' => 50],
    ['code' => 'tafsir_jalalain', 'name' => 'তাফসীরে জালালাইন', 'name_ar' => 'تفسير الجلالين', 'marhala_code' => 'fazilat', 'category' => 'tafsir', 'sort_order' => 60],
    ['code' => 'tafsir_baydawi', 'name' => 'তাফসীরে বায়যাবী', 'name_ar' => 'أنوار التنزيل وأسرار التأويل', 'marhala_code' => 'fazilat', 'category' => 'tafsir', 'sort_order' => 70],
    ['code' => 'mishkatul_masabih', 'name' => 'মিশকাতুল মাসাবীহ', 'name_ar' => 'مشكاة المصابيح', 'marhala_code' => 'fazilat', 'category' => 'hadith', 'sort_order' => 80],
    ['code' => 'sharhe_nukhba', 'name' => 'শরহে নুখবাতুল ফিকার', 'name_ar' => 'نزهة النظر شرح نخبة الفكر', 'marhala_code' => 'fazilat', 'category' => 'hadith', 'sort_order' => 90],
    ['code' => 'muqaddimah_ibn_salah', 'name' => 'উসূলুল হাদীস', 'name_ar' => 'أصول الحديث', 'marhala_code' => 'fazilat', 'category' => 'hadith', 'sort_order' => 100],
    ['code' => 'hidayatul_hikmah', 'name' => 'হিদায়াতুল হিকমাহ', 'name_ar' => 'هداية الحكمة', 'marhala_code' => 'fazilat', 'category' => 'logic', 'sort_order' => 110],
    ['code' => 'mibzi_falsafa', 'name' => 'মায়বুযী (শরহে হিদায়াতুল হিকমাহ)', 'name_ar' => 'شرح الميبذي', 'marhala_code' => 'fazilat', 'category' => 'logic', 'sort_order' => 120],
    ['code' => 'sharhe_aqaid_nasafi_faz', 'name' => 'শরহুল আকাইদ (উচ্চতর)', 'name_ar' => 'شرح العقائد النسفية', 'marhala_code' => 'fazilat', 'category' => 'aqidah', 'sort_order' => 130],
    ['code' => 'aqidatut_tahawiyyah_faz', 'name' => 'শরহুল আকীদাতিত তাহাবিয়্যাহ', 'name_ar' => 'شرح العقيدة الطحاوية', 'marhala_code' => 'fazilat', 'category' => 'aqidah', 'sort_order' => 140],
    ['code' => 'sirajii_faraid_faz', 'name' => 'শরীফিয়া (শরহে সিরাজী)', 'name_ar' => 'الشريفية شرح السراجية', 'marhala_code' => 'fazilat', 'category' => 'fiqh', 'sort_order' => 150],
    ['code' => 'usulul_fiqh_faz', 'name' => 'উসূলুল ফিকহ (তুলনামূলক)', 'name_ar' => 'أصول الفقه المقارن', 'marhala_code' => 'fazilat', 'category' => 'usul', 'sort_order' => 160],
    ['code' => 'ilmul_kalam_faz', 'name' => 'ইলমুল কালাম', 'name_ar' => 'علم الكلام', 'marhala_code' => 'fazilat', 'category' => 'aqidah', 'sort_order' => 170],
    ['code' => 'tarikhut_tashri', 'name' => 'তারীখুত তাশরীইল ইসলামী', 'name_ar' => 'تاريخ التشريع الإسلامي', 'marhala_code' => 'fazilat', 'category' => 'history', 'sort_order' => 180],
    ['code' => 'ulumul_quran_faz', 'name' => 'উলূমুল কুরআন', 'name_ar' => 'علوم القرآن', 'marhala_code' => 'fazilat', 'category' => 'usul', 'sort_order' => 190],
    ['code' => 'al_fauzul_kabir', 'name' => 'আল-ফাওযুল কাবীর', 'name_ar' => 'الفوز الكبير في أصول التفسير', 'marhala_code' => 'fazilat', 'category' => 'usul', 'sort_order' => 200],
    ['code' => 'hujjatullahil_baligah_faz', 'name' => 'হুজ্জাতুল্লাহিল বালিগাহ (আংশিক)', 'name_ar' => 'حجة الله البالغة', 'marhala_code' => 'fazilat', 'category' => 'hadith', 'sort_order' => 210],
    ['code' => 'adab_arabi_faz', 'name' => 'আদাবুল লুগাতিল আরাবিয়্যাহ', 'name_ar' => 'آداب اللغة العربية', 'marhala_code' => 'fazilat', 'category' => 'arabic_literature', 'sort_order' => 220],
    ['code' => 'khutubat_faz', 'name' => 'খুতুবাত ও দাওয়াহ', 'name_ar' => 'الخطابة والدعوة', 'marhala_code' => 'fazilat', 'category' => 'general', 'sort_order' => 230],
    ['code' => 'firaq_batilah', 'name' => 'ফিরাকে বাতিলা', 'name_ar' => 'الفرق الباطلة', 'marhala_code' => 'fazilat', 'category' => 'aqidah', 'sort_order' => 240],

    // ==========================================================
    // তাকমিল / দাওরায়ে হাদীস — ১ বছর (সিহাহ সিত্তাহ ও অন্যান্য)
    // ==========================================================
    ['code' => 'bukhari_sharif_awwal', 'name' => 'বুখারী শরীফ (১ম খণ্ড)', 'name_ar' => 'صحيح البخاري - الجزء الأول', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 10],
    ['code' => 'bukhari_sharif_sani', 'name' => 'বুখারী শরীফ (২য় খণ্ড)', 'name_ar' => 'صحيح البخاري - الجزء الثاني', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 20],
    ['code' => 'muslim_sharif_awwal', 'name' => 'মুসলিম শরীফ (১ম খণ্ড)', 'name_ar' => 'صحيح مسلم - الجزء الأول', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 30],
    ['code' => 'muslim_sharif_sani', 'name' => 'মুসলিম শরীফ (২য় খণ্ড)', 'name_ar' => 'صحيح مسلم - الجزء الثاني', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 40],
    ['code' => 'tirmizi_sharif_awwal', 'name' => 'তিরমিযী শরীফ (১ম খণ্ড)', 'name_ar' => 'جامع الترمذي - الجزء الأول', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 50],
    ['code' => 'tirmizi_sharif_sani', 'name' => 'তিরমিযী শরীফ (২য় খণ্ড)', 'name_ar' => 'جامع الترمذي - الجزء الثاني', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 60],
    ['code' => 'abu_dawud_sharif', 'name' => 'আবু দাউদ শরীফ', 'name_ar' => 'سنن أبي داود', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 70],
    ['code' => 'nasai_sharif', 'name' => 'নাসাঈ শরীফ', 'name_ar' => 'سنن النسائي', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 80],
    ['code' => 'ibn_majah_sharif', 'name' => 'ইবনে মাজাহ শরীফ', 'name_ar' => 'سنن ابن ماجه', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 90],
    ['code' => 'sharhu_maanil_athar', 'name' => 'তাহাবী শরীফ (শরহু মাআনিল আসার)', 'name_ar' => 'شرح معاني الآثار للطحاوي', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 100],
    ['code' => 'shamail_tirmizi', 'name' => 'শামায়েলে তিরমিযী', 'name_ar' => 'الشمائل المحمدية', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 110],
    ['code' => 'muwatta_malik', 'name' => 'মুয়াত্তা ইমাম মালিক', 'name_ar' => 'موطأ الإمام مالك', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 120],
    ['code' => 'muwatta_muhammad', 'name' => 'মুয়াত্তা ইমাম মুহাম্মাদ', 'name_ar' => 'موطأ الإمام محمد', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 130],
    ['code' => 'sharhus_sunnah_takmil', 'name' => 'শরহুস সুন্নাহ', 'name_ar' => 'شرح السنة للبغوي', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 140],
    ['code' => 'usulul_hadith_takmil', 'name' => 'উসূলুল হাদীস ও আসমাউর রিজাল', 'name_ar' => 'أصول الحديث وأسماء الرجال', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 150],
    ['code' => 'muqaddimah_shaykh_abdulhaq', 'name' => 'মুকাদ্দামায়ে শায়খ আবদুল হক', 'name_ar' => 'مقدمة الشيخ عبد الحق', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 160],
    ['code' => 'ilmul_jarh_wat_tadil', 'name' => 'ইলমুল জারহ ওয়াত তাদীল', 'name_ar' => 'علم الجرح والتعديل', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 170],
    ['code' => 'hujjatullahil_balighah_takmil', 'name' => 'হুজ্জাতুল্লাহিল বালিগাহ', 'name_ar' => 'حجة الله البالغة', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 180],
    ['code' => 'tahawi_sharif_sani', 'name' => 'তাহাবী শরীফ (২য় খণ্ড)', 'name_ar' => 'شرح معاني الآثار - الجزء الثاني', 'marhala_code' => 'takmil', 'category' => 'hadith', 'sort_order' => 190],
    ['code' => 'ikhtilaful_aimmah', 'name' => 'ইখতিলাফুল আইম্মাহ', 'name_ar' => 'اختلاف الأئمة', 'marhala_code' => 'takmil', 'category' => 'fiqh', 'sort_order' => 200],

    // ==========================================================
    // নাযেরা বিভাগ — ২ বছর
    // ==========================================================
    ['code' => 'nazera_qaida_nurani', 'name' => 'নূরানী কায়দা (নাযেরা)', 'name_ar' => 'القاعدة النورانية', 'marhala_code' => 'nazera', 'category' => 'quran', 'sort_order' => 10],
    ['code' => 'nazera_amma_para', 'name' => 'আম্মাপারা (নাযেরা)', 'name_ar' => 'جزء عمّ', 'marhala_code' => 'nazera', 'category' => 'quran', 'sort_order' => 20],
    ['code' => 'nazera_quran_kamil', 'name' => 'নাযেরা কুরআন (পূর্ণাঙ্গ)', 'name_ar' => 'الناظرة للقرآن الكريم', 'marhala_code' => 'nazera', 'category' => 'quran', 'sort_order' => 30],
    ['code' => 'nazera_tajweed_mabadi', 'name' => 'তাজবীদের প্রাথমিক নিয়ম', 'name_ar' => 'مبادئ التجويد', 'marhala_code' => 'nazera', 'category' => 'quran', 'sort_order' => 40],
    ['code' => 'nazera_makharij', 'name' => 'মাখারিজুল হুরূফ', 'name_ar' => 'مخارج الحروف', 'marhala_code' => 'nazera', 'category' => 'quran', 'sort_order' => 50],
    ['code' => 'nazera_sifatul_huruf', 'name' => 'সিফাতুল হুরূফ', 'name_ar' => 'صفات الحروف', 'marhala_code' => 'nazera', 'category' => 'quran', 'sort_order' => 60],
    ['code' => 'nazera_masnun_dua', 'name' => 'মাসনূন দুআ ও সূরা মুখস্থ', 'name_ar' => 'الأدعية المسنونة وحفظ السور', 'marhala_code' => 'nazera', 'category' => 'quran', 'sort_order' => 70],
    ['code' => 'nazera_namaz_masail', 'name' => 'নামাযের মাসায়েল', 'name_ar' => 'مسائل الصلاة', 'marhala_code' => 'nazera', 'category' => 'fiqh', 'sort_order' => 80],
    ['code' => 'nazera_akaid', 'name' => 'প্রাথমিক আকাইদ', 'name_ar' => 'العقائد الأساسية', 'marhala_code' => 'nazera', 'category' => 'aqidah', 'sort_order' => 90],
    ['code' => 'nazera_bangla', 'name' => 'বাংলা ও অংক (সহায়ক)', 'name_ar' => 'البنغالية والرياضيات', 'marhala_code' => 'nazera', 'category' => 'general', 'sort_order' => 100],

    // ==========================================================
    // হিফজুল কুরআন বিভাগ — ৩ বছর
    // ==========================================================
    ['code' => 'hifz_amma_para', 'name' => 'হিফজ আম্মাপারা', 'name_ar' => 'حفظ جزء عمّ', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 10],
    ['code' => 'hifz_sabak', 'name' => 'সবক (নতুন পাঠ হিফজ)', 'name_ar' => 'السبق', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 20],
    ['code' => 'hifz_sabki', 'name' => 'সবকী (সাম্প্রতিক পারা পুনরাবৃত্তি)', 'name_ar' => 'السبقي', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 30],
    ['code' => 'hifz_manzil', 'name' => 'মানযিল (পুরাতন পারা দাওর)', 'name_ar' => 'المنزل', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 40],
    ['code' => 'hifz_dawr_kamil', 'name' => 'দাওরে কামিল (পূর্ণ কুরআন দাওর)', 'name_ar' => 'الدور الكامل', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 50],
    ['code' => 'hifz_tajweed', 'name' => 'তাজবীদ (হিফজ বিভাগ)', 'name_ar' => 'التجويد لقسم الحفظ', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 60],
    ['code' => 'hifz_tuhfatul_atfal', 'name' => 'তুহফাতুল আতফাল', 'name_ar' => 'تحفة الأطفال', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 70],
    ['code' => 'hifz_jazariyyah', 'name' => 'মুকাদ্দিমাতুল জাযারিয়্যাহ', 'name_ar' => 'المقدمة الجزرية', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 80],
    ['code' => 'hifz_waqf_ibtida', 'name' => 'ওয়াকফ ও ইবতিদা', 'name_ar' => 'الوقف والابتداء', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 90],
    ['code' => 'hifz_rasmul_uthmani', 'name' => 'রসমে উসমানী পরিচিতি', 'name_ar' => 'الرسم العثماني', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 100],
    ['code' => 'hifz_taraweeh_tarbiyat', 'name' => 'তারাবীহ প্রশিক্ষণ', 'name_ar' => 'تدريب التراويح', 'marhala_code' => 'hifz', 'category' => 'quran', 'sort_order' => 110],
    ['code' => 'hifz_masail_zaruriyyah', 'name' => 'যরূরী মাসায়েল ও আদাব', 'name_ar' => 'المسائل الضرورية والآداب', 'marhala_code' => 'hifz', 'category' => 'fiqh', 'sort_order' => 120],

    // ==========================================================
    // কিরাআত বিভাগ — ২ বছর
    // ==========================================================
    ['code' => 'qirat_shatibiyyah', 'name' => 'আশ-শাতিবিয়্যাহ', 'name_ar' => 'حرز الأماني ووجه التهاني (الشاطبية)', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 10],
    ['code' => 'qirat_sabaa', 'name' => 'কিরাআতে সাবআ', 'name_ar' => 'القراءات السبع', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 20],
    ['code' => 'qirat_ashara', 'name' => 'কিরাআতে আশারা', 'name_ar' => 'القراءات العشر', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 30],
    ['code' => 'qirat_durrah', 'name' => 'আদ-দুররাতুল মুদিয়্যাহ', 'name_ar' => 'الدرة المضية', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 40],
    ['code' => 'qirat_tayyibatun_nashr', 'name' => 'তাইয়্যিবাতুন নাশর', 'name_ar' => 'طيبة النشر في القراءات العشر', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 50],
    ['code' => 'qirat_hafs_asim', 'name' => 'রেওয়ায়াতে হাফস আন আসেম', 'name_ar' => 'رواية حفص عن عاصم', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 60],
    ['code' => 'qirat_warsh_nafi', 'name' => 'রেওয়ায়াতে ওয়ারশ আন নাফে', 'name_ar' => 'رواية ورش عن نافع', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 70],
    ['code' => 'qirat_jazariyyah_ulya', 'name' => 'মুকাদ্দিমাতুল জাযারিয়্যাহ (উচ্চতর)', 'name_ar' => 'المقدمة الجزرية', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 80],
    ['code' => 'qirat_fathul_wahid', 'name' => 'ফাতহুল ওয়াহীদ', 'name_ar' => 'فتح الوحيد', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 90],
    ['code' => 'qirat_tajweed_ulya', 'name' => 'ইলমুত তাজবীদ (উচ্চতর)', 'name_ar' => 'علم التجويد المتقدم', 'marhala_code' => 'qirat', 'category' => 'quran', 'sort_order' => 100],
    ['code' => 'qirat_ulumul_quran', 'name' => 'উলূমুল কুরআন (কিরাআত বিভাগ)', 'name_ar' => 'علوم القرآن', 'marhala_code' => 'qirat', 'category' => 'usul', 'sort_order' => 110],
    ['code' => 'qirat_tarikh_qiraat', 'name' => 'তারীখুল কিরাআত', 'name_ar' => 'تاريخ القراءات', 'marhala_code' => 'qirat', 'category' => 'history', 'sort_order' => 120],

    // ==========================================================
    // ইফতা (উচ্চতর ফিকহ ও ফতোয়া) — ১ বছর
    // ==========================================================
    ['code' => 'usulul_ifta', 'name' => 'উসূলুল ইফতা', 'name_ar' => 'أصول الإفتاء', 'marhala_code' => 'ifta', 'category' => 'usul', 'sort_order' => 10],
    ['code' => 'raddul_muhtar', 'name' => 'রদ্দুল মুহতার (ফাতাওয়ায়ে শামী)', 'name_ar' => 'رد المحتار على الدر المختار', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 20],
    ['code' => 'fatawa_shami_kitabus_salah', 'name' => 'ফাতাওয়ায়ে শামী (ইবাদাত অধ্যায়)', 'name_ar' => 'الفتاوى الشامية - كتاب العبادات', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 30],
    ['code' => 'fatawa_alamgiri', 'name' => 'ফাতাওয়ায়ে আলমগীরী (হিন্দিয়্যাহ)', 'name_ar' => 'الفتاوى الهندية (العالمكيرية)', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 40],
    ['code' => 'al_ashbah_wan_nazair', 'name' => 'আল-আশবাহ ওয়ান নাযাইর', 'name_ar' => 'الأشباه والنظائر', 'marhala_code' => 'ifta', 'category' => 'usul', 'sort_order' => 50],
    ['code' => 'sharhul_majallah', 'name' => 'শরহুল মাজাল্লাহ', 'name_ar' => 'شرح المجلة', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 60],
    ['code' => 'qawaid_fiqhiyyah', 'name' => 'আল-কাওয়াইদুল ফিকহিয়্যাহ', 'name_ar' => 'القواعد الفقهية', 'marhala_code' => 'ifta', 'category' => 'usul', 'sort_order' => 70],
    ['code' => 'imdadul_ahkam', 'name' => 'ইমদাদুল আহকাম', 'name_ar' => 'إمداد الأحكام', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 80],
    ['code' => 'imdadul_fatawa', 'name' => 'ইমদাদুল ফাতাওয়া', 'name_ar' => 'إمداد الفتاوى', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 90],
    ['code' => 'fatawa_darul_ulum_deoband', 'name' => 'ফাতাওয়ায়ে দারুল উলূম দেওবন্দ', 'name_ar' => 'فتاوى دار العلوم ديوبند', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 100],
    ['code' => 'ahsanul_fatawa', 'name' => 'আহসানুল ফাতাওয়া', 'name_ar' => 'أحسن الفتاوى', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 110],
    ['code' => 'kifayatul_mufti', 'name' => 'কিফায়াতুল মুফতী', 'name_ar' => 'كفاية المفتي', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 120],
    ['code' => 'usulul_iftah_ibn_abidin', 'name' => 'শরহু উকূদি রসমিল মুফতী', 'name_ar' => 'شرح عقود رسم المفتي', 'marhala_code' => 'ifta', 'category' => 'usul', 'sort_order' => 130],
    ['code' => 'ilmul_faraid_ifta', 'name' => 'ইলমুল ফারাইয (উচ্চতর প্রয়োগ)', 'name_ar' => 'علم الفرائض التطبيقي', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 140],
    ['code' => 'fiqhul_muamalat_ifta', 'name' => 'ফিকহুল মুআমালাত (আধুনিক লেনদেন)', 'name_ar' => 'فقه المعاملات المعاصرة', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 150],
    ['code' => 'islami_banking_ifta', 'name' => 'ইসলামী ব্যাংকিং ও অর্থনীতি', 'name_ar' => 'المصرفية والاقتصاد الإسلامي', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 160],
    ['code' => 'tibbi_masail_ifta', 'name' => 'তিব্বী মাসায়েল (চিকিৎসা ফিকহ)', 'name_ar' => 'المسائل الطبية المعاصرة', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 170],
    ['code' => 'tadribul_ifta', 'name' => 'তাদরীবুল ইফতা (ব্যবহারিক প্রশিক্ষণ)', 'name_ar' => 'تدريب الإفتاء', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 180],
    ['code' => 'bidayatul_mujtahid_ifta', 'name' => 'বিদায়াতুল মুজতাহিদ (তুলনামূলক ফিকহ)', 'name_ar' => 'بداية المجتهد ونهاية المقتصد', 'marhala_code' => 'ifta', 'category' => 'fiqh', 'sort_order' => 190],
    ['code' => 'ilaus_sunan_ifta', 'name' => 'ইলাউস সুনান', 'name_ar' => 'إعلاء السنن', 'marhala_code' => 'ifta', 'category' => 'hadith', 'sort_order' => 200],
];
