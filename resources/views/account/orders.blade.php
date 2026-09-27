@extends('layouts.public')

@section('title', 'طلباتي')

@section('content')
<div class="mx-auto max-w-5xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <h1 class="font-headline-md text-2xl font-bold text-stone-900">طلباتي</h1>

    @php
        $tier = auth()->user()->tier();
    @endphp
    <div class="mt-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-surface-container-lowest border border-slate-100 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 flex items-center justify-center shrink-0 drop-shadow-xs">
                @include('partials.tier-icon', ['tier' => $tier['key'], 'class' => 'w-11 h-11'])
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <strong class="text-sm font-bold text-stone-900">{{ $tier['name'] }}</strong>
                    <span class="text-xs text-stone-500 font-mono">({{ number_format($tier['current_spent'], 1) }} ₪ مشتريات)</span>
                </div>
                @if($tier['next_tier'])
                    <p class="text-[11px] text-stone-500 mt-0.5">
                        اطلب بـ <strong class="text-primary font-bold">{{ $tier['remaining'] }} ₪</strong> إضافية للترقية إلى <strong>{{ $tier['next_tier'] }}</strong>
                    </p>
                @else
                    <p class="text-[11px] text-purple-700 font-bold mt-0.5">أنت في أعلى مستوى بلاتيني (VIP) في سفرة غزة</p>
                @endif
            </div>
        </div>
        <a href="{{ route('account.show') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1 self-start sm:self-center">
            <span>تفاصيل المستوى</span>
            <span class="material-symbols-outlined text-[14px]">arrow_back</span>
        </a>
    </div>

    <div class="space-y-3">
        @forelse($orders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="flex items-center justify-between gap-3 rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs">
                <div>
                    <div class="font-bold text-on-surface">#{{ $order->id }} — {{ $order->restaurant->name }}</div>
                    <div class="text-sm text-on-surface-variant">{{ $order->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="text-left shrink-0">
                    <div class="font-bold">{{ number_format($order->total, 2) }} <span class="ils">₪</span></div>
                    <div class="text-sm rounded-full bg-surface-container-low px-2 py-0.5 mt-1 inline-block">{{ $order->statusLabel() }}</div>
                </div>
            </a>
        @empty
            <p class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-8 text-on-surface-variant">لا توجد طلبات.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</div>
@endsection
