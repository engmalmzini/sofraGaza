@extends('layouts.admin')

@section('kicker', 'لوحة التحكم والعمليات')
@section('title', 'نظرة عامة على المنصة')

@section('actions')
    <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn--primary">
        <span class="material-symbols-outlined">receipt_long</span>
        <span>الطلبات @if($pendingOrders > 0)({{ $pendingOrders }})@endif</span>
    </a>
    <a href="{{ route('admin.restaurants.create') }}" class="admin-btn admin-btn--secondary">
        <span class="material-symbols-outlined">add_business</span>
        <span>إضافة مطعم</span>
    </a>
    <a href="{{ route('admin.delivery.index') }}" class="admin-btn admin-btn--ghost">
        <span class="material-symbols-outlined">two_wheeler</span>
        <span>المندوبين</span>
    </a>
@endsection

@section('content')
<!-- Top Operational Metrics (4 Rich Cards with Icons & Context) -->
<div class="admin-metrics">
    <article class="admin-metric {{ $pendingOrders > 0 ? 'admin-metric--accent' : '' }}">
        <div class="admin-metric__top">
            <span>طلبات بانتظار التأكيد</span>
            <div class="admin-metric__icon">
                <span class="material-symbols-outlined">pending_actions</span>
            </div>
        </div>
        <strong>{{ $pendingOrders }}</strong>
        <span class="admin-metric__note">
            @if($pendingOrders > 0)
                <a href="{{ route('admin.orders.index', ['status' => 'pending_confirmation']) }}" class="text-primary font-bold hover:underline">
                    ⚠️ تتطلب مراجعة واعتماد فوري
                </a>
            @else
                <span class="text-slate-500">✅ تم معالجة كافة الطلبات الواردة</span>
            @endif
        </span>
    </article>

    <article class="admin-metric">
        <div class="admin-metric__top">
            <span>مبيعات اليوم والنشاط</span>
            <div class="admin-metric__icon">
                <span class="material-symbols-outlined">payments</span>
            </div>
        </div>
        <strong>{{ number_format($todaySales, 0) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note text-slate-500">
            {{ $todayOrders }} طلب اليوم • الشهر: {{ number_format($monthSales, 0) }} ₪
        </span>
    </article>

    <article class="admin-metric">
        <div class="admin-metric__top">
            <span>شبكة المطاعم والمندوبين</span>
            <div class="admin-metric__icon">
                <span class="material-symbols-outlined">storefront</span>
            </div>
        </div>
        <strong>{{ $activeRestaurants }} <span class="text-sm font-semibold text-slate-500">مطعم نشط</span></strong>
        <span class="admin-metric__note text-slate-500">
            {{ $activeCouriers }} مندوب توصيل جاهز بالخدمة
        </span>
    </article>

    <article class="admin-metric">
        <div class="admin-metric__top">
            <span>الزبائن والعضويات</span>
            <div class="admin-metric__icon">
                <span class="material-symbols-outlined">group</span>
            </div>
        </div>
        <strong>{{ $customers }} <span class="text-sm font-semibold text-slate-500">زبون</span></strong>
        <span class="admin-metric__note">
            @if($pendingSubscriptions > 0)
                <a href="{{ route('admin.subscriptions.index') }}" class="text-primary font-bold hover:underline">
                    {{ $pendingSubscriptions }} طلب عضوية بانتظار الاعتماد
                </a>
            @else
                <span class="text-slate-500">العضويات والزبائن محدثة بالكامل</span>
            @endif
        </span>
    </article>
</div>

<!-- Center Section: Action Hub & Expiry Monitoring (2-Column Balanced Grid) -->
<div class="admin-grid-2">
    <!-- Right: Operational Approvals & Tasks Hub -->
    <section class="admin-card">
        <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">assignment</span>
                <h2 class="text-base font-bold text-slate-900">مركز المهام والموافقات العاجلة</h2>
            </div>
            @php
                $totalPendingTasks = $pendingRestaurants->count() + $pendingPayouts->count() + $pendingTopups->count() + $pendingSubscriptionsList->count();
            @endphp
            @if($totalPendingTasks > 0)
                <span class="admin-pill admin-pill--wait">{{ $totalPendingTasks }} مطلوب إجراء</span>
            @else
                <span class="admin-pill admin-pill--ok">الكل مكتمل</span>
            @endif
        </div>

        @if($totalPendingTasks === 0)
            <div class="py-8 text-center">
                <span class="material-symbols-outlined text-4xl text-emerald-500 mb-1">task_alt</span>
                <p class="font-bold text-slate-800">لا توجد مهام أو طلبات معلقة حالياً</p>
                <p class="text-xs text-slate-500 mt-1">كافة المطاعم، سحوبات المندوبين، وشحن المحافظ تم تدقيقها بالكامل.</p>
            </div>
        @else
            <!-- 1. Pending Restaurants -->
            @if($pendingRestaurants->isNotEmpty())
                <div class="admin-subheading">
                    <span>مطاعم جديدة بانتظار الاعتماد</span>
                    <span class="text-xs text-primary font-bold">{{ $pendingRestaurants->count() }} مطاعم</span>
                </div>
                <div class="admin-action-list">
                    @foreach($pendingRestaurants as $restaurant)
                        <div class="admin-action-item">
                            <div class="admin-action-item__main">
                                <span class="admin-action-item__title">{{ $restaurant->name }}</span>
                                <span class="admin-action-item__meta">
                                    المالك: {{ $restaurant->owner->name ?? 'غير محدد' }} • {{ $restaurant->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="admin-action-item__side">
                                <a href="{{ route('admin.restaurants.show', $restaurant) }}" class="admin-btn admin-btn--primary !min-h-[2rem] !py-1 !px-3 !text-xs">
                                    فحص واعتماد
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- 2. Pending Courier Payouts -->
            @if($pendingPayouts->isNotEmpty())
                <div class="admin-subheading">
                    <span>سحب أرباح المندوبين (كاش / محفظة)</span>
                    <span class="text-xs text-primary font-bold">{{ $pendingPayouts->count() }} طلبات</span>
                </div>
                <div class="admin-action-list">
                    @foreach($pendingPayouts as $payout)
                        <div class="admin-action-item">
                            <div class="admin-action-item__main">
                                <span class="admin-action-item__title">{{ $payout->user->name ?? 'مندوب' }}</span>
                                <span class="admin-action-item__meta">
                                    المطلوب: <strong class="text-slate-900">{{ number_format($payout->amount, 2) }} ₪</strong> • {{ $payout->methodLabel() }}
                                </span>
                            </div>
                            <div class="admin-action-item__side">
                                <a href="{{ route('admin.delivery.show', $payout->user_id) }}" class="admin-btn admin-btn--secondary !min-h-[2rem] !py-1 !px-3 !text-xs">
                                    مراجعة السحب
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- 3. Pending Wallet Topups -->
            @if($pendingTopups->isNotEmpty())
                <div class="admin-subheading">
                    <span>طلبات شحن رصيد المحفظة</span>
                    <span class="text-xs text-primary font-bold">{{ $pendingTopups->count() }} طلبات</span>
                </div>
                <div class="admin-action-list">
                    @foreach($pendingTopups as $topup)
                        <div class="admin-action-item">
                            <div class="admin-action-item__main">
                                <span class="admin-action-item__title">{{ $topup->user->name ?? 'زبون' }}</span>
                                <span class="admin-action-item__meta">
                                    المبلغ: <strong class="text-slate-900">{{ number_format($topup->amount, 2) }} ₪</strong> • {{ $topup->paymentMethodLabel() }}
                                </span>
                            </div>
                            <div class="admin-action-item__side">
                                <a href="{{ route('admin.wallet-topups.index') }}" class="admin-btn admin-btn--ghost !min-h-[2rem] !py-1 !px-3 !text-xs">
                                    فحص الإيصال
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- 4. Pending Membership Subscriptions -->
            @if($pendingSubscriptionsList->isNotEmpty())
                <div class="admin-subheading">
                    <span>طلبات ترقية العضويات</span>
                    <span class="text-xs text-primary font-bold">{{ $pendingSubscriptionsList->count() }} طلبات</span>
                </div>
                <div class="admin-action-list">
                    @foreach($pendingSubscriptionsList as $sub)
                        <div class="admin-action-item">
                            <div class="admin-action-item__main">
                                <span class="admin-action-item__title">{{ $sub->user->name ?? 'زبون' }}</span>
                                <span class="admin-action-item__meta">
                                    الباقة: {{ $sub->membership->name ?? 'عضوية' }} • {{ $sub->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="admin-action-item__side">
                                <a href="{{ route('admin.subscriptions.index') }}" class="admin-btn admin-btn--primary !min-h-[2rem] !py-1 !px-3 !text-xs">
                                    معاينة
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </section>

    <!-- Left: Subscriptions & Expiry Monitoring -->
    <section class="admin-card">
        <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">event_upcoming</span>
                <h2 class="text-base font-bold text-slate-900">متابعة الاشتراكات والصلاحيات</h2>
            </div>
            <span class="text-xs text-slate-500 font-semibold">تنبيه خلال {{ $warningDays }} أيام</span>
        </div>

        <!-- 1. Expiring Restaurants -->
        <div class="admin-subheading">
            <span>اشتراكات مطاعم قاربت على الانتهاء</span>
            <span class="text-xs font-bold text-slate-500">{{ $expiringRestaurants->count() }}</span>
        </div>
        @if($expiringRestaurants->isNotEmpty())
            <div class="admin-action-list">
                @foreach($expiringRestaurants as $restaurant)
                    <div class="admin-action-item">
                        <div class="admin-action-item__main">
                            <span class="admin-action-item__title">{{ $restaurant->name }}</span>
                            <span class="admin-action-item__meta">
                                ينتهي في: {{ $restaurant->expires_at->format('Y-m-d') }}
                            </span>
                        </div>
                        <div class="admin-action-item__side">
                            <span class="admin-pill admin-pill--wait">باقي {{ $restaurant->daysRemaining() }} يوم</span>
                            <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="admin-btn admin-btn--ghost !min-h-[2rem] !py-1 !px-2.5 !text-xs" title="تعديل أو تجديد">
                                تجديد
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-500 py-2">✅ كافة اشتراكات المطاعم سارية ولا يوجد ما هو وشيك الانتهاء.</p>
        @endif

        <!-- 2. Expiring Memberships -->
        <div class="admin-subheading mt-4">
            <span>عضويات زبائن قاربت على الانتهاء</span>
            <span class="text-xs font-bold text-slate-500">{{ $expiringMemberships->count() }}</span>
        </div>
        @if($expiringMemberships->isNotEmpty())
            <div class="admin-action-list">
                @foreach($expiringMemberships as $subscription)
                    <div class="admin-action-item">
                        <div class="admin-action-item__main">
                            <span class="admin-action-item__title">{{ $subscription->user->name ?? 'زبون' }}</span>
                            <span class="admin-action-item__meta">
                                {{ $subscription->membership->name ?? 'عضوية' }} • ينتهي في {{ $subscription->ends_at->format('Y-m-d') }}
                            </span>
                        </div>
                        <div class="admin-action-item__side">
                            <span class="admin-pill admin-pill--wait">باقي {{ $subscription->daysRemaining() }} يوم</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-500 py-2">لا توجد عضويات على وشك الانتهاء حالياً.</p>
        @endif

        <!-- 3. Expired Restaurants -->
        @if($expiredRestaurants->isNotEmpty())
            <div class="admin-subheading mt-4">
                <span>اشتراكات مطاعم منتهية الصلاحية</span>
                <span class="text-xs font-bold text-rose-600">{{ $expiredRestaurants->count() }}</span>
            </div>
            <div class="admin-action-list">
                @foreach($expiredRestaurants as $restaurant)
                    <div class="admin-action-item">
                        <div class="admin-action-item__main">
                            <span class="admin-action-item__title text-slate-500 line-through">{{ $restaurant->name }}</span>
                            <span class="admin-action-item__meta text-rose-500">
                                انتهى في {{ $restaurant->expires_at->format('Y-m-d') }}
                            </span>
                        </div>
                        <div class="admin-action-item__side">
                            <span class="admin-pill admin-pill--off">منتهي</span>
                            <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="admin-btn admin-btn--ghost !min-h-[2rem] !py-1 !px-2.5 !text-xs">
                                تفعيل
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>

<!-- Bottom Section: Latest Orders with Rich Details & Filter Chips -->
<section class="admin-card mt-5">
<!-- Bottom Section: Orders Live Board & Table (Cards in Columns by Status) -->
<section class="admin-card mt-5" data-admin-orders-table data-last-id="{{ $latestOrders->max('id') ?? 0 }}" data-live-url="{{ route('admin.orders.live') }}">
    <div class="admin-toolbar flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <h2 class="text-base font-bold text-slate-900">متابعة الطلبات المباشرة</h2>
            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                مباشر
            </span>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Mode Switcher -->
            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold">
                <button type="button" id="tab-btn-board" onclick="switchOrdersView('board')" class="px-3 py-1.5 rounded-lg transition-all bg-white text-slate-900 shadow-sm flex items-center gap-1">
                    <span class="material-symbols-outlined !text-sm text-primary">view_kanban</span>
                    <span>بطاقات وأعمدة</span>
                </button>
                <button type="button" id="tab-btn-table" onclick="switchOrdersView('table')" class="px-3 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-900 flex items-center gap-1">
                    <span class="material-symbols-outlined !text-sm">table_rows</span>
                    <span>جدول تفصيلي</span>
                </button>
            </div>

            <a class="admin-btn admin-btn--primary !min-h-[2.2rem] !py-1 !px-3.5 !text-xs" href="{{ route('admin.orders.index') }}">
                كافة الطلبات
            </a>
        </div>
    </div>

    <!-- 1. Primary View: Kanban Board of Order Cards in Columns by Status -->
    <div id="orders-view-board" class="transition-all">
        @include('admin.orders.partials.order-board', ['ordersByStatus' => $ordersByStatus])
    </div>

    <!-- 2. Secondary View: Detailed Table -->
    <div id="orders-view-table" class="admin-table-wrap hidden transition-all mt-3">
        <table>
            <thead>
                <tr>
                    <th># الطلب</th>
                    <th>الزبون</th>
                    <th>المطعم</th>
                    <th>المندوب</th>
                    <th>طريقة الدفع</th>
                    <th>المبلغ الإجمالي</th>
                    <th>الحالة</th>
                    <th class="text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestOrders as $order)
                    <tr class="admin-click-row" data-href="{{ route('admin.orders.show', $order) }}" role="link" tabindex="0">
                        <td>
                            <div class="flex flex-col">
                                <a class="font-bold text-primary hover:underline" href="{{ route('admin.orders.show', $order) }}">
                                    #{{ $order->id }}
                                </a>
                                <span class="text-[0.72rem] text-slate-400 font-medium">
                                    {{ $order->created_at->format('H:i') }} • {{ $order->created_at->diffForHumans(null, true) }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ $order->user->name ?? 'عميل' }}</span>
                                <span class="text-[0.72rem] text-slate-500 font-mono">{{ $order->phone ?? ($order->user->phone ?? '—') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="font-semibold text-slate-800">{{ $order->restaurant->name ?? '—' }}</span>
                        </td>
                        <td>
                            @if($order->courier)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                    <span class="material-symbols-outlined !text-sm text-slate-500">two_wheeler</span>
                                    {{ $order->courier->name }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400 italic">لم يُعيّن بعد</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-xs font-medium text-slate-600">
                                @if($order->payment_method === 'wallet')
                                    💳 المحفظة
                                @else
                                    💵 عند الاستلام
                                @endif
                            </span>
                        </td>
                        <td>
                            <strong class="text-slate-900 font-mono text-sm">{{ number_format($order->total, 2) }}</strong>
                            <span class="ils">₪</span>
                        </td>
                        <td>
                            @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
                        </td>
                        <td class="whitespace-nowrap">
                            <div class="admin-table-actions">
                                <a href="{{ route('admin.orders.show', $order) }}" class="admin-action-btn admin-action-btn--primary admin-action-btn--sm">
                                    <span class="material-symbols-outlined">visibility</span>
                                    <span>عرض</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 text-slate-500">
                            لا توجد طلبات مسجلة بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function switchOrdersView(view) {
            const boardEl = document.getElementById('orders-view-board');
            const tableEl = document.getElementById('orders-view-table');
            const btnBoard = document.getElementById('tab-btn-board');
            const btnTable = document.getElementById('tab-btn-table');

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
</section>

<!-- Bottom Shortcuts: Fast Management Access -->
<div class="admin-shortcuts">
    <a href="{{ route('admin.restaurants.create') }}" class="admin-shortcut">
        <span class="material-symbols-outlined">add_business</span>
        <div>
            <strong>إضافة مطعم جديد</strong>
            <small>تسجيل واعتماد شريك جديد</small>
        </div>
    </a>
    <a href="{{ route('admin.coupons.index') }}" class="admin-shortcut">
        <span class="material-symbols-outlined">sell</span>
        <div>
            <strong>أكواد الخصم</strong>
            <small>إنشاء وإدارة الكوبونات</small>
        </div>
    </a>
    <a href="{{ route('admin.delivery.index') }}" class="admin-shortcut">
        <span class="material-symbols-outlined">motorcycle</span>
        <div>
            <strong>إدارة المندوبين</strong>
            <small>تتبع التوصيل وسحب الأرباح</small>
        </div>
    </a>
    <a href="{{ route('admin.wallet-topups.index') }}" class="admin-shortcut">
        <span class="material-symbols-outlined">account_balance_wallet</span>
        <div>
            <strong>شحن رصيد المحافظ</strong>
            <small>مراجعة حوالات الزبائن</small>
        </div>
    </a>
</div>
@endsection
