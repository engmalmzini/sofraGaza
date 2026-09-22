@extends('layouts.partner')

@section('title', 'تحقق البطاقة')

@section('content')
<section class="admin-card">
    <h2>تحقق من بطاقة العضوية</h2>
    <p class="mt-1 text-sm text-on-surface-variant">إذا الزبون متواجد في المطعم، اطلب البطاقة وأدخل رقمها. يظهر الخصم الواجب تطبيقه على الفاتورة.</p>
    <form method="GET" action="{{ route('partner.cards.show') }}" class="mt-4 flex flex-wrap gap-2">
        <input name="q" value="{{ $query }}" placeholder="SG-00001" class="flex-1 min-w-52 rounded-xl" autocomplete="off" inputmode="text">
        <button class="admin-btn admin-btn--primary">تحقق</button>
    </form>
</section>

@if($searched)
    @if($subscription)
        <section class="admin-card mt-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-on-surface-variant">بطاقة سارية</p>
                    <h2 class="mt-1">{{ $subscription->cardNumber() }}</h2>
                </div>
                @include('admin.partials.pill', ['status' => 'approved', 'label' => 'سارية'])
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm leading-7">
                <div>الزبون: <strong>{{ $subscription->user->name }}</strong></div>
                <div>العضوية: <strong>{{ $subscription->membership->name }}</strong></div>
                <div>صالحة حتى: <strong>{{ $subscription->ends_at?->format('Y/m/d') }}</strong></div>
                <div>المتبقي: <strong>{{ $subscription->daysRemaining() }} يوم</strong></div>
            </div>
            <div class="mt-5 rounded-2xl bg-secondary-fixed text-on-secondary-fixed px-4 py-4">
                <div class="text-sm font-bold">الخصم داخل المطعم</div>
                <p class="mt-1 text-3xl font-extrabold">{{ $subscription->membership->discount_percent }}%</p>
                @if($subscription->membership->free_delivery)
                    <p class="mt-1 text-sm font-semibold">مع توصيل مجاني لطلبات المنصة.</p>
                @endif
                <p class="mt-2 text-sm">طبّق هذا الخصم على فاتورة الزبون بعد مطابقة الاسم مع البطاقة.</p>
            </div>
        </section>
    @else
        <section class="admin-card mt-4">
            <p class="text-sm">لا توجد عضوية سارية لهذا الرقم. تأكد من رقم البطاقة أو أن الاشتراك لم ينتهِ.</p>
        </section>
    @endif
@endif
@endsection
