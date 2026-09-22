@extends('layouts.partner')

@section('title', 'طلب #'.$order->id)

@section('content')
<div class="grid gap-4 lg:grid-cols-[1.6fr_1fr]">
    <div class="space-y-4">
        @include('partials.order-invoice', ['order' => $order])
        @include('partials.order-receipt', ['order' => $order, 'receiptRoute' => route('partner.orders.receipt', $order)])
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
    </div>
</div>
@endsection
