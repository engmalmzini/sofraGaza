@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'مالية المطاعم')

@section('content')
@include('admin.finance.partials.toolbar', ['routeName' => 'admin.finance.restaurants'])

<p class="text-xs text-slate-500 mb-3">
    هذه الصفحة لحساب المبيعات والعمولة وإقفال الفترة محاسبياً. <strong>الدفع الفعلي للمطعم يتم من
    <a href="{{ route('admin.finance.payouts') }}" class="text-primary font-bold hover:underline">سحب المطاعم</a></strong>
    بعد ما يطلب المطعم السحب وتأكيد التحويل مع إشعار.
</p>

<div class="admin-metrics !grid-cols-2 lg:!grid-cols-4">
    <article class="admin-metric">
        <div class="admin-metric__top"><span>مبيعات المطاعم</span></div>
        <strong>{{ number_format($salesTotal, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ $rows->count() }} مطعم له طلبات مكتملة</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>العمولة المستحقة</span></div>
        <strong class="finance-in">{{ number_format($commissionTotal, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ \App\Support\Finance::restaurantCommissionLabel() }} من إجمالي المبيعات</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>صافي الفترة</span></div>
        <strong>{{ number_format($netTotal, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">مبيعات الفترة ناقص العمولة</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>رصيد لم يُحوَّل</span></div>
        <strong>{{ number_format($availableTotal, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">
            @if($pendingPayoutTotal > 0)
                منها {{ number_format($pendingPayoutTotal, 2) }} ₪ طلب سحب معلّق
            @else
                المتبقي بعد التحويلات الفعلية
            @endif
        </span>
    </article>
</div>

<section class="admin-card mt-4">
    <h2>مبيعات كل مطعم</h2>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>المطعم</th>
                    <th>الطلبات</th>
                    <th>المبيعات</th>
                    <th>العمولة {{ \App\Support\Finance::restaurantCommissionLabel() }}</th>
                    <th>صافي الفترة</th>
                    <th>رصيد متاح</th>
                    <th>إقفال الفترة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="font-bold">
                            <a href="{{ route('admin.finance.restaurants.show', array_merge(['restaurant' => $row['restaurant']], $periodQuery)) }}" class="hover:text-primary">
                                {{ $row['restaurant']->name }}
                            </a>
                        </td>
                        <td>{{ $row['orders_count'] }}</td>
                        <td class="font-mono font-bold">{{ number_format($row['sales'], 2) }} ₪</td>
                        <td class="font-mono finance-in">{{ number_format($row['commission'], 2) }} ₪</td>
                        <td class="font-mono">{{ number_format($row['net'], 2) }} ₪</td>
                        <td>
                            <div class="font-mono font-bold">{{ number_format($row['available'], 2) }} ₪</div>
                            @if($row['pending_payout'] > 0)
                                <a href="{{ route('admin.finance.payouts', ['payout_status' => 'pending']) }}" class="text-[11px] font-bold text-amber-800 hover:underline">طلب سحب معلّق</a>
                            @elseif($row['available'] <= 0.009)
                                <div class="text-[11px] text-emerald-700 font-bold">تم التحويل</div>
                            @else
                                <div class="text-[11px] text-slate-500">لم يُحوَّل بعد</div>
                            @endif
                        </td>
                        <td>
                            @if($row['status'] === 'paid')
                                <span class="inline-flex px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 font-bold text-[11px]">مُقفلة</span>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['last_settled_at']?->format('Y/m/d') }}</div>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full bg-slate-50 text-slate-600 font-bold text-[11px]">غير مُقفلة</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">
                            <a href="{{ route('admin.finance.restaurants.show', array_merge(['restaurant' => $row['restaurant']], $periodQuery)) }}" class="admin-action-btn admin-action-btn--outline admin-action-btn--sm">السجل</a>
                            @if($row['status'] !== 'paid')
                                <form method="POST" action="{{ route('admin.finance.restaurants.settle', $row['restaurant']) }}" class="inline" onsubmit="return confirm('هذا إقفال محاسبي للفترة فقط، ولا يحوّل فلوس للمطعم. التحويل الفعلي من صفحة سحب المطاعم. متابعة؟');">
                                    @csrf
                                    @foreach($periodQuery as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                    <button class="admin-action-btn admin-action-btn--sm" type="submit">إقفال</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 py-8">لا توجد مبيعات مكتملة للمطاعم في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
