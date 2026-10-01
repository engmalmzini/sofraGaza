@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'لوحة المالية')

@section('content')
@include('admin.finance.partials.toolbar', ['routeName' => 'admin.finance.index'])

@php
    $in = $overview['in'];
    $out = $overview['out'];
    $cmp = $overview['comparison'] ?? null;
    $cmpLabel = ($cmp['kind'] ?? '') === 'month' ? 'مقارنة بالشهر السابق' : ($cmp['label'] ?? 'مقارنة بالفترة السابقة');
    $orders = $overview['completed_orders'] ?? collect();
    $memberships = $in['memberships']['rows'] ?? [];
@endphp

<section class="admin-card mb-4">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h2>نظرة عامة</h2>
        <span class="text-xs font-bold text-slate-500">{{ $period['label'] }} — يُحسب تلقائياً</span>
    </div>
    <div class="admin-metrics !grid-cols-2 lg:!grid-cols-4 !mt-0">
        <article class="admin-metric">
            <div class="admin-metric__top">
                <span>الدخل الكلي</span>
                <div class="admin-metric__icon !bg-emerald-50 !text-emerald-700 !border-emerald-200">
                    <span class="material-symbols-outlined">south</span>
                </div>
            </div>
            <strong class="finance-in">{{ number_format($in['total'], 2) }} <span class="ils">₪</span></strong>
            @include('admin.finance.partials.compare', ['compare' => $cmp['in'] ?? null, 'label' => $cmpLabel])
            <span class="admin-metric__note">طلبات + اشتراكات + إعلانات + دخل إضافي</span>
        </article>
        <article class="admin-metric">
            <div class="admin-metric__top">
                <span>المصروف الكلي</span>
                <div class="admin-metric__icon !bg-rose-50 !text-rose-700 !border-rose-200">
                    <span class="material-symbols-outlined">north</span>
                </div>
            </div>
            <strong class="finance-out">{{ number_format($out['total'], 2) }} <span class="ils">₪</span></strong>
            @include('admin.finance.partials.compare', ['compare' => $cmp['out'] ?? null, 'label' => $cmpLabel, 'invert' => true])
            <span class="admin-metric__note">كباتن + مزايا الأعضاء + تشغيل</span>
        </article>
        <article class="admin-metric {{ $overview['net'] >= 0 ? '' : 'admin-metric--accent' }}">
            <div class="admin-metric__top">
                <span>الصافي الحقيقي</span>
                <div class="admin-metric__icon">
                    <span class="material-symbols-outlined">monitoring</span>
                </div>
            </div>
            <strong class="{{ $overview['net'] >= 0 ? 'finance-in' : 'finance-out' }}">{{ number_format($overview['net'], 2) }} <span class="ils">₪</span></strong>
            @include('admin.finance.partials.compare', ['compare' => $cmp['net'] ?? null, 'label' => $cmpLabel])
            <span class="admin-metric__note">الدخل الكلي ناقص المصروف الكلي</span>
        </article>
        <article class="admin-metric">
            <div class="admin-metric__top">
                <span>الطلبات المكتملة</span>
                <div class="admin-metric__icon">
                    <span class="material-symbols-outlined">task_alt</span>
                </div>
            </div>
            <strong>{{ number_format($overview['completed_count']) }}</strong>
            <span class="admin-metric__note">مبيعات {{ number_format($overview['completed_sales'], 2) }} ₪ • ملغى/مرفوض {{ $overview['incomplete_count'] }}</span>
        </article>
    </div>
</section>

