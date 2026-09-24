@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'طلب #'.$order->id)

@section('content')
<div class="grid gap-4 lg:grid-cols-[1.6fr_1fr]">
    <div class="space-y-4">
        @include('partials.order-invoice', ['order' => $order])
        @include('partials.order-receipt', ['order' => $order, 'receiptRoute' => route('admin.orders.receipt', $order)])
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
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-3">
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
        <section class="admin-card">
            <h2>التوصيل</h2>
            <div class="mt-2 text-sm leading-7">
                <div>المندوب: {{ $order->courier?->name ?: 'بدون مندوب' }}</div>
                @if($order->courier)
                    <div>هاتف المندوب: <span dir="ltr">{{ $order->courier->phone }}</span></div>
                @endif
            </div>
            @if(in_array($order->status, ['preparing', 'delivering'], true))
                <form method="POST" action="{{ route('admin.delivery.assign', $order) }}" class="mt-3 space-y-2">
                    @csrf
                    <select name="courier_id" required>
                        <option value="">تعيين مندوب</option>
                        @foreach(\App\Models\User::query()->where('role', 'courier')->where(function ($query) {
                            $query->where('courier_status', \App\Models\User::COURIER_APPROVED)->orWhereNull('courier_status');
                        })->orderBy('name')->get() as $courier)
                            <option value="{{ $courier->id }}" @selected($order->courier_id === $courier->id)>{{ $courier->name }} — {{ $courier->isCourierBusy() ? 'مشغول' : 'فاضي' }}</option>
                        @endforeach
                    </select>
                    <button class="admin-btn admin-btn--secondary w-full">تعيين / نقل الطلب</button>
                </form>
                @if($order->courier_id)
                    <form method="POST" action="{{ route('admin.delivery.unassign', $order) }}" class="mt-2">
                        @csrf
                        <button class="admin-btn admin-btn--ghost w-full">إلغاء التعيين</button>
                    </form>
                @endif
            @endif
        </section>
    </div>
</div>
@endsection
