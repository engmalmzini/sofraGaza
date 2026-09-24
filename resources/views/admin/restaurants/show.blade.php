@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', $restaurant->name)

@section('actions')
<a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="admin-btn admin-btn--ghost">تعديل</a>
<a href="{{ route('admin.restaurants.menu-items.index', $restaurant) }}" class="admin-btn admin-btn--primary">المنيو</a>
@endsection

@section('content')
<div class="grid gap-4 lg:grid-cols-[1.5fr_1fr]">
    <section class="admin-card space-y-2 text-sm leading-7">
        <h2>بيانات المطعم</h2>
        <div>النوع: {{ $restaurant->typeLabel() }} · {{ $restaurant->cuisineLabel() }}</div>
        <div>الهاتف: {{ $restaurant->phone ?: '—' }}</div>
        <div>المنطقة: {{ $restaurant->areaLabel() }}</div>
        <div>العنوان: {{ $restaurant->address ?: '—' }}</div>
        <div>ساعات العمل: {{ $restaurant->hoursLabel() }}</div>
        <div>الرخصة: {{ $restaurant->license_number ?: '—' }}</div>
        <div>أصناف المنيو: {{ $restaurant->menu_items_count }}</div>
        <div>اكتساب النقاط: {{ $restaurant->points_per_amount ? 'كل '.$restaurant->points_per_amount.' ₪ = نقطة (خاص بالمطعم)' : 'حسب الإعداد العام' }}</div>
        <div>استبدال النقاط: {{ $restaurant->points_redeem_per_amount ? 'كل '.$restaurant->points_redeem_per_amount.' ₪ = نقطة (خاص بالمطعم)' : 'حسب الإعداد العام' }}</div>
        <p class="pt-2 text-on-surface-variant">{{ $restaurant->description ?: 'لا يوجد وصف.' }}</p>
        @if($restaurant->imageUrl())
            <img src="{{ $restaurant->imageUrl() }}" alt="" class="mt-3 h-44 w-full rounded-xl object-cover">
        @endif
    </section>
    <div class="space-y-4">
        <section class="admin-card text-sm leading-7">
            <h2>صاحب المطعم</h2>
            @if($restaurant->owner)
                <div>{{ $restaurant->owner->name }}</div>
                <div>{{ $restaurant->owner->phone }}</div>
                <div>{{ $restaurant->owner->email ?: 'بدون بريد' }}</div>
                <div>رقم الهوية: {{ $restaurant->owner_national_id ?: '—' }}</div>
            @else
                <p class="text-on-surface-variant">هذا المطعم أُضيف من لوحة الإدارة وليس له حساب شريك.</p>
            @endif
        </section>
        <section class="admin-card">
            <h2>حالة التحقق</h2>
            <div class="mt-2">@include('admin.partials.pill', ['status' => $restaurant->verification_status, 'label' => $restaurant->verificationLabel()])</div>
            <p class="mt-2 text-sm text-on-surface-variant">
                {{ $restaurant->isVisible() ? 'ظاهر حالياً للزبائن.' : 'غير منشور على الصفحة الرئيسية.' }}
            </p>
            @if($restaurant->verified_at)
                <p class="mt-1 text-xs text-on-surface-variant">آخر تحقق: {{ $restaurant->verified_at->format('Y-m-d') }}{{ $restaurant->verifier ? ' بواسطة '.$restaurant->verifier->name : '' }}</p>
            @endif
            @if($restaurant->isRejected() && $restaurant->rejection_reason)
                <p class="mt-3 text-sm">سبب الرفض: {{ $restaurant->rejection_reason }}</p>
            @endif
            @if($restaurant->isPending())
                <form method="POST" action="{{ route('admin.restaurants.approve', $restaurant) }}" class="mt-4 space-y-2">
                    @csrf
                    <label class="block text-sm font-bold">مدة العرض بعد الموافقة (يوم)</label>
                    <input type="number" name="listing_days" min="7" max="365" value="{{ \App\Models\Setting::value('restaurant_listing_days', 90) }}">
                    <button class="admin-btn admin-btn--secondary w-full">الموافقة ونشر المطعم</button>
                </form>
                <form method="POST" action="{{ route('admin.restaurants.reject', $restaurant) }}" class="mt-3 space-y-2">
                    @csrf
                    <textarea name="rejection_reason" rows="3" placeholder="سبب الرفض لصاحب المطعم">{{ old('rejection_reason') }}</textarea>
                    <button class="admin-btn admin-btn--danger w-full">رفض الطلب</button>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
