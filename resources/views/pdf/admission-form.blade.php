@php
    use App\Support\Bn;
    use App\Support\HijriDate;

    /** ফাঁকা ঘরে ড্যাশ — ছাপা ফর্মে খালি জায়গা অস্পষ্ট দেখায়। */
    $v = static fn (?string $value): string => filled($value) ? e($value) : '—';
@endphp

<style>
    /* mPDF does not inherit the body font into bold contexts (th/strong/h1);
       without an explicit family those runs fall back to a font with no
       Bengali glyphs and print as tofu boxes. See PdfFactory. */
    body, h1, h2, h3, p, td, th, div, span, b, strong {
        font-family: hindsiliguri;
    }
    body { font-size: 9.5pt; color: #111; }

    .head { border-bottom: 1mm solid {{ $brand }}; padding-bottom: 2mm; margin-bottom: 3mm; }
    .head-name { font-size: 17pt; font-weight: bold; color: {{ $brand }}; margin: 0; }
    .head-meta { font-size: 8.5pt; color: #333; margin: 0.8mm 0 0; }
    .logo { width: 20mm; }

    .title {
        background: {{ $brand }}; color: #fff; text-align: center;
        font-size: 12pt; font-weight: bold; padding: 1.8mm; margin-bottom: 3mm;
    }
    .title-sub { font-size: 8.5pt; font-weight: normal; }

    .sec {
        background: {{ $accent }}; color: #fff; font-size: 9.5pt; font-weight: bold;
        padding: 1.2mm 2mm; margin: 0 0 1.5mm;
    }

    table.f { width: 100%; border-collapse: collapse; margin-bottom: 3mm; }
    table.f td { border: 0.25mm solid #666; padding: 1.5mm 2mm; vertical-align: top; }
    td.k { width: 22%; background: #f1f1f1; font-weight: bold; }
    td.val { width: 28%; }

    /* The top table gives up 30mm to the photo box, so its columns are set in
       mm rather than %: percentages there left the value cells narrower than
       their labels and "অনাবাসিক" wrapped mid-word. */
    table.top td.k { width: 26mm; }
    table.top td.val { width: 36mm; }

    .photo {
        border: 0.4mm dashed #666; width: 30mm; height: 36mm;
        text-align: center; font-size: 7.5pt; color: #555; padding-top: 15mm;
    }

    .terms { border: 0.25mm solid #666; padding: 1.5mm 2mm 1.5mm 6mm; font-size: 8pt; margin-bottom: 2mm; }
    .terms li { margin-bottom: 0.5mm; }

    .sign { width: 100%; border-collapse: collapse; }
    .sign td { text-align: center; font-size: 8.5pt; padding-top: 9mm; width: 33%; }
    .sign .line { border-top: 0.3mm solid #333; padding-top: 1mm; }

    .office { border: 0.25mm solid #666; border-top: 1mm solid {{ $accent }}; padding: 2mm; margin-top: 2mm; page-break-inside: avoid; }
    .office-h { font-weight: bold; color: {{ $accent }}; font-size: 9pt; margin: 0 0 1.5mm; }
    .blank { border-bottom: 0.25mm solid #666; }
</style>

{{-- হেডার: লোগো + মাদরাসার নাম --}}
<table class="head">
    <tr>
        @if ($logo)
            <td style="width: 22mm;"><img src="{{ $logo }}" class="logo"></td>
        @endif
        <td>
            <p class="head-name">{{ $madrasa }}</p>
            <p class="head-meta">
                {{ $settings->address ?: '' }}
                @if ($settings->phone)
                    @if ($settings->address) · @endif মোবাইল: {{ Bn::num($settings->phone) }}
                @endif
                @if ($settings->established_year)
                    · প্রতিষ্ঠিত: {{ Bn::num($settings->established_year) }}
                @endif
            </p>
        </td>
    </tr>
</table>

<div class="title">
    ভর্তি ফরম
    @if ($admission->academicSession)
        <span class="title-sub">— {{ $admission->academicSession->name }} শিক্ষাবর্ষ</span>
    @endif
</div>

{{-- আবেদন নম্বর, তারিখ ও ছবির ঘর --}}
<table class="f top">
    <tr>
        <td class="k">আবেদন নং</td>
        <td class="val"><b>{{ Bn::num($admission->application_no) }}</b></td>
        <td class="k">আবেদনের তারিখ</td>
        <td>
            {{ $admission->applied_on ? Bn::num($admission->applied_on->format('d/m/Y')) : '—' }}
            @if ($admission->applied_on)
                <br><span style="font-size: 8pt;">{{ HijriDate::format($admission->applied_on) }}</span>
            @endif
        </td>
        <td rowspan="2" class="photo">ছবি<br>(পাসপোর্ট সাইজ)</td>
    </tr>
    <tr>
        <td class="k">ভর্তিচ্ছু জামাত</td>
        <td>{{ $v($admission->jamaat?->name) }}</td>
        <td class="k">আবাসিক/অনাবাসিক</td>
        <td>{{ $admission->residency_type === 'residential' ? 'আবাসিক' : 'অনাবাসিক' }}</td>
    </tr>
</table>

<div class="sec">১। ছাত্রের পরিচয়</div>
<table class="f">
    <tr>
        <td class="k">নাম (বাংলা)</td>
        <td colspan="3">{{ $v($admission->name) }}</td>
    </tr>
    <tr>
        <td class="k">নাম (আরবি)</td>
        <td colspan="3" style="font-family: xbriyaz, serif;">{{ $v($admission->name_ar) }}</td>
    </tr>
    <tr>
        <td class="k">পিতার নাম</td>
        <td class="val">{{ $v($admission->father_name) }}</td>
        <td class="k">মাতার নাম</td>
        <td>{{ $v($admission->mother_name) }}</td>
    </tr>
    <tr>
        <td class="k">জন্ম তারিখ</td>
        <td>{{ $admission->date_of_birth ? Bn::num($admission->date_of_birth->format('d/m/Y')) : '—' }}</td>
        <td class="k">জন্ম নিবন্ধন নং</td>
        <td>{{ $admission->birth_certificate_no ? Bn::num($admission->birth_certificate_no) : '—' }}</td>
    </tr>
    <tr>
        <td class="k">মোবাইল</td>
        <td>{{ $admission->mobile ? Bn::num($admission->mobile) : '—' }}</td>
        <td class="k">বিশেষ অবস্থা</td>
        <td>
            @php
                $flags = array_filter([
                    $admission->is_orphan ? 'এতিম' : null,
                    $admission->is_poor ? 'দরিদ্র' : null,
                ]);
            @endphp
            {{ $flags ? implode(' · ', $flags) : '—' }}
        </td>
    </tr>
</table>

<div class="sec">২। ঠিকানা</div>
<table class="f">
    <tr>
        <td class="k">গ্রাম</td>
        <td class="val">{{ $v($admission->village) }}</td>
        <td class="k">ডাকঘর</td>
        <td>{{ $v($admission->post_office) }}</td>
    </tr>
    <tr>
        <td class="k">ইউনিয়ন</td>
        <td>{{ $v($admission->union) }}</td>
        <td class="k">উপজেলা</td>
        <td>{{ $v($admission->upazila) }}</td>
    </tr>
    <tr>
        <td class="k">জেলা</td>
        <td colspan="3">{{ $v($admission->district) }}</td>
    </tr>
</table>

<div class="sec">৩। অভিভাবক ও পূর্ববর্তী শিক্ষা</div>
<table class="f">
    <tr>
        <td class="k">অভিভাবকের নাম</td>
        <td class="val">{{ $v($admission->guardian_name) }}</td>
        <td class="k">সম্পর্ক</td>
        <td>{{ $v($admission->guardian_relation) }}</td>
    </tr>
    <tr>
        <td class="k">অভিভাবকের মোবাইল</td>
        <td>{{ $admission->guardian_mobile ? Bn::num($admission->guardian_mobile) : '—' }}</td>
        <td class="k">পূর্ববর্তী মাদরাসা</td>
        <td>{{ $v($admission->previous_madrasa) }}</td>
    </tr>
    <tr>
        <td class="k">পূর্ববর্তী জামাত</td>
        <td>{{ $v($admission->previous_jamaat) }}</td>
        <td class="k">মন্তব্য</td>
        <td>{{ $v($admission->remarks) }}</td>
    </tr>
</table>

<div class="sec">৪। অঙ্গীকারনামা</div>
<div class="terms">
    <ol>
        <li>আমার সন্তান মাদরাসার সকল নিয়ম-শৃঙ্খলা মেনে চলবে।</li>
        <li>নির্ধারিত সময়ের মধ্যে মাসিক বেতন ও অন্যান্য ফি পরিশোধ করব।</li>
        <li>উপরে দেওয়া সকল তথ্য সত্য; অসত্য প্রমাণিত হলে ভর্তি বাতিল হবে।</li>
        <li>মাদরাসা কর্তৃপক্ষের সিদ্ধান্তই চূড়ান্ত বলে গণ্য হবে।</li>
    </ol>
</div>

<table class="sign" style="page-break-inside: avoid;">
    <tr>
        <td><div class="line">ছাত্রের স্বাক্ষর</div></td>
        <td><div class="line">অভিভাবকের স্বাক্ষর</div></td>
        <td><div class="line">মুহতামিমের স্বাক্ষর ও সিল</div></td>
    </tr>
</table>

<div class="office">
    <p class="office-h">অফিস ব্যবহারের জন্য</p>
    <table class="f" style="margin-bottom: 0;">
        <tr>
            <td class="k">ভর্তি অনুমোদন</td>
            <td class="blank val">&nbsp;</td>
            <td class="k">নির্ধারিত রোল</td>
            <td class="blank">&nbsp;</td>
        </tr>
        <tr>
            <td class="k">ভর্তি ফি</td>
            <td class="blank">&nbsp;</td>
            <td class="k">রসিদ নং</td>
            <td class="blank">&nbsp;</td>
        </tr>
    </table>
</div>
