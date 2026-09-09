@php
    use App\Support\Bn;
    use App\Support\HijriDate;

    $methods = [
        'cash' => 'নগদ',
        'bank' => 'ব্যাংক',
        'mobile' => 'মোবাইল ব্যাংকিং',
    ];

    $allocations = $payment->allocations;
    $advance = $payment->unallocatedAmount();
@endphp

<style>
    /* mPDF বোল্ড কনটেক্সটে body ফন্ট নিজে থেকে নেয় না — সব ট্যাগে বলতে হয়। */
    body, h1, h2, h3, p, td, th, div, span, b, strong {
        font-family: hindsiliguri;
    }

    body { font-size: 10pt; color: #111; }

    .head { border-bottom: 0.6mm solid {{ $brand }}; padding-bottom: 3mm; }
    .madrasa { font-size: 15pt; font-weight: bold; color: {{ $brand }}; }
    .addr { font-size: 8.5pt; color: #555; }
    .title {
        margin-top: 3mm;
        background: {{ $brand }};
        color: #fff;
        padding: 1.5mm 3mm;
        font-size: 11pt;
        font-weight: bold;
    }

    .meta td { padding: 1.2mm 0; font-size: 9.5pt; }
    .label { color: #555; }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 4mm; }
    table.lines th {
        background: #f2f2f2;
        border: 0.25mm solid #999;
        padding: 1.8mm;
        font-size: 9pt;
        text-align: left;
    }
    table.lines td { border: 0.25mm solid #999; padding: 1.8mm; font-size: 9.5pt; }
    .right { text-align: right; }

    .total { background: #f8f8f8; font-weight: bold; }
    .advance { color: {{ $accent }}; font-size: 9pt; }

    .sign { margin-top: 12mm; }
    .sign td { font-size: 9pt; text-align: center; padding-top: 1.5mm; }
    .sign .line { border-top: 0.25mm solid #666; width: 45mm; }

    .cancelled {
        margin-top: 4mm;
        border: 0.5mm solid #b91c1c;
        color: #b91c1c;
        padding: 2mm 3mm;
        font-weight: bold;
        font-size: 10pt;
    }

    .note { margin-top: 5mm; font-size: 8pt; color: #666; }
</style>

<div class="head">
    <table width="100%">
        <tr>
            @if ($logo)
                <td width="18mm"><img src="{{ $logo }}" width="16mm"></td>
            @endif
            <td>
                <div class="madrasa">{{ $madrasa }}</div>
                @if ($settings->address)
                    <div class="addr">{{ $settings->address }}</div>
                @endif
                @if ($settings->phone)
                    <div class="addr">মোবাইল: {{ Bn::num($settings->phone) }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="title">আদায় রসিদ</div>

@if ($payment->isCancelled())
    {{-- বাতিল রসিদও ছাপা হয়: অভিভাবকের কাছে পুরনো কপি থাকে, মেলাতে হয়। --}}
    <div class="cancelled">
        এই রসিদটি বাতিল করা হয়েছে।
        @if ($payment->cancel_reason)
            কারণ: {{ $payment->cancel_reason }}।
        @endif
        @if ($payment->cancelled_at)
            তারিখ: {{ Bn::num($payment->cancelled_at->format('d/m/Y')) }}
        @endif
    </div>
@endif

<table width="100%" class="meta">
    <tr>
        <td width="15%" class="label">রসিদ নম্বর</td>
        <td width="35%"><b>{{ Bn::num($payment->receipt_no) }}</b></td>
        <td width="15%" class="label">তারিখ</td>
        <td width="35%">
            {{ $payment->paid_on ? Bn::num($payment->paid_on->format('d/m/Y')) : '—' }}
            @if ($payment->paid_on)
                <span style="font-size: 8pt; color: #666;">
                    ({{ HijriDate::format($payment->paid_on) }})
                </span>
            @endif
        </td>
    </tr>
    <tr>
        <td class="label">ছাত্রের নাম</td>
        <td><b>{{ $payment->student?->name ?? '—' }}</b></td>
        <td class="label">ছাত্র আইডি</td>
        <td>{{ $payment->student ? Bn::num($payment->student->student_uid) : '—' }}</td>
    </tr>
    <tr>
        <td class="label">পিতার নাম</td>
        <td>{{ $payment->student?->father_name ?? '—' }}</td>
        <td class="label">পদ্ধতি</td>
        <td>
            {{ $methods[$payment->method] ?? $payment->method }}
            @if ($payment->reference)
                — {{ Bn::num($payment->reference) }}
            @endif
        </td>
    </tr>
</table>

<table class="lines">
    <thead>
        <tr>
            <th width="12%">ক্রম</th>
            <th>বিবরণ</th>
            <th width="20%">মাস</th>
            <th width="22%" class="right">টাকা</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($allocations as $index => $allocation)
            <tr>
                <td>{{ Bn::num($index + 1) }}</td>
                <td>
                    বিল নম্বর {{ $allocation->invoice ? Bn::num($allocation->invoice->invoice_no) : '—' }}
                </td>
                <td>{{ $allocation->invoice ? Bn::num($allocation->invoice->billing_month) : '—' }}</td>
                <td class="right">{{ Bn::taka($allocation->amount) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #666;">
                    কোনো বিলে বসানো হয়নি — সম্পূর্ণ অগ্রিম জমা।
                </td>
            </tr>
        @endforelse

        @if ($advance > 0)
            <tr>
                <td colspan="3" class="advance">অগ্রিম জমা (পরের বিলে সমন্বয় হবে)</td>
                <td class="right advance">{{ Bn::taka($advance) }}</td>
            </tr>
        @endif

        <tr class="total">
            <td colspan="3" class="right">সর্বমোট</td>
            <td class="right">{{ Bn::taka($payment->amount) }}</td>
        </tr>
    </tbody>
</table>

<table width="100%" class="sign">
    <tr>
        <td width="50%">
            <div class="line"></div>
            অভিভাবকের স্বাক্ষর
        </td>
        <td width="50%">
            <div class="line"></div>
            {{ $payment->receivedBy?->name ?? 'গ্রহীতার স্বাক্ষর' }}
        </td>
    </tr>
</table>

<div class="note">
    এই রসিদটি সংরক্ষণ করুন। রসিদ ছাড়া কোনো দাবি গ্রহণযোগ্য হবে না।
</div>
