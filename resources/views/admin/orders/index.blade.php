@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'الطلبات')

@section('content')
<div class="admin-toolbar flex-wrap gap-2 justify-between items-center">
    <div class="admin-chips">
        <a class="admin-chip {{ request('status') ? '' : 'is-active' }}" href="{{ route('admin.orders.index', request()->except('status')) }}">الكل</a>
        @foreach(\App\Models\Order::STATUSES as $key => $label)
            <a class="admin-chip {{ request('status') === $key ? 'is-active' : '' }}" href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="flex items-center gap-2">
        <!-- View Toggle -->
        <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold">
            <button type="button" id="orders-toggle-board" onclick="toggleOrdersPageView('board')" class="px-3 py-1.5 rounded-lg transition-all {{ request('status') ? 'text-slate-500 hover:text-slate-900' : 'bg-white text-slate-900 shadow-sm' }} flex items-center gap-1">
                <span class="material-symbols-outlined !text-sm text-primary">view_kanban</span>
                <span>أعمدة</span>
            </button>
            <button type="button" id="orders-toggle-table" onclick="toggleOrdersPageView('table')" class="px-3 py-1.5 rounded-lg transition-all {{ request('status') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }} flex items-center gap-1">
                <span class="material-symbols-outlined !text-sm">table_rows</span>
                <span>جدول</span>
            </button>
        </div>

        <button type="button" id="btn-toggle-sound" class="admin-btn admin-btn--ghost text-xs flex items-center gap-1" title="اختبار نغمة التنبيه">
            <span class="material-symbols-outlined text-primary text-[16px]">volume_up</span>
            <span id="sound-status-label">تنبيه صوتي: مفعل</span>
        </button>
    </div>
</div>

<!-- 1. Board View (Columns by status) -->
<div id="orders-page-board" class="{{ request('status') ? 'hidden' : '' }} mb-6">
    @include('admin.orders.partials.order-board', ['ordersByStatus' => $ordersByStatus])
</div>

<!-- 2. Table View -->
<div id="orders-page-table" class="{{ request('status') ? '' : 'hidden' }}">
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
                    <th class="text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody id="admin-orders-tbody">
                @include('admin.orders.partials.order-rows', ['orders' => $orders])
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>

<script>
    function toggleOrdersPageView(view) {
        const boardEl = document.getElementById('orders-page-board');
        const tableEl = document.getElementById('orders-page-table');
        const btnBoard = document.getElementById('orders-toggle-board');
        const btnTable = document.getElementById('orders-toggle-table');

        if (view === 'board') {
            boardEl.classList.remove('hidden');
            tableEl.classList.add('hidden');
            btnBoard.className = 'px-3 py-1.5 rounded-lg transition-all bg-white text-slate-900 shadow-sm flex items-center gap-1';
            btnTable.className = 'px-3 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-900 flex items-center gap-1';
        } else {
            boardEl.classList.add('hidden');
            tableEl.classList.remove('hidden');
            btnTable.className = 'px-3 py-1.5 rounded-lg transition-all bg-white text-slate-900 shadow-sm flex items-center gap-1';
            btnBoard.className = 'px-3 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-900 flex items-center gap-1';
        }
    }
</script>
@endsection
