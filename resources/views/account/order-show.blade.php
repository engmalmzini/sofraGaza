@extends('layouts.public')

@section('title', 'تفاصيل الطلب')

@section('content')
<div class="mx-auto max-w-3xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-on-surface-variant mb-4">
        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        كل الطلبات
    </a>
    <h1 class="font-headline-md text-2xl font-bold text-stone-900">طلب #{{ $order->id }}</h1>
    <p class="mt-1 text-on-surface-variant">{{ $order->restaurant->name }} — {{ $order->statusLabel() }}</p>
    <div class="mt-6 rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs">
        <ul class="space-y-2">
            @foreach($order->items as $item)
                <li class="flex justify-between text-sm"><span>{{ $item->name }} × {{ $item->quantity }}</span><span>{{ number_format($item->line_total, 2) }} <span class="ils">₪</span></span></li>
            @endforeach
        </ul>
        <div class="mt-4 border-t border-stone-100 pt-4 text-sm text-on-surface-variant space-y-1">
            <div class="flex justify-between"><span>الأصلي</span><span>{{ number_format($order->subtotal, 2) }} <span class="ils">₪</span></span></div>
            <div class="flex justify-between"><span>خصم {{ $order->discount_percent }}%</span><span>{{ number_format($order->discount_amount, 2) }} <span class="ils">₪</span></span></div>
            <div class="flex justify-between"><span>توصيل</span><span>{{ number_format($order->delivery_fee, 2) }} <span class="ils">₪</span></span></div>
            <div class="mt-2 flex justify-between font-bold text-on-surface"><span>النهائي</span><span>{{ number_format($order->total, 2) }} <span class="ils">₪</span></span></div>
        </div>
        <div class="mt-4 text-sm leading-7 text-on-surface-variant">
            <div>الهاتف: {{ $order->phone }}</div>
            <div>العنوان: {{ $order->address_details }}</div>
            @if($order->rejection_reason)
                <div class="text-primary font-medium">سبب الرفض: {{ $order->rejection_reason }}</div>
            @endif
        </div>
        @if($order->canCancel())
            <form method="POST" action="{{ route('account.orders.cancel', $order) }}" class="mt-6" onsubmit="return confirm('إلغاء الطلب؟')">
                @csrf
                <button class="text-sm font-bold text-primary">إلغاء الطلب</button>
            </form>
        @endif
    </div>
</div>
@endsection
