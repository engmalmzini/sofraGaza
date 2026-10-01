@php
    $routeName = $routeName ?? 'admin.finance.index';
    $query = $periodQuery ?? [];
@endphp
<nav class="finance-tabs" aria-label="أقسام المالية">
    <a href="{{ route('admin.finance.index', $query) }}" class="finance-tab {{ request()->routeIs('admin.finance.index') ? 'is-active' : '' }}">
        <span class="material-symbols-outlined">account_balance</span>
        نظرة عامة
    </a>
    <a href="{{ route('admin.finance.orders', $query) }}" class="finance-tab {{ request()->routeIs('admin.finance.orders') ? 'is-active' : '' }}">
        <span class="material-symbols-outlined">receipt_long</span>
        مالية الطلبات
    </a>
    <a href="{{ route('admin.finance.restaurants', $query) }}" class="finance-tab {{ request()->routeIs('admin.finance.restaurants', 'admin.finance.restaurants.show') ? 'is-active' : '' }}">
        <span class="material-symbols-outlined">storefront</span>
        مالية المطاعم
    </a>
</nav>

<form method="GET" action="{{ route($routeName, isset($restaurant) ? ['restaurant' => $restaurant] : []) }}" class="finance-period admin-card">
    <div>
        <span class="finance-period__label">الفترة</span>
        <div class="admin-chips">
            <a class="admin-chip {{ ($period['key'] ?? '') === 'day' ? 'is-active' : '' }}" href="{{ route($routeName, isset($restaurant) ? ['restaurant' => $restaurant, 'period' => 'day'] : ['period' => 'day']) }}">يومي</a>
            <a class="admin-chip {{ ($period['key'] ?? '') === 'week' ? 'is-active' : '' }}" href="{{ route($routeName, isset($restaurant) ? ['restaurant' => $restaurant, 'period' => 'week'] : ['period' => 'week']) }}">أسبوعي</a>
            <a class="admin-chip {{ ($period['key'] ?? '') === 'month' ? 'is-active' : '' }}" href="{{ route($routeName, isset($restaurant) ? ['restaurant' => $restaurant, 'period' => 'month'] : ['period' => 'month']) }}">شهري</a>
        </div>
    </div>
    <label>
        <span>يوم محدد</span>
        <input type="date" name="date" value="{{ $period['key'] === 'day' || $period['key'] === 'week' ? $period['start']->toDateString() : '' }}">
        <input type="hidden" name="period" id="finance-period-key" value="{{ $period['key'] }}">
    </label>
    <label>
        <span>شهر سابق</span>
        <input type="month" name="month" value="{{ $period['key'] === 'month' ? $period['start']->format('Y-m') : '' }}">
    </label>
    <label>
        <span>من</span>
        <input type="date" name="from" value="{{ $period['key'] === 'custom' ? $period['start']->toDateString() : '' }}">
    </label>
    <label>
        <span>إلى</span>
        <input type="date" name="to" value="{{ $period['key'] === 'custom' ? $period['end']->toDateString() : '' }}">
    </label>
    <button type="submit" class="admin-btn admin-btn--primary text-xs" onclick="(function (form) { if (form.from.value && form.to.value) { form.period.value = 'custom'; } else if (form.month.value && !form.date.value) { form.period.value = 'month'; } else if (form.date.value && form.period.value !== 'week') { form.period.value = 'day'; } })(this.form)">عرض</button>
</form>
<p class="finance-period__hint">الفترة الحالية: <strong>{{ $period['label'] }}</strong></p>
