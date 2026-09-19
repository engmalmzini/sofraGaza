@extends('layouts.public')

@section('title', 'العضويات والمكافآت')

@section('content')
<div class="mx-auto max-w-5xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <div class="mb-6">
        <div class="inline-flex items-center gap-1.5 text-amber-700 text-[12px] font-bold tracking-wider mb-1">
            <span class="material-symbols-outlined text-[16px]">stars</span>
            <span>برنامج الولاء</span>
        </div>
        <h1 class="font-headline-md text-2xl lg:text-[28px] font-bold text-stone-900">العضويات والمكافآت</h1>
        <p class="text-sm text-on-surface-variant mt-1">اشترك لتحصل على خصم فوري، توصيل مجاني، ونقاط مضاعفة.</p>
    </div>
    @if($current)
        <p class="mb-3 rounded-2xl bg-secondary-fixed text-on-secondary-fixed px-4 py-3 text-sm font-medium">عضويتك الحالية: {{ $current->membership->name }} — تنتهي بعد {{ $current->daysRemaining() }} يوم.</p>
    @endif
    @if($pending)
        <p class="mb-3 rounded-2xl bg-tertiary-fixed text-tertiary px-4 py-3 text-sm font-medium">طلب عضوية {{ $pending->membership->name }} بانتظار مراجعة الحوالة.</p>
    @endif
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($memberships as $membership)
            <article class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs">
                <h2 class="text-xl font-bold text-on-surface">{{ $membership->name }}</h2>
                <div class="mt-2 flex items-center gap-1.5 text-3xl font-bold text-primary">{{ number_format($membership->monthly_price) }} <span class="ils">₪</span> <span class="text-sm font-medium text-on-surface-variant">شهرياً</span></div>
                <ul class="mt-4 space-y-2 text-sm text-on-surface-variant">
                    @foreach($membership->benefitsList() as $benefit)
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-secondary text-[18px]">check_circle</span>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
                @auth
                    <form method="POST" action="{{ route('memberships.subscribe', $membership) }}" enctype="multipart/form-data" class="mt-5 space-y-3">
                        @csrf
                        <input type="file" name="receipt" accept="image/*" class="w-full rounded-xl border border-dashed border-outline/40 p-2 text-sm">
                        <button class="w-full rounded-xl bg-primary hover:bg-primary-container text-on-primary py-3 font-semibold">اشترك وأرفق الحوالة</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mt-5 inline-flex rounded-xl bg-primary px-4 py-2.5 text-on-primary font-semibold">سجّل دخول للاشتراك</a>
                @endauth
            </article>
        @endforeach
    </div>
</div>
@endsection
