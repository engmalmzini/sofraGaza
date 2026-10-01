@extends('layouts.partner')

@section('title', 'طلب #'.$order->id)

@section('content')
<div class="admin-toolbar">
    <div class="flex items-center gap-2">
        <a href="{{ route('partner.orders.index') }}" class="admin-btn admin-btn--ghost text-xs">
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            <span>العودة للطلبات</span>
        </a>
        <h1 class="text-xl font-bold text-on-surface">طلب #{{ $order->id }}</h1>
    </div>
    <div>
        <span class="admin-pill {{ in_array($order->status, ['pending_confirmation', 'confirmed']) ? 'admin-pill--wait' : ($order->status === 'preparing' ? 'admin-pill--warn' : 'admin-pill--ok') }}">
            {{ $order->statusLabel() }}
        </span>
    </div>
</div>

<div class="grid gap-4 lg:grid-cols-[1.6fr_1fr] mt-4">
    <div class="space-y-4">
        {{-- Invoice with items and item-level notes (Partner tone: Food only, no delivery fee) --}}
        @include('partials.order-invoice', ['order' => $order, 'tone' => 'partner'])
        @include('partials.group-order-receipts', ['order' => $order, 'tone' => 'partner'])

        {{-- Special preparation notes summary --}}
        @php
            $hasItemNotes = $order->items->contains(fn($item) => filled($item->notes));
        @endphp
        @if($hasItemNotes || filled($order->notes))
            <section class="admin-card border-2 border-amber-300 bg-amber-50/40">
                <h2 class="text-amber-900 flex items-center gap-1.5 font-bold">
                    <span class="material-symbols-outlined text-amber-700">warning</span>
                    <span>تنبيهات وملاحظات التحضير الخاصة</span>
                </h2>
                <div class="mt-3 space-y-2 text-sm">
                    @foreach($order->items as $item)
                        @if($item->notes)
                            <div class="p-2.5 rounded-xl bg-white border border-amber-200 text-stone-900 shadow-xs">
                                <span class="font-bold text-primary">{{ $item->quantity }}× {{ $item->name }}:</span>
                                <span class="font-extrabold text-rose-700 mr-1">{{ $item->notes }}</span>
                            </div>
                        @endif
                    @endforeach

                    @if($order->notes)
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-700">
                            <strong>ملاحظات عامة من الزبون:</strong> {{ $order->notes }}
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </div>

    <div class="space-y-4">
        {{-- Order Status and Quick Action --}}
        <section class="admin-card">
            <h2>حالة الطلب والتحضير</h2>
            <div class="mt-2 flex items-center gap-2">
                <span class="admin-pill {{ in_array($order->status, ['pending_confirmation', 'confirmed']) ? 'admin-pill--wait' : ($order->status === 'preparing' ? 'admin-pill--warn' : 'admin-pill--ok') }}">
                    {{ $order->statusLabel() }}
                </span>
                @if($order->isPrepared())
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 bg-emerald-100 px-2.5 py-1 rounded-full">
                        <span class="material-symbols-outlined text-[15px]">task_alt</span>
                        <span>تم تجهيز الوجبات</span>
                    </span>
                @endif
            </div>

            @if($order->status === 'confirmed')
                <form method="POST" action="{{ route('partner.orders.update', $order) }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="preparing">
                    <button class="admin-btn admin-btn--primary w-full">
                        <span class="material-symbols-outlined">cooking</span>
                        <span>بدء تجهيز الطلب في المطبخ</span>
                    </button>
                </form>
            @elseif($order->status === 'preparing')
                @if(! $order->isPrepared())
                    <form method="POST" action="{{ route('partner.orders.prepared', $order) }}" class="mt-4">
                        @csrf
                        <button class="admin-btn admin-btn--primary w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                            <span>تم تجهيز الطلب (جاهز للاستلام)</span>
                        </button>
                    </form>
                    <p class="text-xs text-on-surface-variant mt-2 text-center">
                        انقر فور انتهاء المطبخ من تحضير الوجبات، وسيصل إشعار فوري للإدارة والمندوب ليتوجه للمطعم واستلامها.
                    </p>
                @else
                    <div class="mt-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 space-y-1">
                        <div class="font-bold flex items-center gap-1.5 text-sm">
                            <span class="material-symbols-outlined text-emerald-600 text-[20px]">task_alt</span>
                            <span>الوجبات جاهزة في المطبخ للاستلام</span>
                        </div>
                        <div class="text-xs text-emerald-800">
                            وقت اكتمال التجهيز: {{ $order->prepared_at?->format('H:i') }} ({{ $order->prepared_at?->diffForHumans() }})
                        </div>
                        <div class="text-[11px] text-emerald-700 pt-1">
                            تم إرسال إشعار للإدارة والمندوب، يرجى تسليم الطلب للمندوب فور وصوله للمطعم.
                        </div>
                    </div>
                @endif
            @endif
        </section>

        {{-- Customer Info --}}
        <section class="admin-card text-sm leading-8">
            <h2>بيانات الزبون</h2>
            <div>الاسم: <strong>{{ $order->user->name }}</strong></div>
            <div>الهاتف: <a href="tel:{{ $order->phone }}" class="text-primary font-bold" dir="ltr">{{ $order->phone }}</a></div>
        </section>

        {{-- Delivery Courier Info --}}
        <section class="admin-card text-sm leading-7">
            <h2>تسليم الطلب للتوصيل</h2>
            @if($order->courier)
                <div class="mt-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200 space-y-1">
                    <div>المندوب: <strong>{{ $order->courier->name }}</strong></div>
                    <div>الهاتف: <a href="tel:{{ $order->courier->phone }}" class="text-emerald-700 font-bold" dir="ltr">{{ $order->courier->phone }}</a></div>
                    <div class="text-xs text-emerald-800">مندوب التوصيل سيتوجه للمطعم لاستلام الوجبات وتسليمها للزبون.</div>
                </div>
            @else
                <p class="mt-2 text-on-surface-variant text-xs">جاري تعيين مندوب من قِبل إدارة التطبيق لاستلام الطلب من المطعم...</p>
            @endif
        </section>
    </div>
</div>
@endsection
