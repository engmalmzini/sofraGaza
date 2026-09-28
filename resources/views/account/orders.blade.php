@extends('layouts.public')

@section('title', 'طلباتي')

@section('content')
<div class="mx-auto max-w-5xl px-margin lg:px-margin-desktop py-3 lg:py-10">
    <h1 class="font-headline-md text-2xl font-bold text-stone-900 hidden lg:block">طلباتي</h1>

    @php
        $tier = auth()->user()->tier();
    @endphp
    {{-- بطاقة مستوى الولاء بالخلفية السوداء وبتصميم مرتب وأنيق --}}
    <div class="relative overflow-hidden rounded-2xl p-4 mt-3 mb-5 border border-stone-800 shadow-[0_6px_24px_rgba(0,0,0,0.22)] space-y-3 text-white" style="background: linear-gradient(145deg, #18181b 0%, #0d0d0f 100%);">
        
        {{-- هالة برتقالية خفيفة في زاوية البطاقة --}}
        <div class="absolute -top-12 -left-12 w-32 h-32 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            {{-- الجانب الأيمن: أيقونة المستوى واسم المستوى والمشتريات --}}
            <div class="flex items-center gap-3 min-w-0">
                <div class="p-1 rounded-full ring-1 ring-amber-400/40 bg-stone-900 shrink-0 drop-shadow-sm">
                    @include('partials.tier-icon', ['tier' => $tier['key'], 'class' => 'w-10 h-10'])
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <strong class="text-sm font-extrabold text-white leading-tight">{{ $tier['name'] }}</strong>
                        <span class="px-2 py-0.5 rounded-full bg-white/10 text-stone-300 font-mono text-[10px] font-bold">
                            {{ number_format($tier['current_spent'], 1) }} ₪ مشتريات
                        </span>
                    </div>
                    @if($tier['next_tier'])
                        <p class="text-[11px] text-stone-400 mt-1 leading-snug">
                            اطلب بـ <strong class="text-amber-400 font-mono font-bold">{{ number_format($tier['remaining'], 0) }} ₪</strong> إضافية للترقية إلى <strong class="text-white">{{ $tier['next_tier'] }}</strong>
                        </p>
                    @else
                        <p class="text-[11px] text-amber-400 font-bold mt-1">أنت في أعلى مستوى بلاتيني (VIP) في سفرة غزة 👑</p>
                    @endif
                </div>
            </div>

            {{-- الجانب الأيسر: زر تفاصيل المستوى --}}
            <div class="flex items-center shrink-0 self-end sm:self-center">
                <a href="{{ route('account.show') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/15 active:scale-95 text-amber-400 hover:text-amber-300 text-xs font-bold transition-all border border-white/10 shadow-2xs">
                    <span>تفاصيل المستوى</span>
                    <span class="material-symbols-outlined text-[15px]">arrow_back</span>
                </a>
            </div>
        </div>

        {{-- شريط تقدم الترقية للمستوى التالي --}}
        @if($tier['next_tier'])
            <div class="relative z-10 pt-2 border-t border-stone-800/80 space-y-1.5">
                <div class="w-full bg-stone-800/90 rounded-full h-2 overflow-hidden p-0.5">
                    <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-400 h-full rounded-full transition-all duration-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]" style="width: {{ max(4, $tier['progress_percent']) }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[10px] text-stone-400">
                    <span>مشترياتك: <strong class="text-white font-mono font-bold">{{ number_format($tier['current_spent'], 1) }} ₪</strong></span>
                    <span>الترقية القادمة: <strong class="text-amber-400 font-bold">{{ $tier['next_tier'] }}</strong> ({{ $tier['next_min'] }} ₪)</span>
                </div>
            </div>
        @endif
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
            <div class="rounded-3xl bg-surface-container-lowest border border-slate-100 p-8 text-center space-y-3.5 shadow-xs">
                <div class="w-14 h-14 rounded-full bg-primary/10 text-primary flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined text-[30px]">receipt_long</span>
                </div>
                <h3 class="text-sm font-bold text-stone-900">لا توجد طلبات بعد</h3>
                <p class="text-xs text-stone-500 leading-relaxed max-w-sm mx-auto">لم تقم بإجراء أي طلبات حتى الآن. استكشف مطاعم غزة وابدأ طلبك الأول!</p>
                <div class="pt-1">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold text-xs px-5 py-2.5 shadow-xs transition-all active:scale-95">
                        <span>استكشف المطاعم</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    </a>
                </div>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</div>
@endsection
