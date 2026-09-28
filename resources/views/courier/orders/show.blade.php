@extends('layouts.courier')

@php
    $pickup = $order->restaurant->address ?: $order->restaurant->areaLabel();
    $mapsPickup = 'https://www.google.com/maps/search/?api=1&query='.urlencode($pickup);
    $mapsDrop = 'https://www.google.com/maps/search/?api=1&query='.urlencode($order->address_details);
    $restaurantPhone = $order->restaurant->phone;
    $backTab = $order->status === 'delivered' ? 'done' : 'mine';
    $foodCost = max(0, (float)$order->subtotal - (float)$order->discount_amount);
    $isPrepaid = $order->isPaidWithWallet() || $order->confirmed_at;
@endphp

@section('title', 'طلب #'.$order->id)
@section('back', route('courier.dashboard', ['tab' => $backTab]))

@section('content')
<article class="courier-detail">
    <header class="courier-detail__head">
        <span class="courier-pill courier-pill--{{ $order->status }}">{{ $order->statusLabel() }}</span>
        <b>{{ number_format($order->total, 2) }} <span class="ils">₪</span></b>
    </header>

    {{-- Financial Breakdown for Courier --}}
    <section class="p-4 bg-surface-container-low border-b border-outline/10 space-y-3">
        <h3 class="font-bold text-sm text-stone-900 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-primary text-[18px]">receipt_long</span>
            <span>تفاصيل حساب الفاتورة والتوصيل</span>
        </h3>

        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-slate-500 block">تدفعه للمطعم (قيمة الأكل):</span>
                <strong class="text-base text-rose-600 block mt-0.5">{{ number_format($foodCost, 2) }} <span class="ils">₪</span></strong>
                @if((float)$order->discount_amount > 0)
                    <small class="text-[10px] text-slate-400">(السعر الأصلي: {{ number_format($order->subtotal, 2) }} ₪)</small>
                @endif
            </div>

            <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-slate-500 block">أجرة التوصيل (لك):</span>
                <strong class="text-base text-emerald-600 block mt-0.5">{{ number_format($order->delivery_fee, 2) }} <span class="ils">₪</span></strong>
                <small class="text-[10px] text-slate-400">({{ $order->deliveryAreaLabel() ?: 'المنطقة المحددة' }})</small>
            </div>
        </div>

        {{-- Payment instructions --}}
        @if($isPrepaid)
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs flex items-start gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[18px] shrink-0 mt-0.5">verified</span>
                <div class="leading-relaxed">
                    <strong class="block font-bold">الطلب مدفوع مسبقاً ({{ $order->paymentMethodLabel() }})</strong>
                    <span>أنت تدفع للمطعم ثمن الوجبات، <strong>ولا تقبض ثمن الوجبة نقداً من الزبون</strong>، تحاسبك الإدارة أو المحفظة مباشرة.</span>
                </div>
            </div>
        @else
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-950 text-xs flex items-start gap-2">
                <span class="material-symbols-outlined text-amber-600 text-[18px] shrink-0 mt-0.5">payments</span>
                <div class="leading-relaxed">
                    <strong class="block font-bold">تحصيل نقدي عند التسليم:</strong>
                    <span>ادفع للمطعم، واقبض من الزبون عند تسليمه الطلب <strong>{{ number_format($order->total, 2) }} ₪</strong> كاملاً.</span>
                </div>
            </div>
        @endif
    </section>

    {{-- Stop 1: Pickup / Restaurant --}}
    <section class="courier-stop">
        <div class="courier-stop__label">1 · استلام وطلب من المطعم</div>
        <h2>{{ $order->restaurant->name }}</h2>
        <p>{{ $pickup }}</p>
        <div class="courier-card__actions">
            @if($restaurantPhone)
                <a class="courier-btn courier-btn--ghost" href="tel:{{ $restaurantPhone }}">
                    <span class="material-symbols-outlined">call</span>
                    المطعم
                </a>
            @endif
            <a class="courier-btn courier-btn--ghost" href="{{ $mapsPickup }}" target="_blank" rel="noopener">
                <span class="material-symbols-outlined">map</span>
                الخريطة
            </a>
        </div>
    </section>

    {{-- Ordered Items & Customization Notes (Crucial for Counter Ordering) --}}
    <section class="courier-items-wrap">
        <div class="flex items-center justify-between mb-2">
            <h2>الأصناف وملاحظات التحضير (اطلبها كما هي بالزبط)</h2>
            <span class="text-xs font-bold text-slate-500">{{ $order->items->count() }} أصناف</span>
        </div>
        
        <div class="space-y-2.5">
            @foreach($order->items as $index => $item)
                <div class="p-3 rounded-xl bg-surface-container-lowest border border-slate-200/80 shadow-xs">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2">
                            <span class="w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <span class="font-bold text-sm text-stone-900 block">{{ $item->quantity }}× {{ $item->name }}</span>
                                <span class="text-[11px] text-slate-500">سعر الوحدة: {{ number_format($item->price, 2) }} ₪</span>
                            </div>
                        </div>
                        <b class="text-sm font-bold text-stone-900 shrink-0">{{ number_format($item->line_total, 2) }} <span class="ils">₪</span></b>
                    </div>

                    {{-- Customer Item-Level Note (e.g. without cheese, extra chili) --}}
                    @if($item->notes)
                        <div class="mt-2.5 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-950 text-xs flex items-start gap-1.5 font-medium">
                            <span class="material-symbols-outlined text-rose-600 text-[16px] shrink-0 mt-0.5">error</span>
                            <div>
                                <strong class="text-rose-700">ملاحظة الزبون للصنف:</strong>
                                <span class="font-bold text-stone-900 mr-1">{{ $item->notes }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- Stop 2: Dropoff / Customer --}}
    <section class="courier-stop courier-stop--drop">
        <div class="courier-stop__label">2 · تسليم للزبون</div>
        <h2>{{ $order->user->name }}</h2>
        @if($order->delivery_area)
            <div class="text-xs font-bold text-primary mb-1">منطقة التوصيل: {{ $order->deliveryAreaLabel() }}</div>
        @endif
        <p class="text-sm leading-relaxed">{{ $order->address_details }}</p>
        
        @if($order->notes)
            <div class="mt-2 p-2 rounded-lg bg-surface-container-high border border-outline/10 text-xs">
                <span class="text-slate-500 block text-[11px]">ملاحظات عامة للتوصيل:</span>
                <span class="font-medium text-stone-900">{{ $order->notes }}</span>
            </div>
        @endif

        <div class="courier-card__actions mt-3">
            <a class="courier-btn courier-btn--ghost" href="tel:{{ $order->phone }}">
                <span class="material-symbols-outlined">call</span>
                اتصال بالزبون
            </a>
            <a class="courier-btn courier-btn--ghost" href="{{ $mapsDrop }}" target="_blank" rel="noopener">
                <span class="material-symbols-outlined">map</span>
                الخريطة
            </a>
        </div>
    </section>
</article>

@if($order->courier_id === auth()->id() && $order->status === 'delivering')
    <form method="POST" action="{{ route('courier.orders.complete', $order) }}" class="courier-sticky">
        @csrf
        <button class="courier-btn courier-btn--ok courier-btn--block">
            <span class="material-symbols-outlined">check_circle</span>
            <span>تأكيد إتمام التسليم</span>
        </button>
    </form>
@endif
@endsection
