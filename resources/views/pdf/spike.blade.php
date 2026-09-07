<style>
    /* mPDF does not reliably inherit the body font into bold contexts
       (h1/th/strong), which silently falls back to a font with no Bengali
       glyphs and prints tofu boxes. Declare the family explicitly on every
       element that can go bold. */
    body, h1, h2, h3, p, td, th, div, span, b, strong {
        font-family: hindsiliguri;
    }
    body { font-size: 11pt; }
    h1 { text-align: center; font-size: 16pt; margin: 0 0 2mm; }
    .sub { text-align: center; font-size: 10pt; margin: 0 0 5mm; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 6mm; }
    th, td { border: 0.3mm solid #333; padding: 1.6mm 2mm; }
    th { background: #eee; }
    .num { text-align: center; }
    .ar { font-family: xbriyaz, serif; }
    .box { border: 0.3mm solid #333; padding: 3mm; margin-bottom: 5mm; }
    .label { font-weight: bold; }
</style>

<h1>{{ $madrasa }}</h1>
<p class="sub">{{ $address }}<br>{{ $examName }} — {{ $session }}</p>

<div class="box">
    <span class="label">নাম:</span> {{ $student }} &nbsp;
    <span class="label">পিতা:</span> {{ $father }}<br>
    <span class="label">জামাত:</span> {{ $jamaat }} &nbsp;
    <span class="label">রোল:</span> {{ $roll }} &nbsp;
    <span class="label">তারিখ:</span> {{ $date }} ({{ $hijri }})
</div>

<table>
    <thead>
        <tr>
            <th>ক্রমিক</th>
            <th>কিতাবের নাম</th>
            <th>পূর্ণমান</th>
            <th>লিখিত</th>
            <th>মৌখিক</th>
            <th>মোট</th>
            <th>মন্তব্য</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($marks as $i => $m)
            <tr>
                <td class="num">{{ \App\Support\Bn::num($i + 1) }}</td>
                <td>{{ $m['name'] }} <span class="ar">({{ $m['name_ar'] }})</span></td>
                <td class="num">{{ \App\Support\Bn::num($m['full']) }}</td>
                <td class="num">{{ \App\Support\Bn::num($m['written']) }}</td>
                <td class="num">{{ \App\Support\Bn::num($m['oral']) }}</td>
                <td class="num">{{ \App\Support\Bn::num($m['written'] + $m['oral']) }}</td>
                <td>{{ $m['grade'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="box">
    <span class="label">মোট নম্বর:</span> {{ \App\Support\Bn::num($total) }} /
    {{ \App\Support\Bn::num($fullTotal) }} &nbsp;
    <span class="label">শতকরা:</span> {{ \App\Support\Bn::num(number_format($percent, 2)) }}% &nbsp;
    <span class="label">গ্রেড:</span> {{ $grade }} &nbsp;
    <span class="label">মেধাক্রম:</span> {{ \App\Support\Bn::num($position) }}
</div>

<div class="box">
    <p class="label">যুক্তাক্ষর পরীক্ষা (conjunct rendering test):</p>
    <p>ক্ষ ঞ্চ ষ্ট্র র‍্যা ক্ত ত্র জ্ঞ হ্ম ণ্ড স্থ দ্ধ ব্ধ ঙ্গ ল্প শ্চ</p>
    <p>শিক্ষা · বিজ্ঞান · রাষ্ট্র · সংখ্যা · উজ্জ্বল · বৃত্তি · স্বাস্থ্য</p>
    <p class="label">বাংলা সংখ্যা:</p>
    <p>{{ \App\Support\Bn::num('0123456789') }} — {{ \App\Support\Bn::taka(1234567.89) }}</p>
    <p class="label">মিশ্র বাংলা+আরবি লাইন:</p>
    <p>হেদায়া <span class="ar">(الهداية)</span> · মিশকাত <span class="ar">(مشكاة المصابيح)</span> ·
       বুখারী <span class="ar">(صحيح البخاري)</span></p>
</div>
