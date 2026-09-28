@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'الطلبات')

@section('content')
<div class="admin-toolbar flex-wrap gap-2">
    <div class="admin-chips">
        <a class="admin-chip {{ request('status') ? '' : 'is-active' }}" href="{{ route('admin.orders.index', request()->except('status')) }}">الكل</a>
        @foreach(\App\Models\Order::STATUSES as $key => $label)
            <a class="admin-chip {{ request('status') === $key ? 'is-active' : '' }}" href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}">{{ $label }}</a>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <button type="button" id="btn-toggle-sound" class="admin-btn admin-btn--ghost text-xs flex items-center gap-1" title="اختبار نغمة التنبيه">
            <span class="material-symbols-outlined text-primary text-[16px]">volume_up</span>
            <span id="sound-status-label">تنبيه صوتي: مفعل</span>
        </button>
    </div>
</div>
<div class="admin-table-wrap" data-admin-orders-table data-last-id="{{ $orders->max('id') ?? 0 }}" data-live-url="{{ route('admin.orders.live') }}">
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
        <tbody id="admin-orders-tbody">
            @include('admin.orders.partials.order-rows', ['orders' => $orders])
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
