@extends('layouts.courier')

@php
    $periodTitles = [
        'today' => 'أرباح اليوم',
        'yesterday' => 'أرباح أمس',
        'week' => 'أرباح هذا الأسبوع',
        'month' => 'أرباح هذا الشهر',
        'all' => 'كل الأرباح',
    ];
    $periodHint = $periodTitles[$period] ?? 'أرباح الفترة';
@endphp

@section('title', 'كشف حساب الأرباح')
@section('back', route('courier.wallet', ['period' => $period]))
@section('shell', 'courier-shell--wide')

@section('content')
<article class="cw-statement">
    <header class="cw-statement__intro">
        <div class="cw-statement__intro-copy">
            <span class="material-symbols-outlined">calendar_month</span>
            <div>
                <h2>{{ $periodHint }}</h2>
                <p>تفصيل كامل لرسوم التوصيل، خصم المنصة 15%، وصافي حصتك 85%.</p>
            </div>
        </div>
        @include('courier.partials.earnings-periods', ['periodRoute' => 'courier.wallet.statement'])
    </header>

    <section class="cw-statement__hero" aria-label="صافي المستحقات">
        <span>صافي مستحقاتك (85%)</span>
        <strong>+{{ number_format($earnings['net_earnings'], 2) }} <em>₪</em></strong>
        <small>{{ $earnings['count'] }} توصيلة مكتملة خلال هذه الفترة</small>
    </section>

    <section class="cw-statement__stats" aria-label="ملخص الفترة">
        <div>
            <span>التوصيلات المكتملة</span>
            <strong>{{ $earnings['count'] }} طلب</strong>
        </div>
        <div>
            <span>إجمالي رسوم التوصيل</span>
            <strong>{{ number_format($earnings['total_fees'], 2) }} ₪</strong>
        </div>
        <div>
            <span>خصم المنصة (15%)</span>
            <strong class="is-cut">-{{ number_format($earnings['platform_fee'], 2) }} ₪</strong>
        </div>
    </section>

    <section class="cw-statement__list">
        <div class="cw-statement__list-head">
            <h3>طلبات التوصيل المسلّمة</h3>
            <span>{{ $earnings['count'] }} طلب</span>
        </div>

        @if($earnings['orders']->isEmpty())
            <div class="cw-statement__empty">
                <span class="material-symbols-outlined">receipt_long</span>
                <strong>لا توجد توصيلات مكتملة في هذه الفترة</strong>
                <p>عند تسليم طلب جديد سيظهر هنا مع تفصيل الرسم والخصم والصافي.</p>
            </div>
        @else
            <div class="cw-statement__orders">
                @foreach($earnings['orders'] as $order)
                    @php
                        $fee = (float) $order->delivery_fee;
                        $courierNet = round($fee * 0.85, 2);
                        $platformCut = round($fee * 0.15, 2);
                    @endphp
                    <a class="cw-statement__order" href="{{ route('courier.orders.show', ['order' => $order, 'from' => 'earnings', 'period' => $period]) }}">
                        <div class="cw-statement__order-main">
                            <div class="cw-statement__order-meta">
                                <b>طلب #{{ $order->id }}</b>
                                <time>{{ $order->delivered_at ? $order->delivered_at->format('Y/m/d H:i') : $order->created_at->format('Y/m/d H:i') }}</time>
                            </div>
                            <h4>{{ $order->restaurant->name }}</h4>
                            <p>{{ $order->deliveryAreaLabel() ?: $order->address_details }}</p>
                            <div class="cw-statement__chips">
                                <span>رسم {{ number_format($fee, 2) }} ₪</span>
                                <span class="is-cut">خصم {{ number_format($platformCut, 2) }} ₪</span>
                            </div>
                        </div>
                        <div class="cw-statement__order-net">
                            <strong>+{{ number_format($courierNet, 2) }}</strong>
                            <em>₪</em>
                            <small>صافي حصتك</small>
                            <span class="material-symbols-outlined">chevron_left</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</article>
@endsection
