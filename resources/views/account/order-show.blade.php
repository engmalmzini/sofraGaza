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
    <div class="mt-6">
        @include('partials.order-invoice', ['order' => $order, 'tone' => 'public'])
    </div>
    <div class="mt-4 rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs text-sm leading-7 text-on-surface-variant">
        <div>الهاتف: {{ $order->phone }}</div>
        <div>العنوان: {{ $order->address_details }}</div>
        @if($order->rejection_reason)
            <div class="text-primary font-medium">سبب الرفض: {{ $order->rejection_reason }}</div>
        @endif
        @if($order->canCancel())
            <form method="POST" action="{{ route('account.orders.cancel', $order) }}" class="mt-6" onsubmit="return confirm('إلغاء الطلب؟')">
                @csrf
                <button class="text-sm font-bold text-primary">إلغاء الطلب</button>
            </form>
        @endif
    </div>
</div>
@endsection