<section class="admin-card mb-4">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <div>
            <h2>دخل الطلبات</h2>
            <p class="text-xs text-slate-500 mt-1">تفصيل كل طلب: قيمته، عمولة المنصة منه (+10%)، أجرة الكابتن منه (−15%)</p>
        </div>
        <a class="admin-btn admin-btn--ghost text-xs" href="{{ route('admin.finance.orders', $periodQuery) }}">كافة الطلبات</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المطعم</th>
                    <th>قيمة الطلب</th>
                    <th>عمولة المنصة +10%</th>
                    <th>أجرة الكابتن −15%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders->take(20) as $order)
                    <tr>
                        <td class="font-mono"><a href="{{ route('admin.orders.show', $order) }}" class="font-bold hover:text-primary">#{{ $order->id }}</a></td>
                        <td>{{ $order->restaurant->name ?? '—' }}</td>
                        <td class="font-mono font-bold">{{ number_format($order->foodTotal(), 2) }} ₪</td>
                        <td class="font-mono finance-in">+{{ number_format($order->platformCommission(), 2) }} ₪</td>
                        <td class="font-mono finance-out">−{{ number_format($order->courierFinanceShare(), 2) }} ₪</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-8">لا توجد طلبات مكتملة في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
            @if($orders->isNotEmpty())
                <tfoot>
                    <tr class="font-extrabold">
                        <td colspan="2">الإجمالي ({{ $orders->count() }} طلب)</td>
                        <td class="font-mono">{{ number_format($overview['completed_sales'], 2) }} ₪</td>
                        <td class="font-mono finance-in">+{{ number_format($in['commission'], 2) }} ₪</td>
                        <td class="font-mono finance-out">−{{ number_format($out['courier'], 2) }} ₪</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</section>

<section class="admin-card mb-4">
    <h2>دخل الاشتراكات</h2>
    <p class="text-xs text-slate-500 mb-3">عدد مشتركي كل فئة (60 / 100) وإجمالي دخلهم</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الفئة</th>
                    <th>عدد المشتركين</th>
                    <th>سعر الاشتراك</th>
                    <th>إجمالي الدخل</th>
                </tr>
            </thead>
            <tbody>
                @forelse($memberships as $row)
                    <tr>
                        <td class="font-bold">{{ $row['source'] }}{{ !empty($row['plan']) ? ' — '.$row['plan'] : '' }}</td>
                        <td>{{ number_format($row['count'] ?? 0) }}</td>
                        <td class="font-mono">{{ number_format($row['price'] ?? 0, 0) }} ₪</td>
                        <td class="font-mono finance-in">{{ number_format($row['amount'], 2) }} ₪</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-slate-500 py-8">لا توجد اشتراكات معتمدة في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($memberships))
                <tfoot>
                    <tr class="font-extrabold">
                        <td colspan="3">إجمالي دخل الاشتراكات</td>
                        <td class="font-mono finance-in">{{ number_format($in['memberships']['total'], 2) }} ₪</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</section>

