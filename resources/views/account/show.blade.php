@extends('layouts.public')

@section('title', 'حسابي')

@section('content')
<div class="mx-auto max-w-5xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <h1 class="font-headline-md text-2xl font-bold text-stone-900">مرحباً {{ $user->name }}</h1>

    @if($subscription?->isExpiringSoon())
        <p class="mt-4 rounded-2xl bg-tertiary-fixed text-tertiary px-4 py-3 text-sm font-medium">
            عضويتك تنتهي خلال {{ $subscription->daysRemaining() }} أيام. جدّد الاشتراك حتى لا تفقد الخصم والنقاط الإضافية.
            <a href="{{ route('memberships.index') }}" class="font-bold underline">تجديد الآن</a>
        </p>
    @endif

    @if($subscription)
        <div class="mt-6 max-w-xl">
            @include('partials.membership-card', ['subscription' => $subscription, 'holder' => $user])
        </div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <a href="{{ route('account.points') }}" class="rounded-2xl bg-secondary-fixed text-on-secondary-fixed p-5">
            <div class="text-sm opacity-80">رصيد النقاط</div>
            <div class="mt-2 text-4xl font-bold">{{ $user->points_balance }}</div>
            <div class="mt-3 text-sm font-medium flex items-center gap-1">سجل النقاط <span class="material-symbols-outlined text-[16px]">arrow_back</span></div>
        </a>
        <div class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs">
            <div class="text-sm text-on-surface-variant">العضوية</div>
            <div class="mt-2 text-xl font-bold text-on-surface">{{ $membership->name ?? 'بدون اشتراك' }}</div>
            @if($subscription)
                <p class="mt-2 text-sm text-on-surface-variant">تنتهي بعد {{ $subscription->daysRemaining() }} يوم</p>
            @endif
            <a href="{{ route('memberships.index') }}" class="mt-3 inline-block text-sm font-bold text-primary">إدارة الاشتراك</a>
        </div>
        <div class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs">
            <div class="text-sm text-on-surface-variant mb-3">روابط سريعة</div>
            <div class="space-y-2 text-sm font-bold">
                <a class="flex items-center gap-2 text-on-surface" href="{{ route('account.orders') }}"><span class="material-symbols-outlined text-primary text-[18px]">receipt_long</span> طلباتي</a>
                <a class="flex items-center gap-2 text-on-surface" href="{{ route('account.addresses') }}"><span class="material-symbols-outlined text-primary text-[18px]">location_on</span> العناوين</a>
                <a class="flex items-center gap-2 text-on-surface" href="{{ $user->notificationsInboxRoute() }}"><span class="material-symbols-outlined text-primary text-[18px]">notifications</span> الإشعارات ({{ $unreadNotifications }})</a>
                @if($user->isRestaurantOwner())
                    <a class="flex items-center gap-2 text-on-surface" href="{{ route('partner.dashboard') }}"><span class="material-symbols-outlined text-primary text-[18px]">storefront</span> لوحة المطعم</a>
                @endif
                <a class="flex items-center gap-2 text-on-surface" href="{{ route('redeem.create') }}"><span class="material-symbols-outlined text-primary text-[18px]">redeem</span> استبدال النقاط</a>
            </div>
        </div>
    </div>
    <h2 class="mt-10 font-headline-sm text-xl font-bold text-on-surface">آخر الطلبات</h2>
    <div class="mt-4 space-y-3">
        @forelse($recentOrders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="flex items-center justify-between rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs">
                <span class="font-medium">طلب #{{ $order->id }} — {{ $order->restaurant->name }}</span>
                <span class="text-sm rounded-full bg-surface-container-low px-3 py-1">{{ $order->statusLabel() }}</span>
            </a>
        @empty
            <p class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-6 text-on-surface-variant">لا توجد طلبات بعد.</p>
        @endforelse
    </div>
    <form method="POST" action="{{ route('logout') }}" class="mt-8">@csrf<button class="text-sm text-primary font-semibold">تسجيل الخروج</button></form>
</div>
@endsection
