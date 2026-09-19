@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'الطلبات')

@section('content')
<div class="admin-toolbar">
    <div class="admin-chips">
        <a class="admin-chip {{ request('status') ? '' : 'is-active' }}" href="{{ route('admin.orders.index', request()->except('status')) }}">الكل</a>
        @foreach(\App\Models\Order::STATUSES as $key => $label)
            <a class="admin-chip {{ request('status') === $key ? 'is-active' : '' }}" href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}">{{ $label }}</a>
        @endforeach
    </div>
</div>
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الزبون</th>
                <th>المطعم</th>
                <th>المبلغ</th>
                <th>النوع</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><a class="font-bold text-primary" href="{{ route('admin.orders.show', $order) }}">{{ $order->id }}</a></td>
                    <td>{{ $order->user->name }}<div class="text-xs text-on-surface-variant">{{ $order->phone }}</div></td>
                    <td>{{ $order->restaurant->name }}</td>
                    <td>{{ number_format($order->total, 2) }} <span class="ils">₪</span></td>
                    <td>{{ $order->type === 'redemption' ? 'استبدال نقاط' : 'شراء' }}</td>
                    <td>@include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])</td>
                </tr>
            @empty
                <tr><td colspan="6">لا توجد طلبات مطابقة.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
