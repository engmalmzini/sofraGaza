@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'مالية المطاعم')

@section('content')
@include('admin.finance.partials.toolbar', ['routeName' => 'admin.finance.restaurants'])

<div class="admin-metrics !grid-cols-2 lg:!grid-cols-4">
    <article class="admin-metric">
        <div class="admin-metric__top"><span>مبيعات المطاعم</span></div>
        <strong>{{ number_format($salesTotal, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ $rows->count() }} مطعم له طلبات مكتملة</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>العمولة المستحقة</span></div>
        <strong class="finance-in">{{ number_format($commissionTotal, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">10% من إجمالي المبيعات</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>صافي مستحقات المطاعم</span></div>
        <strong>{{ number_format($netTotal, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">المبيعات ناقص العمولة</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>حالة التسوية</span></div>
        <strong>{{ $pendingCount }} قيد الانتظار</strong>
        <span class="admin-metric__note">{{ $paidCount }} تم الدفع لهذه الفترة</span>
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
                    <th>العمولة 10%</th>
                    <th>صافي المستحق</th>
                    <th>حالة التسوية</th>
                    <th>آخر تسوية</th>
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
                            @if($row['status'] === 'paid')
                                <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px]">تم الدفع</span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-bold text-[11px]">قيد الانتظار</span>
                            @endif
                        </td>
                        <td class="text-xs text-slate-500">{{ $row['last_settled_at']?->format('Y/m/d') ?: '—' }}</td>
                        <td class="whitespace-nowrap">
                            <a href="{{ route('admin.finance.restaurants.show', array_merge(['restaurant' => $row['restaurant']], $periodQuery)) }}" class="admin-action-btn admin-action-btn--outline admin-action-btn--sm">السجل</a>
                            @if($row['status'] !== 'paid')
                                <form method="POST" action="{{ route('admin.finance.restaurants.settle', $row['restaurant']) }}" class="inline">
                                    @csrf
                                    @foreach($periodQuery as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                    <button class="admin-action-btn admin-action-btn--sm" type="submit">تسوية</button>
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
