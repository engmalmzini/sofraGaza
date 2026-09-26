@extends('layouts.partner')

@section('title', 'اشتراك الظهور')

@section('content')
@if($locked)
    <div class="admin-alert {{ $restaurant->panel_suspended ? 'admin-alert--err' : 'admin-alert--wait' }}">
        <strong>قم بتجديد الاشتراك</strong>
        <p class="mt-1">{{ $restaurant->panelLockMessage() }}</p>
    </div>
@elseif($listing = $current)
    @if($listing->isExpiringSoon())
        <div class="admin-alert admin-alert--wait">
            باقي {{ $listing->daysRemaining() }} أيام على انتهاء باقة {{ $listing->plan->name }}. جدّد من الأسفل حتى لا تُوقف اللوحة.
        </div>
    @endif
@endif

@if($current)
    <section class="admin-card mb-4">
        <h2>اشتراكك الحالي</h2>
        <div class="admin-metrics mt-3">
            <article class="admin-metric admin-metric--accent">
                <span>الباقة</span>
                <strong class="!text-xl">{{ $current->plan->name }}</strong>
            </article>
            <article class="admin-metric">
                <span>المدة</span>
                <strong class="!text-xl">{{ $current->plan->durationLabel() }}</strong>
            </article>
            <article class="admin-metric">
                <span>باقي</span>
                <strong class="!text-xl">{{ $current->daysRemaining() }} يوم</strong>
            </article>
        </div>
        <p class="mt-3 text-sm text-on-surface-variant">
            من {{ $current->starts_at?->format('Y-m-d') }} إلى {{ $current->ends_at?->format('Y-m-d') }}
            · {{ number_format($current->amount, 0) }} <span class="ils">₪</span>
        </p>
        @if($restaurant->panel_suspended)
            <p class="mt-2 text-sm">اللوحة موقوفة من الإدارة. بياناتك محفوظة. بعد تأكيد حوالة التجديد نعيد الفتح.</p>
        @endif
    </section>
@endif

@if($pending)
    <p class="mb-4 admin-alert admin-alert--wait">رفع إشعار حوالة باقة {{ $pending->plan->name }} بقيمة {{ number_format($pending->amount, 0) }} ₪ — بانتظار تأكيد الإدارة.</p>
@endif

<h2 class="mb-3 text-lg font-bold">{{ $current ? 'تجديد أو ترقية' : 'اختر الباقة' }}</h2>
<div class="grid gap-4 md:grid-cols-3">
    @foreach($plans as $plan)
        <article class="admin-card">
            <h2>{{ $plan->name }}</h2>
            <div class="mt-2 text-3xl font-bold text-primary">{{ number_format($plan->price, 0) }} <span class="ils">₪</span></div>
            <p class="mt-1 text-sm text-on-surface-variant">{{ $plan->durationLabel() }}</p>
            <p class="mt-3 text-sm leading-7">{{ $plan->description }}</p>
            @if($pending)
                <p class="mt-4 text-sm text-on-surface-variant">لا يمكن إرسال حوالة جديدة قبل مراجعة الحالية.</p>
            @else
                <form method="POST" action="{{ route('partner.subscription.store', $plan) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <input type="file" name="receipt" accept="image/*" class="w-full rounded-xl border border-dashed border-outline/40 p-2 text-sm">
                    <button class="admin-btn admin-btn--primary w-full">ادفع وأرفق الحوالة</button>
                </form>
            @endif
        </article>
    @endforeach
</div>
@endsection
