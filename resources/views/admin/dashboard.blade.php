@extends('layouts.admin')

@section('kicker', 'ملخص اليوم')
@section('title', 'نظرة عامة')

@section('content')
<div class="admin-metrics">
    <article class="admin-metric admin-metric--accent">
        <span>طلبات بانتظار التأكيد</span>
        <strong>{{ $pendingOrders }}</strong>
    </article>
    <article class="admin-metric">
        <span>طلبات اليوم</span>
        <strong>{{ $todayOrders }}</strong>
    </article>
    <article class="admin-metric">
        <span>مبيعات الشهر المسلّمة</span>
        <strong>{{ number_format($monthSales, 0) }} <span class="ils">₪</span></strong>
    </article>
    <article class="admin-metric">
        <span>زبائن / عضويات معلّقة</span>
        <strong>{{ $customers }} / {{ $pendingSubscriptions }}</strong>
    </article>
</div>

<div class="admin-grid-2">
    <section class="admin-card">
        <h2>مهام تحتاج إجراء</h2>
        @forelse($pendingRestaurants as $restaurant)
            <a href="{{ route('admin.restaurants.show', $restaurant) }}" class="admin-row">
                <span>{{ $restaurant->name }}</span>
                <span class="admin-pill admin-pill--wait">جاري التحقق</span>
            </a>
        @empty
            @if($expiringRestaurants->isEmpty())
                <p class="mt-3 text-sm text-on-surface-variant">لا توجد تنبيهات حالياً.</p>
            @endif
        @endforelse
        @foreach($expiringRestaurants as $restaurant)
            <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="admin-row">
                <span>{{ $restaurant->name }}</span>
                <span class="admin-pill admin-pill--wait">باقي {{ $restaurant->daysRemaining() }} يوم</span>
            </a>
        @endforeach
        @if($expiredRestaurants->isNotEmpty())
            <h2 class="mt-5">عروض منتهية</h2>
            @foreach($expiredRestaurants as $restaurant)
                <div class="admin-row">
                    <span>{{ $restaurant->name }}</span>
                    <span class="admin-pill admin-pill--off">{{ $restaurant->expires_at->format('Y-m-d') }}</span>
                </div>
            @endforeach
        @endif
    </section>
    <section class="admin-card">
        <h2>عضويات قرب الانتهاء</h2>
        @forelse($expiringMemberships as $subscription)
            <div class="admin-row">
                <span>{{ $subscription->user->name }} — {{ $subscription->membership->name }}</span>
                <span class="admin-pill admin-pill--wait">باقي {{ $subscription->daysRemaining() }} يوم</span>
            </div>
        @empty
            <p class="mt-3 text-sm text-on-surface-variant">لا توجد عضويات على وشك الانتهاء.</p>
        @endforelse
    </section>
</div>

<section class="admin-card">
    <div class="admin-toolbar">
        <h2>آخر الطلبات</h2>
        <a class="admin-btn admin-btn--ghost" href="{{ route('admin.orders.index') }}">عرض الكل</a>
    </div>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الزبون</th>
                    <th>المطعم</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestOrders as $order)
                    <tr>
                        <td><a class="font-bold text-primary" href="{{ route('admin.orders.show', $order) }}">{{ $order->id }}</a></td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->restaurant->name }}</td>
                        <td>{{ number_format($order->total, 2) }} <span class="ils">₪</span></td>
                        <td>@include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])</td>
                    </tr>
                @empty
                    <tr><td colspan="5">لا توجد طلبات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
