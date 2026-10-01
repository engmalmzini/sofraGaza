@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'تسوية '.$restaurant->name)

@section('actions')
    <a href="{{ route('admin.finance.restaurants', $periodQuery) }}" class="admin-btn admin-btn--ghost">رجوع للمطاعم</a>
@endsection

@section('content')
@include('admin.finance.partials.toolbar', ['routeName' => 'admin.finance.restaurants.show', 'restaurant' => $restaurant])

<div class="admin-metrics !grid-cols-2 lg:!grid-cols-4">
    <article class="admin-metric">
        <div class="admin-metric__top"><span>مبيعات الفترة</span></div>
        <strong>{{ number_format($row['sales'] ?? 0, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ $row['orders_count'] ?? 0 }} طلب مكتمل</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>العمولة المستحقة</span></div>
        <strong class="finance-in">{{ number_format($row['commission'] ?? 0, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">10% من المبيعات</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>صافي مستحقات المطعم</span></div>
        <strong>{{ number_format($row['net'] ?? 0, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">المبلغ اللازم إيصاله</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>حالة التسوية</span></div>
        <strong>{{ ($row['status'] ?? 'pending') === 'paid' ? 'تم الدفع' : 'قيد الانتظار' }}</strong>
        <span class="admin-metric__note">آخر تسوية: {{ $row['last_settled_at']?->format('Y/m/d') ?: 'لا يوجد' }}</span>
    </article>
</div>

@if(($row['status'] ?? 'pending') !== 'paid' && $row)
    <section class="admin-card mt-4">
        <h2>تسجيل تسوية هذه الفترة</h2>
        <form method="POST" action="{{ route('admin.finance.restaurants.settle', $restaurant) }}" class="flex flex-wrap items-end gap-3 text-xs mt-3">
            @csrf
            @foreach($periodQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label class="flex-1 min-w-[12rem]">
                <span class="block font-bold mb-1">ملاحظة</span>
                <input type="text" name="notes" placeholder="حوالة / نقداً / رقم العملية" class="w-full !h-10">
            </label>
            <button class="admin-btn admin-btn--primary text-xs" type="submit">تأكيد الدفع {{ number_format($row['net'], 2) }} ₪</button>
        </form>
    </section>
@endif

<section class="admin-card mt-4">
    <h2>طلبات الفترة</h2>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>قيمة الطلب</th>
                    <th>عمولة</th>
                    <th>صافي</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold hover:text-primary">#{{ $order->id }}</a></td>
                        <td class="font-mono">{{ number_format($order->foodTotal(), 2) }} ₪</td>
                        <td class="font-mono finance-in">{{ number_format($order->platformCommission(), 2) }} ₪</td>
                        <td class="font-mono">{{ number_format($order->restaurantNet(), 2) }} ₪</td>
                        <td class="text-xs">{{ optional($order->delivered_at ?? $order->updated_at)->format('Y/m/d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-6">لا طلبات مكتملة في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="admin-card mt-4">
    <h2>السجل التاريخي للتسويات</h2>
    <p class="text-xs text-slate-500 mb-3">أرشيف كل التسويات السابقة، مع إمكانية الرجوع لأي شهر</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الفترة</th>
                    <th>المبيعات</th>
                    <th>العمولة</th>
                    <th>صافي دُفع</th>
                    <th>تاريخ الدفع</th>
                    <th>بواسطة</th>
                    <th>ملاحظة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $settlement)
                    <tr>
                        <td>
                            <a class="font-bold hover:text-primary" href="{{ route('admin.finance.restaurants.show', ['restaurant' => $restaurant, 'period' => 'custom', 'from' => $settlement->period_start->toDateString(), 'to' => $settlement->period_end->toDateString()]) }}">
                                {{ $settlement->periodLabel() }}
                            </a>
                        </td>
                        <td class="font-mono">{{ number_format($settlement->sales_total, 2) }} ₪</td>
                        <td class="font-mono">{{ number_format($settlement->commission_total, 2) }} ₪</td>
                        <td class="font-mono font-bold">{{ number_format($settlement->net_total, 2) }} ₪</td>
                        <td class="text-xs">{{ $settlement->paid_at?->format('Y/m/d H:i') ?: '—' }}</td>
                        <td class="text-xs">{{ $settlement->settler?->name ?: '—' }}</td>
                        <td class="text-xs text-slate-500">{{ $settlement->notes ?: '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.finance.settlements.destroy', $settlement) }}" onsubmit="return confirm('حذف هذه التسوية من الأرشيف؟')">
                                @csrf
                                @method('DELETE')
                                @foreach($periodQuery as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <button class="admin-action-btn admin-action-btn--ghost admin-action-btn--sm" type="submit">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 py-6">لا يوجد أرشيف تسويات بعد</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
