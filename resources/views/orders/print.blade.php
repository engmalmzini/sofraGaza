<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>طباعة طلب #{{ $order->id }} — سفرة غزة</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f4f1ec;
            color: #111;
            font-family: "Segoe UI", Tahoma, "Noto Naskh Arabic", "Noto Sans Arabic", Arial, sans-serif;
            direction: rtl;
        }
        .ticket-bar {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            gap: .5rem;
            justify-content: center;
            flex-wrap: wrap;
            padding: .75rem;
            background: #fff;
            border-bottom: 1px solid #e5e0d8;
        }
        .ticket-bar a,
        .ticket-bar button {
            appearance: none;
            border: 0;
            border-radius: 999px;
            padding: .55rem 1rem;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
        }
        .ticket-bar .print {
            background: #a33900;
            color: #fff;
        }
        .ticket-bar .back {
            background: #efe7de;
            color: #3f2a1d;
        }
        .ticket {
            width: 80mm;
            max-width: 100%;
            margin: 1rem auto 2rem;
            background: #fff;
            padding: 8mm 5mm;
            box-shadow: 0 10px 30px rgba(80, 40, 10, .08);
        }
        .brand {
            text-align: center;
            border-bottom: 2px dashed #111;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .brand strong { display: block; font-size: 18px; }
        .brand small { color: #444; font-size: 12px; }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 13px;
            margin: 3px 0;
        }
        h1 {
            margin: 6px 0;
            text-align: center;
            font-size: 28px;
            letter-spacing: .04em;
        }
        .badge {
            display: inline-block;
            border: 1.5px solid #111;
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }
        .center { text-align: center; }
        .items { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 13px; }
        .items td { padding: 5px 0; vertical-align: top; border-bottom: 1px dotted #bbb; }
        .items .qty { width: 2.2rem; font-weight: 800; }
        .items .price { width: 4.4rem; text-align: left; white-space: nowrap; }
        .choice, .note { display: block; font-size: 11px; color: #333; }
        .note { font-weight: 800; }
        .totals { border-top: 2px dashed #111; padding-top: 6px; margin-top: 6px; }
        .totals .grand { font-size: 16px; font-weight: 900; }
        .foot { text-align: center; margin-top: 10px; font-size: 11px; }
        @media print {
            body { background: #fff; }
            .ticket-bar { display: none !important; }
            .ticket { width: 72mm; margin: 0; padding: 0; box-shadow: none; }
            @page { size: 80mm auto; margin: 4mm; }
        }
    </style>
</head>
<body>
    @php
        $tone = $tone ?? 'partner';
        $backUrl = $backUrl ?? url()->previous();
        $itemGroups = $order->isGroupOrder()
            ? $order->items->groupBy(fn ($item) => $item->ordered_by_name ?: 'غير معروف')
            : collect(['' => $order->items]);
    @endphp
    <div class="ticket-bar no-print">
        <button type="button" class="print" id="printNow">طباعة على الطابعة</button>
        <a class="back" href="{{ $backUrl }}">رجوع للطلب</a>
    </div>

    <main class="ticket" id="orderTicket">
        <div class="brand">
            <strong>سفرة غزة</strong>
            <small>{{ $order->venueName() }}</small>
        </div>
        <h1>#{{ $order->id }}</h1>
        <div class="center">
            <span class="badge">{{ $order->statusLabel() }}</span>
            @if($tone === 'partner' && $order->shouldShowPaidBadge())
                <span class="badge">تم الدفع</span>
            @endif
            @if($order->isGroupOrder())
                <span class="badge">طلب جماعي</span>
            @endif
        </div>
        <div class="row"><span>{{ $order->created_at?->format('Y/m/d') }}</span><span>{{ $order->created_at?->format('H:i') }}</span></div>
        <div class="row"><span>الزبون</span><strong>{{ $order->user->name ?? '—' }}</strong></div>
        <div class="row"><span>الهاتف</span><strong dir="ltr">{{ $order->phone }}</strong></div>
        @if($tone === 'admin')
            @if($order->delivery_area)
                <div class="row"><span>المنطقة</span><strong>{{ $order->deliveryAreaLabel() }}</strong></div>
            @endif
            @if($order->address_details)
                <div class="row"><span>العنوان</span><strong>{{ $order->address_details }}</strong></div>
            @endif
        @endif

        <table class="items">
            @foreach($itemGroups as $memberName => $memberItems)
                @if($order->isGroupOrder() && $memberName !== '')
                    <tr><td colspan="3"><strong>طلب {{ $memberName }}</strong></td></tr>
                @endif
                @foreach($memberItems as $item)
                    <tr>
                        <td class="qty">{{ $item->quantity }}×</td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if($item->choicesLabel())
                                <span class="choice">{{ $item->choicesLabel() }}</span>
                            @endif
                            @if($item->notes)
                                <span class="note">ملاحظة: {{ $item->notes }}</span>
                            @endif
                        </td>
                        <td class="price">{{ number_format($item->line_total, 2) }} ₪</td>
                    </tr>
                @endforeach
            @endforeach
        </table>

        @if($order->notes)
            <p class="note">ملاحظات عامة: {{ $order->notes }}</p>
        @endif

        <div class="totals">
            <div class="row"><span>مجموع الأصناف</span><span>{{ number_format($order->subtotal, 2) }} ₪</span></div>
            @if((float) $order->discount_amount > 0)
                <div class="row"><span>خصم {{ $order->discount_percent }}%</span><span>− {{ number_format($order->discount_amount, 2) }} ₪</span></div>
            @endif
            @if($tone === 'admin')
                <div class="row"><span>التوصيل</span><span>@if((float) $order->delivery_fee > 0){{ number_format($order->delivery_fee, 2) }} ₪@else مجاني @endif</span></div>
                <div class="row grand"><span>الإجمالي</span><span>{{ number_format($order->total, 2) }} ₪</span></div>
            @else
                <div class="row grand"><span>حساب الوجبات</span><span>{{ number_format($order->foodTotal(), 2) }} ₪</span></div>
            @endif
        </div>
        <p class="foot">تذكرة مطبخ — سفرة غزة</p>
    </main>

    <script>
        (function () {
            const printNow = document.getElementById('printNow');
            const auto = new URLSearchParams(window.location.search).get('autoprint') !== '0';
            function runPrint() {
                window.print();
            }
            printNow?.addEventListener('click', runPrint);
            if (auto) {
                window.addEventListener('load', function () {
                    setTimeout(runPrint, 250);
                });
            }
        })();
    </script>
</body>
</html>
