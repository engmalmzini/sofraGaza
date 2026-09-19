@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', $user->name)

@section('content')
<div class="admin-grid-2">
    <section class="admin-card">
        <div class="text-sm leading-7">
            <div>الهاتف: {{ $user->phone }}</div>
            <div>البريد: {{ $user->email ?: '—' }}</div>
        </div>
        <div class="mt-3 text-2xl font-extrabold">{{ $user->points_balance }} نقطة</div>
        <form method="POST" action="{{ route('admin.users.points', $user) }}" class="mt-4 space-y-2">
            @csrf
            <input type="number" name="points" placeholder="مثلاً 10 أو -5">
            <input name="reason" placeholder="سبب التعديل">
            <button class="admin-btn admin-btn--primary">تعديل النقاط يدوياً</button>
        </form>
    </section>
    <section class="admin-card">
        <h2>العضويات</h2>
        @forelse($user->subscriptions as $subscription)
            <div class="admin-row">
                <span>{{ $subscription->membership->name }}</span>
                @include('admin.partials.pill', ['status' => $subscription->status, 'label' => $subscription->statusLabel()])
            </div>
        @empty
            <p class="mt-3 text-sm text-on-surface-variant">لا توجد عضويات.</p>
        @endforelse
        <h2 class="mt-5">سجل النقاط</h2>
        @forelse($user->pointTransactions->take(8) as $row)
            <div class="admin-row">
                <span>{{ $row->description }}</span>
                <strong>{{ $row->points }}</strong>
            </div>
        @empty
            <p class="mt-3 text-sm text-on-surface-variant">لا يوجد سجل.</p>
        @endforelse
    </section>
</div>
<section class="admin-card mt-4">
    <h2>الطلبات</h2>
    @forelse($user->orders as $order)
        <a class="admin-row" href="{{ route('admin.orders.show', $order) }}">
            <span>#{{ $order->id }} {{ $order->restaurant->name }}</span>
            @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
        </a>
    @empty
        <p class="mt-3 text-sm text-on-surface-variant">لا توجد طلبات.</p>
    @endforelse
</section>
@endsection
