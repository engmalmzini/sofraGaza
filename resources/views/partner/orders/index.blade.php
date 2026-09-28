@extends('layouts.partner')

@section('title', 'الطلبات الواردة')

@section('content')
<div class="admin-toolbar flex-wrap gap-3">
    <div class="flex items-center gap-3">
        <h1 class="text-xl font-bold text-on-surface">الطلبات الواردة</h1>
        <span id="active-orders-counter" class="admin-pill {{ $activeCount > 0 ? 'admin-pill--ok' : '' }}">
            {{ $activeCount }} قيد المتابعة
        </span>
    </div>

    {{-- Sound notification button --}}
    <div class="flex items-center gap-2">
        <button type="button" id="btn-toggle-sound" class="admin-btn admin-btn--ghost text-xs flex items-center gap-1.5" title="انقر لتشغيل وتجربة نغمة التنبيه">
            <span class="material-symbols-outlined text-primary text-[18px]">volume_up</span>
            <span id="sound-status-label">تنبيه الصوت: مفعل (انقر للتجربة)</span>
        </button>
    </div>
</div>

{{-- Filter chips --}}
<div class="admin-toolbar mt-3">
    <div class="admin-chips">
        <a class="admin-chip {{ request('status') ? '' : 'is-active' }}" href="{{ route('partner.orders.index', request()->except('status')) }}">الكل</a>
        @foreach(\App\Models\Order::STATUSES as $key => $label)
            <a class="admin-chip {{ request('status') === $key ? 'is-active' : '' }}" href="{{ route('partner.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

{{-- Real-time Orders Container --}}
<div class="mt-4" id="partner-orders-wrapper" data-last-id="{{ $orders->max('id') ?? 0 }}" data-live-url="{{ route('partner.orders.live') }}">
    <div id="partner-orders-container" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @include('partner.orders.partials.order-cards', ['orders' => $orders, 'restaurant' => $restaurant])
    </div>
</div>

<div class="mt-4">
    {{ $orders->links() }}
</div>
@endsection