<section class="admin-card mb-4">
    <h2>دخل الإعلانات</h2>
    <p class="text-xs text-slate-500 mb-3">كل حملة Boost نشطة أو منتهية: المطعم، عدد الأيام، السعر، الحالة</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>المطعم</th>
                    <th>الأيام</th>
                    <th>سعر اليوم</th>
                    <th>السعر</th>
                    <th>دخل الفترة</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($in['boosts'] as $boost)
                    <tr>
                        <td class="font-bold">{{ $boost->restaurant->name ?? '—' }}</td>
                        <td>{{ $boost->durationDays() }} يوم</td>
                        <td class="font-mono">{{ number_format((float) $boost->daily_rate, 0) }} ₪</td>
                        <td class="font-mono">{{ number_format($boost->totalIncome(), 2) }} ₪</td>
                        <td class="font-mono finance-in">{{ number_format($boost->incomeIn($period['start'], $period['end']), 2) }} ₪</td>
                        <td>
                            <span class="inline-flex px-2.5 py-1 rounded-full font-bold text-[11px] {{ $boost->campaignStateClass() }}">{{ $boost->campaignStateLabel() }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-8">لا توجد حملات إعلان في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
            @if($in['boosts']->isNotEmpty())
                <tfoot>
                    <tr class="font-extrabold">
                        <td colspan="4">إجمالي دخل الإعلانات</td>
                        <td class="font-mono finance-in" colspan="2">{{ number_format($in['boost_total'], 2) }} ₪</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</section>

<section class="admin-card mb-4">
    <h2>مصروف الكباتن</h2>
    <p class="text-xs text-slate-500 mb-3">مستحقات كل كابتن من −15% من قيمة الطلبات، وحالة الدفع</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الكابتن</th>
                    <th>الطلبات</th>
                    <th>المستحق</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                    <th>حالة الدفع</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overview['couriers'] as $row)
                    <tr>
                        <td class="font-bold">{{ $row['name'] }}</td>
                        <td>{{ $row['orders_count'] }}</td>
                        <td class="font-mono finance-out">{{ number_format($row['dues'], 2) }} ₪</td>
                        <td class="font-mono">{{ number_format($row['paid'], 2) }} ₪</td>
                        <td class="font-mono font-bold">{{ number_format($row['remaining'], 2) }} ₪</td>
                        <td>
                            <span class="inline-flex px-2.5 py-1 rounded-full font-bold text-[11px]
                                {{ $row['status_key'] === 'paid' ? 'bg-emerald-100 text-emerald-900' : ($row['status_key'] === 'pending' ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-900') }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-8">لا توجد مستحقات كباتن في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
            @if($overview['couriers']->isNotEmpty())
                <tfoot>
                    <tr class="font-extrabold">
                        <td colspan="2">إجمالي مصروف الكباتن</td>
                        <td class="font-mono finance-out" colspan="4">{{ number_format($out['courier'], 2) }} ₪</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</section>

<section class="admin-card mb-4">
    <h2>مصروف مزايا الأعضاء</h2>
    <p class="text-xs text-slate-500 mb-3">تكلفة الهدايا الشهرية لفئة 100 — تُشترى من المطعم بسعره الكامل</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العضو</th>
                    <th>المطعم</th>
                    <th>التكلفة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($out['gifts'] as $gift)
                    <tr>
                        <td class="font-mono">#{{ $gift->id }}</td>
                        <td>{{ $gift->user->name ?? '—' }}</td>
                        <td>{{ $gift->restaurant->name ?? '—' }}</td>
                        <td class="font-mono finance-out">{{ number_format($gift->giftCost(), 2) }} ₪</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-slate-500 py-8">لا توجد هدايا شهرية مسجّلة في هذه الفترة</td>
                    </tr>
                @endforelse
                @if($out['gift_manual_total'] > 0)
                    <tr>
                        <td colspan="3" class="text-xs text-slate-500">هدايا مُدخلة يدوياً</td>
                        <td class="font-mono finance-out">{{ number_format($out['gift_manual_total'], 2) }} ₪</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="font-extrabold">
                    <td colspan="3">إجمالي مصروف مزايا الأعضاء</td>
                    <td class="font-mono finance-out">{{ number_format($out['gift_total'], 2) }} ₪</td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<section class="finance-net-hero admin-card mb-4 {{ $overview['net'] >= 0 ? 'is-profit' : 'is-loss' }}">
    <div>
        <h2>الصافي الحقيقي</h2>
        <p class="text-xs mt-1 opacity-80">الرقم النهائي بعد كل شي فوق: الدخل الكلي − المصروف الكلي</p>
    </div>
    <div class="finance-net-hero__figure">
        <strong>{{ number_format($overview['net'], 2) }} <span class="ils">₪</span></strong>
        @include('admin.finance.partials.compare', ['compare' => $cmp['net'] ?? null, 'label' => $cmpLabel])
    </div>
    <ul class="finance-formula">
        <li>دخل الطلبات +{{ number_format($in['commission'], 2) }}</li>
        <li>دخل الاشتراكات +{{ number_format($in['memberships']['total'], 2) }}</li>
        <li>دخل الإعلانات +{{ number_format($in['boost_total'], 2) }}</li>
        @if($in['extra_total'] > 0)
            <li>دخل إضافي +{{ number_format($in['extra_total'], 2) }}</li>
        @endif
        <li>مصروف الكباتن −{{ number_format($out['courier'], 2) }}</li>
        <li>مزايا الأعضاء −{{ number_format($out['gift_total'], 2) }}</li>
        @if($out['operating'] > 0)
            <li>تشغيل −{{ number_format($out['operating'], 2) }}</li>
        @endif
    </ul>
</section>

<div class="admin-grid-2 lg:grid-cols-2 mt-4">
    <section class="admin-card">
        <h2>تسجيل حملة إعلان (Boost)</h2>
        <p class="text-xs text-slate-500 mb-3">السعر = عدد الأيام × {{ number_format($boostRate, 0) }} ₪</p>
        <form method="POST" action="{{ route('admin.finance.boosts.store') }}" class="grid gap-3 text-xs sm:grid-cols-2">
            @csrf
            @foreach($periodQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label class="sm:col-span-2">
                <span class="block font-bold mb-1">المطعم</span>
                <select name="restaurant_id" required class="w-full !h-10">
                    <option value="">اختر مطعماً</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="block font-bold mb-1">من</span>
                <input type="date" name="starts_on" required value="{{ $period['start']->toDateString() }}" class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">إلى</span>
                <input type="date" name="ends_on" required value="{{ $period['end']->toDateString() }}" class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">سعر اليوم</span>
                <input type="number" step="0.01" min="0" name="daily_rate" value="{{ $boostRate }}" class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">عنوان اختياري</span>
                <input type="text" name="title" placeholder="Boost الصفحة الرئيسية" class="w-full !h-10">
            </label>
            <div class="sm:col-span-2">
                <button class="admin-btn admin-btn--primary text-xs" type="submit">حفظ الحملة</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <h2>مصروف تشغيلي أو دخل إضافي</h2>
        <form method="POST" action="{{ route('admin.finance.expenses.store') }}" class="grid gap-3 text-xs sm:grid-cols-2 mb-5">
            @csrf
            @foreach($periodQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label class="sm:col-span-2">
                <span class="block font-bold mb-1">مصروف</span>
                <input type="text" name="title" required placeholder="استضافة، تصميم، طباعة بطاقات" class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">المبلغ</span>
                <input type="number" step="0.01" min="0.01" name="amount" required class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">التاريخ</span>
                <input type="date" name="spent_on" required value="{{ now()->toDateString() }}" class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">التصنيف</span>
                <select name="category" class="w-full !h-10">
                    <option value="operating">تشغيل</option>
                    <option value="gift">هدية عضوية 100</option>
                    <option value="other">أخرى</option>
                </select>
            </label>
            <div class="flex items-end">
                <button class="admin-btn admin-btn--secondary text-xs" type="submit">إضافة مصروف</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.finance.incomes.store') }}" class="grid gap-3 text-xs sm:grid-cols-2">
            @csrf
            @foreach($periodQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label class="sm:col-span-2">
                <span class="block font-bold mb-1">دخل إضافي</span>
                <input type="text" name="title" required placeholder="رسوم إعداد، حملة لاحقة" class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">المبلغ</span>
                <input type="number" step="0.01" min="0.01" name="amount" required class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">التاريخ</span>
                <input type="date" name="received_on" required value="{{ now()->toDateString() }}" class="w-full !h-10">
            </label>
            <label>
                <span class="block font-bold mb-1">التصنيف</span>
                <select name="category" class="w-full !h-10">
                    <option value="other">دخل إضافي</option>
                    <option value="setup">رسوم إعداد</option>
                </select>
            </label>
            <div class="flex items-end">
                <button class="admin-btn admin-btn--primary text-xs" type="submit">إضافة دخل</button>
            </div>
        </form>

        @if($out['expenses']->isNotEmpty() || $in['extras']->isNotEmpty())
            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>البند</th>
                            <th>النوع</th>
                            <th>المبلغ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($in['extras'] as $income)
                            <tr>
                                <td>{{ $income->title }}</td>
                                <td class="text-xs finance-in">{{ $income->categoryLabel() }}</td>
                                <td class="font-mono finance-in">+{{ number_format($income->amount, 2) }} ₪</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.finance.incomes.destroy', $income) }}" onsubmit="return confirm('حذف الدخل؟')">
                                        @csrf
                                        @method('DELETE')
                                        @foreach($periodQuery as $key => $value)
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endforeach
                                        <button class="admin-action-btn admin-action-btn--ghost admin-action-btn--sm" type="submit">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @foreach($out['expenses'] as $expense)
                            <tr>
                                <td>{{ $expense->title }}</td>
                                <td class="text-xs finance-out">{{ $expense->categoryLabel() }}</td>
                                <td class="font-mono finance-out">−{{ number_format($expense->amount, 2) }} ₪</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.finance.expenses.destroy', $expense) }}" onsubmit="return confirm('حذف المصروف؟')">
                                        @csrf
                                        @method('DELETE')
                                        @foreach($periodQuery as $key => $value)
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endforeach
                                        <button class="admin-action-btn admin-action-btn--ghost admin-action-btn--sm" type="submit">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
