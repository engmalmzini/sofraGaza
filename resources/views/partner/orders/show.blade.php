@extends('layouts.partner')

@section('title', 'طلب #'.$order->id)

@section('content')
<div class="grid gap-4 lg:grid-cols-[1.6fr_1fr]">
    <div class="space-y-4">
        <section class="admin-card">
            <h2>الأصناف</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach($order->items as $item)
                    <li class="flex justify-between"><span>{{ $item->name }} × {{ $item->quantity }}</span><span>{{ number_format($item->line_total, 2) }} <span class="ils">₪</span></span></li>
                @endforeach
            </ul>
            <div class="mt-4 border-t border-slate-100 pt-3 text-sm space-y-1">
                <div class="flex justify-between"><span>الأصلي</span><span>{{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span>خصم {{ $order->discount_percent }}%</span><span>{{ number_format($order->discount_amount, 2) }}</span></div>
                <div class="flex justify-between"><span>توصيل</span><span>{{ number_format($order->delivery_fee, 2) }}</span></div>
                <div class="mt-2 flex justify-between font-extrabold"><span>النهائي</span><span>{{ number_format($order->total, 2) }} <span class="ils">₪</span></span></div>
            </div>
        </section>
        <section class="admin-card text-sm leading-8">
            <h2>التواصل والتوصيل</h2>
            <div>الزبون: {{ $order->user->name }}</div>
            <div>الهاتف: {{ $order->phone }}</div>
            <div>العنوان: {{ $order->address_details }}</div>
            <div>ملاحظات: {{ $order->notes ?: '—' }}</div>
        </section>
    </div>
    <div class="space-y-4">
        <section class="admin-card">
            <h2>الحالة</h2>
            <div class="mt-2">@include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])</div>
            @if($order->nextStatuses())
                @foreach($order->nextStatuses() as $status => $label)
                    <form method="POST" action="{{ route('partner.orders.update', $order) }}" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $status }}">
                        @if($status === 'rejected')
                            <textarea name="rejection_reason" class="mb-2" placeholder="سبب الرفض"></textarea>
                        @endif
                        <button class="admin-btn {{ $status === 'rejected' ? 'admin-btn--danger' : 'admin-btn--secondary' }} w-full">{{ $label }}</button>
                    </form>
                @endforeach
            @endif
        </section>
        @if($order->receiptUrl())
            <section class="admin-card">
                <h2>إشعار الحوالة</h2>
                <a href="{{ $order->receiptUrl() }}" target="_blank">
                    <img src="{{ $order->receiptUrl() }}" alt="حوالة" class="mt-3 max-h-72 w-full rounded-xl object-contain">
                </a>
            </section>
        @endif
    </div>
</div>
@endsection
