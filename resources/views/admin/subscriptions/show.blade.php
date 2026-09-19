@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', 'طلب عضوية')

@section('content')
<div class="admin-grid-2">
    <section class="admin-card leading-8">
        <div>الزبون: {{ $subscription->user->name }} — {{ $subscription->user->phone }}</div>
        <div>العضوية: {{ $subscription->membership->name }}</div>
        <div>المبلغ: {{ number_format($subscription->amount, 2) }} <span class="ils">₪</span></div>
        <div class="mt-2">@include('admin.partials.pill', ['status' => $subscription->status, 'label' => $subscription->statusLabel()])</div>
        @if($subscription->ends_at)
            <div>من {{ $subscription->starts_at->format('Y-m-d') }} إلى {{ $subscription->ends_at->format('Y-m-d') }}</div>
        @endif
        @if($subscription->status === 'pending')
            <form method="POST" action="{{ route('admin.subscriptions.approve', $subscription) }}" class="mt-4">
                @csrf
                <button class="admin-btn admin-btn--secondary">تفعيل 30 يوماً</button>
            </form>
            <form method="POST" action="{{ route('admin.subscriptions.reject', $subscription) }}" class="mt-3">
                @csrf
                <textarea name="rejection_reason" placeholder="سبب الرفض"></textarea>
                <button class="admin-btn admin-btn--danger mt-2">رفض</button>
            </form>
        @endif
    </section>
    @if($subscription->receiptUrl())
        <section class="admin-card">
            <h2>إشعار الحوالة</h2>
            <img src="{{ $subscription->receiptUrl() }}" class="mt-3 max-h-96 rounded-xl object-contain" alt="حوالة">
        </section>
    @endif
</div>
@endsection
