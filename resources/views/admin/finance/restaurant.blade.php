@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'حساب '.$restaurant->name)

@section('actions')
    <a href="{{ route('admin.finance.restaurants', $periodQuery) }}" class="admin-btn admin-btn--ghost">رجوع للمطاعم</a>
@endsection

@section('content')
@include('admin.finance.partials.toolbar', ['routeName' => 'admin.finance.restaurants.show', 'restaurant' => $restaurant])

<p class="text-xs text-slate-500 mb-3">
    الأرقام هنا ملخص المبيعات والعمولة لهذه الفترة. <strong>تحويل فلوس المطعم يتم من
    <a href="{{ route('admin.finance.payouts') }}" class="text-primary font-bold hover:underline">سحب المطاعم</a></strong>
    بعد طلب السحب، مع رفع إشعار التحويل.
</p>

<div class="admin-metrics !grid-cols-2 lg:!grid-cols-4">
    <article class="admin-metric">
        <div class="admin-metric__top"><span>مبيعات الفترة</span></div>
        <strong>{{ number_format($row['sales'] ?? 0, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ $row['orders_count'] ?? 0 }} طلب مكتمل</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>العمولة المستحقة</span></div>
        <strong class="finance-in">{{ number_format($row['commission'] ?? 0, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ \App\Support\Finance::restaurantCommissionLabel() }} من المبيعات</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>صافي مستحقات المطعم</span></div>
        <strong>{{ number_format($row['net'] ?? 0, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">مبيعات الفترة ناقص العمولة</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>رصيد المطعم المتاح</span></div>
        <strong>{{ number_format($row['available'] ?? $restaurant->availableBalance(), 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">
            @if(($row['pending_payout'] ?? $restaurant->pendingPayoutsAmount()) > 0)
                طلب سحب معلّق {{ number_format($row['pending_payout'] ?? $restaurant->pendingPayoutsAmount(), 2) }} ₪
            @else
                ما تم تحويله فعلياً: {{ number_format($row['withdrawn'] ?? $restaurant->totalWithdrawn(), 2) }} ₪
            @endif
        </span>
    </article>
</div>

@if(($row['status'] ?? 'pending') !== 'paid' && $row)
    <section class="admin-card mt-4">
        <h2>إقفال حساب هذه الفترة</h2>
        <p class="text-xs text-slate-500 mt-1">للتوثيق المحاسبي فقط. لا يُخصم من رصيد المطعم ولا يعني أنكم حوّلتم الفلوس.</p>
        <form method="POST" action="{{ route('admin.finance.restaurants.settle', $restaurant) }}" class="flex flex-wrap items-end gap-3 text-xs mt-3" onsubmit="return confirm('هذا إقفال محاسبي للفترة فقط، وليس تحويل فلوس. متابعة؟');">
            @csrf
            @foreach($periodQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label class="flex-1 min-w-[12rem]">
                <span class="block font-bold mb-1">ملاحظة</span>
                <input type="text" name="notes" placeholder="مثلاً: رُوجعت أرقام أكتوبر" class="w-full !h-10">
            </label>
            <button class="admin-btn admin-btn--ghost text-xs" type="submit">إقفال الفترة</button>
        </form>
    </section>
@elseif($row)
    <p class="text-xs text-slate-500 mt-3">حساب الفترة <strong>مُقفل محاسبياً</strong>@if($row['last_settled_at'] ?? null) بتاريخ {{ $row['last_settled_at']->format('Y/m/d') }}@endif. التحويل الفعلي يظهر في سحب المطاعم.</p>
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
        <h2>أرشيف إقفال الفترات</h2>
    <p class="text-xs text-slate-500 mb-3">سجل محاسبي لإقفال كل فترة، وليس سجل التحويلات الفعلية</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الفترة</th>
                    <th>المبيعات</th>
                    <th>العمولة</th>
                    <th>صافي الفترة</th>
                    <th>تاريخ الإقفال</th>
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
                            <form method="POST" action="{{ route('admin.finance.settlements.destroy', $settlement) }}" onsubmit="return confirm('حذف إقفال هذه الفترة من الأرشيف؟')">
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
                        <td colspan="8" class="text-center text-slate-500 py-6">لا يوجد أرشيف إقفال بعد</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
