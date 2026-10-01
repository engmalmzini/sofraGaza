@extends('layouts.public')

@section('title', 'طلب جماعي')
@section('hideFloatingCart', true)

@section('content')
@php
    $guests = $group->guests();
    $paidCount = $guests->where('status', 'paid')->count();
@endphp
<div class="mx-auto max-w-lg px-3 sm:px-4 py-5 sm:py-8 pb-32">
    <div class="mb-5">
        <p class="text-xs font-extrabold text-primary mb-1">{{ $group->restaurant->name }}</p>
        <h1 class="text-2xl font-black text-stone-900 tracking-tight">طلب جماعي</h1>
        <p class="mt-1 text-sm text-stone-500">{{ $group->statusLabel() }} · ينتهي {{ $group->expires_at?->diffForHumans() }}</p>
    </div>

    @if($group->isCollecting())
        <div class="rounded-2xl bg-amber-50 border border-amber-200/80 p-3.5 mb-4 text-xs text-amber-950 leading-relaxed">
            @if($isHost)
                اطلب أصنافك من المنيو. لما كل المدعوين يخلّصوا ويدفعوا، بتكمّل الطلب بعنوان واحد وتوصيل واحد.
            @else
                اطلب اللي بدك إياه من منيو {{ $group->restaurant->name }} وادفع نصيبك. التوصيل على صاحب الطلب.
            @endif
        </div>
    @endif

    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs space-y-3 mb-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-stone-900">الأشخاص</h2>
            <span class="text-[11px] font-bold text-stone-400">{{ $paidCount }}/{{ $guests->count() }} دفعوا</span>
        </div>

        @foreach($group->members->sortByDesc('is_host') as $row)
            <div class="rounded-xl border border-slate-100 p-3">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-sm font-extrabold text-stone-900">
                            {{ $row->displayName() }}
                            @if($row->user_id === auth()->id())
                                <span class="text-[10px] text-primary">أنت</span>
                            @endif
                            @if($row->is_host)
                                <span class="text-[10px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded-full">المنشئ</span>
                            @endif
                        </p>
                        <p class="text-[11px] text-stone-500">{{ $row->statusLabel() }}</p>
                    </div>
                    @if($row->isPaid())
                        <span class="font-mono text-sm font-black text-stone-900">{{ number_format((float) $row->total, 2) }} ₪</span>
                    @endif
                </div>
                @if($row->items())
                    <ul class="mt-2 text-[11px] text-stone-600 space-y-0.5">
                        @foreach($row->items() as $line)
                            <li>{{ $line['qty'] ?? 1 }}× {{ $line['name'] ?? 'صنف' }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    @if($quote && ($quote['lines'] ?? []) !== [])
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs mb-4">
            <h3 class="text-sm font-extrabold text-stone-900 mb-2">سلتك الحالية</h3>
            <ul class="text-xs text-stone-700 space-y-1">
                @foreach($quote['lines'] as $line)
                    <li class="flex justify-between gap-2">
                        <span>{{ $line['qty'] }}× {{ $line['item']->name }}</span>
                        <span class="font-mono">{{ number_format($line['line_total'], 2) }} ₪</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-2 text-sm font-black text-primary">نصيبك: {{ number_format($quote['items_total'], 2) }} ₪</p>
        </div>
    @endif

    <div class="space-y-2">
        @if($group->isCollecting())
            <a href="{{ route('restaurants.show', $group->restaurant) }}"
               class="w-full h-12 rounded-2xl bg-stone-900 text-white font-extrabold text-sm flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">restaurant_menu</span>
                <span>{{ $isHost ? 'أضف أصنافك من المنيو' : 'اطلب اللي بدك إياه' }}</span>
            </a>

            @if($isHost)
                @if($group->canHostPlace())
                    <a href="{{ route('group-orders.checkout', $group) }}"
                       class="w-full h-14 rounded-2xl bg-primary text-white font-extrabold text-base flex items-center justify-center">
                        كمّل الطلب وأرسل الفاتورة
                    </a>
                @else
                    <button type="button" disabled class="w-full h-12 rounded-2xl bg-stone-100 text-stone-400 font-extrabold text-sm">
                        استنى لحتى الكل يخلّص ويدفع
                    </button>
                @endif
                <form method="POST" action="{{ route('group-orders.destroy', $group) }}" onsubmit="return confirm('إلغاء الطلب الجماعي؟')">
                    @csrf
                    @method('DELETE')
                    <button class="w-full h-11 rounded-2xl text-rose-600 text-xs font-extrabold">إلغاء الطلب الجماعي</button>
                </form>
            @else
                @if(! $member?->isPaid())
                    <a href="{{ route('group-orders.pay', $group) }}"
                       class="w-full h-14 rounded-2xl bg-primary text-white font-extrabold text-base flex items-center justify-center">
                        ادفع نصيبي
                    </a>
                    <form method="POST" action="{{ route('group-orders.decline', $group) }}">
                        @csrf
                        <button class="w-full h-11 rounded-2xl text-stone-500 text-xs font-extrabold">اعتذر، مش رح أطلب</button>
                    </form>
                @else
                    <p class="text-center text-xs font-bold text-emerald-700 bg-emerald-50 rounded-2xl py-3">نصيبك مدفوع. استنى صاحب الطلب يرسل الفاتورة.</p>
                @endif
            @endif
        @elseif($group->isPlaced() && $group->order_id)
            <a href="{{ route('account.orders.show', $group->order_id) }}"
               class="w-full h-12 rounded-2xl bg-primary text-white font-extrabold text-sm flex items-center justify-center">
                متابعة الطلب #{{ $group->order_id }}
            </a>
        @endif
    </div>
</div>
@endsection
