@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', $courier->name)

@section('actions')
<a href="{{ route('admin.delivery.index') }}" class="admin-btn admin-btn--ghost">عودة للتوصيل</a>
<a href="tel:{{ $courier->phone }}" class="admin-btn admin-btn--secondary">اتصال</a>
@endsection

@section('content')
@php $current = $courier->activeDelivery(); @endphp
<div class="grid gap-4 lg:grid-cols-[1.2fr_1fr]">
    <section class="admin-card space-y-2 text-sm leading-7">
        <h2>حالة المندوب</h2>
        <div>@include('admin.partials.pill', ['status' => $courier->isCourierPending() ? 'pending' : ($courier->isCourierRejected() ? 'rejected' : ($current ? 'delivering' : 'delivered')), 'label' => $courier->isCourierApproved() ? ($current ? 'مشغول' : 'فاضي / مقبول') : $courier->courierStatusLabel()])</div>
        <div>الهاتف: <span dir="ltr">{{ $courier->phone }}</span></div>
        <div>نوع الدراجة: {{ $courier->bikeTypeLabel() }}</div>
        <div>البريد: {{ $courier->email ?: '—' }}</div>
        @if($courier->photoUrl() || $courier->bikePhotoUrl())
            <div class="grid gap-3 sm:grid-cols-2 pt-2">
                @if($courier->photoUrl())
                    <div>
                        <div class="text-xs text-on-surface-variant mb-1">صورة شخصية</div>
                        <img src="{{ $courier->photoUrl() }}" alt="" class="h-40 w-full rounded-xl object-cover">
                    </div>
                @endif
                @if($courier->bikePhotoUrl())
                    <div>
                        <div class="text-xs text-on-surface-variant mb-1">صورة الدراجة</div>
                        <img src="{{ $courier->bikePhotoUrl() }}" alt="" class="h-40 w-full rounded-xl object-cover">
                    </div>
                @endif
            </div>
        @endif
        @if($courier->isCourierPending())
            <form method="POST" action="{{ route('admin.delivery.approve', $courier) }}" class="pt-4">
                @csrf
                <button class="admin-btn admin-btn--secondary w-full">قبول المندوب وإرسال رابط اللوحة</button>
            </form>
            <form method="POST" action="{{ route('admin.delivery.reject', $courier) }}" class="mt-3 space-y-2">
                @csrf
                <textarea name="rejection_reason" rows="3" placeholder="سبب الرفض">{{ old('rejection_reason') }}</textarea>
                <button class="admin-btn admin-btn--danger w-full">رفض الطلب</button>
            </form>
        @elseif($courier->isCourierRejected())
            <p class="pt-2">سبب الرفض: {{ $courier->courier_rejection_reason ?: '—' }}</p>
        @endif
        @if($current)
            <p class="pt-2">الطلب الحالي: <a class="font-bold text-primary" href="{{ route('admin.orders.show', $current) }}">#{{ $current->id }}</a> — {{ $current->restaurant?->name }}</p>
            <p>التسليم إلى: {{ $current->address_details }}</p>
        @endif
        <h2 class="pt-4">طلبات قيد التوصيل</h2>
        @forelse($active as $order)
            <a class="admin-row" href="{{ route('admin.orders.show', $order) }}">
                <span>#{{ $order->id }} {{ $order->restaurant?->name }} → {{ $order->user?->name }}</span>
                @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
            </a>
        @empty
            <p class="text-on-surface-variant">لا يوجد طلب معه الآن.</p>
        @endforelse
        <h2 class="pt-4">سجل التوصيل</h2>
        @forelse($history as $order)
            <a class="admin-row" href="{{ route('admin.orders.show', $order) }}">
                <span>#{{ $order->id }} {{ $order->restaurant?->name }}</span>
                @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
            </a>
        @empty
            <p class="text-on-surface-variant">لا يوجد سجل بعد.</p>
        @endforelse
    </section>
    <section class="admin-card">
        <h2>تعديل البيانات</h2>
        <form method="POST" action="{{ route('admin.delivery.update', $courier) }}" class="admin-form mt-4 space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-bold">الاسم</label>
                <input name="name" value="{{ old('name', $courier->name) }}" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">الهاتف</label>
                <input name="phone" value="{{ old('phone', $courier->phone) }}" required inputmode="numeric">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">كلمة مرور جديدة (اختياري)</label>
                <input type="password" name="password" minlength="6">
            </div>
            <button class="admin-btn admin-btn--primary">حفظ</button>
        </form>
    </section>
</div>
@endsection
