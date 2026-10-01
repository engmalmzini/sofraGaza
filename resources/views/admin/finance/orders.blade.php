@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'مالية الطلبات')

@section('content')
@include('admin.finance.partials.toolbar', ['routeName' => 'admin.finance.orders'])

<div class="admin-metrics !grid-cols-2 lg:!grid-cols-5">
    <article class="admin-metric">
        <div class="admin-metric__top"><span>إجمالي قيمة الطلبات</span></div>
        <strong>{{ number_format($totals['sales'], 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ $totals['count'] }} طلب مكتمل</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>عمولة المنصة +10%</span></div>
        <strong class="finance-in">{{ number_format($totals['commission'], 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">من قيمة الطعام بعد الخصم</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>أجرة الكابتن −15%</span></div>
        <strong class="finance-out">{{ number_format($totals['courier_share'], 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">من قيمة الطعام بعد الخصم</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>صافي المطاعم</span></div>
        <strong>{{ number_format($totals['restaurant_net'], 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">المبيعات ناقص العمولة</span>
    </article>
    <article class="admin-metric admin-metric--accent">
        <div class="admin-metric__top"><span>ملغى / مرفوض</span></div>
        <strong>{{ number_format($incompleteValue, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ $incompleteCount }} طلب غير مكتمل</span>
    </article>
</div>

<section class="admin-card mt-4">
    <h2>تفصيل كل طلب مكتمل</h2>
    <p class="text-xs text-slate-500 mb-3">قيمة الطلب، عمولة المنصة (+10%)، أجرة الكابتن (−15%)، وصافي المطعم</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المطعم</th>
                    <th>الزبون</th>
                    <th>قيمة الطلب</th>
                    <th>عمولة المنصة +10%</th>
                    <th>أجرة الكابتن −15%</th>
                    <th>صافي المطعم</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($completed as $order)
                    <tr>
                        <td class="font-mono"><a href="{{ route('admin.orders.show', $order) }}" class="font-bold hover:text-primary">#{{ $order->id }}</a></td>
                        <td>{{ $order->restaurant->name }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td class="font-mono font-bold">{{ number_format($order->foodTotal(), 2) }} ₪</td>
                        <td class="font-mono finance-in">+{{ number_format($order->platformCommission(), 2) }} ₪</td>
                        <td class="font-mono finance-out">−{{ number_format($order->courierFinanceShare(), 2) }} ₪</td>
                        <td class="font-mono">{{ number_format($order->restaurantNet(), 2) }} ₪</td>
                        <td class="text-xs text-slate-500">{{ optional($order->delivered_at ?? $order->updated_at)->format('Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 py-8">لا توجد طلبات مكتملة في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $completed->links() }}</div>
</section>

<section class="admin-card mt-4">
    <h2>الطلبات الملغاة / المرفوضة</h2>
    <p class="text-xs text-slate-500 mb-3">قيمتها وسبب الإلغاء — لمعرفة الخسائر غير المكتملة</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المطعم</th>
                    <th>الزبون</th>
                    <th>الحالة</th>
                    <th>القيمة</th>
                    <th>السبب</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomplete as $order)
                    <tr>
                        <td class="font-mono"><a href="{{ route('admin.orders.show', $order) }}" class="font-bold hover:text-primary">#{{ $order->id }}</a></td>
                        <td>{{ $order->restaurant->name }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>
                            <span class="inline-flex px-2.5 py-1 rounded-full bg-rose-100 text-rose-900 font-bold text-[11px]">{{ $order->statusLabel() }}</span>
                        </td>
                        <td class="font-mono font-bold">{{ number_format((float) $order->total, 2) }} ₪</td>
                        <td class="text-xs">{{ $order->rejection_reason ?: $order->notes ?: '—' }}</td>
                        <td class="text-xs text-slate-500">{{ $order->updated_at->format('Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-500 py-8">لا توجد طلبات ملغاة أو مرفوضة في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $incomplete->links() }}</div>
</section>
@endsection
