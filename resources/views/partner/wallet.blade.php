@extends('layouts.partner')

@section('title', 'الرصيد والسحب')

@section('content')
@php
    $periodTabs = [
        'today' => 'اليوم',
        'yesterday' => 'أمس',
        'week' => 'الأسبوع',
        'month' => 'الشهر',
        'all' => 'الكل',
    ];
@endphp

<div class="admin-metrics !grid-cols-2 lg:!grid-cols-4">
    <article class="admin-metric admin-metric--accent">
        <div class="admin-metric__top"><span>الرصيد المتاح للسحب</span></div>
        <strong>{{ number_format($availableBalance, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">صافي المبيعات بعد خصم عمولة المنصة {{ $commissionLabel }}</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>صافي المستحقات الكلي</span></div>
        <strong>{{ number_format($lifetimeNet, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">كل الطلبات المكتملة</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>ما تم سحبه</span></div>
        <strong>{{ number_format($totalWithdrawn, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">تحويلات أكّدتها الإدارة</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>قيد المراجعة</span></div>
        <strong>{{ number_format($pendingPayouts, 2) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">{{ $pendingPayouts > 0 ? 'لن يُخصم إلا بعد تأكيد التحويل' : 'لا يوجد طلب معلّق' }}</span>
    </article>
</div>

<section class="admin-card mt-4">
    <div class="admin-toolbar flex-wrap gap-2">
        <div>
            <h2>كشف الرصيد حسب الفترة</h2>
            <p class="text-xs text-slate-500 mt-1">اختر يومي أو أسبوعي أو شهري لتعرف صافي الفترة، اللي سحبته، واللي ما زال متاحاً.</p>
        </div>
        <div class="admin-chips">
            @foreach($periodTabs as $key => $label)
                <a class="admin-chip {{ $period === $key ? 'is-active' : '' }}" href="{{ route('partner.wallet', ['period' => $key]) }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="admin-metrics !grid-cols-2 lg:!grid-cols-5 mt-4 !mb-0">
        <article class="admin-metric">
            <span>طلبات مكتملة</span>
            <strong>{{ $earnings['count'] }}</strong>
        </article>
        <article class="admin-metric">
            <span>مبيعات الفترة</span>
            <strong>{{ number_format($earnings['sales'], 2) }} <span class="ils">₪</span></strong>
        </article>
        <article class="admin-metric">
            <span>عمولة المنصة {{ $commissionLabel }}</span>
            <strong class="text-rose-700">-{{ number_format($earnings['commission'], 2) }} <span class="ils">₪</span></strong>
        </article>
        <article class="admin-metric">
            <span>صافي الفترة</span>
            <strong class="text-emerald-700">{{ number_format($earnings['net'], 2) }} <span class="ils">₪</span></strong>
        </article>
        <article class="admin-metric">
            <span>متبقي هذه الفترة</span>
            <strong>{{ number_format($earnings['remaining'], 2) }} <span class="ils">₪</span></strong>
            <span class="admin-metric__note">سُحب {{ number_format($earnings['withdrawn'], 2) }} ₪</span>
        </article>
    </div>
</section>

<section class="admin-card mt-4">
    <div class="admin-toolbar">
        <h2>طلب سحب الرصيد</h2>
        @if($pendingPayouts > 0)
            <span class="admin-pill admin-pill--wait">قيد المراجعة</span>
        @elseif($availableBalance > 0)
            <span class="admin-pill admin-pill--ok">جاهز للسحب</span>
        @endif
    </div>

    @if($pendingPayouts > 0)
        <div class="admin-alert admin-alert--wait mt-3">
            طلب سحب بقيمة <strong>{{ number_format($pendingPayouts, 2) }} ₪</strong> قيد المراجعة. الرصيد يبقى ظاهراً، ويتم الخصم فقط بعد تأكيد التحويل من الإدارة.
        </div>
    @elseif($availableBalance > 0)
        <form method="POST" action="{{ route('partner.wallet.payout') }}" class="mt-4 space-y-3 text-xs">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <label>
                    <span class="block font-bold mb-1">فترة السحب</span>
                    <select name="period" class="w-full !h-11">
                        @foreach($periodLabels as $key => $label)
                            <option value="{{ $key }}" @selected(old('period', $formPeriod) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="text-slate-400">المتبقي للسحب حسب الفترة المختارة: {{ number_format($withdrawAmount, 2) }} ₪ · الرصيد الكلي المتاح: {{ number_format($availableBalance, 2) }} ₪</small>
                </label>
                <label>
                    <span class="block font-bold mb-1">المبلغ (₪)</span>
                    <input type="number" step="0.5" name="amount" min="1" max="{{ $availableBalance }}" value="{{ old('amount', $withdrawAmount > 0 ? $withdrawAmount : $availableBalance) }}" required class="w-full !h-11 font-mono font-bold">
                </label>
                <label>
                    <span class="block font-bold mb-1">طريقة التحويل</span>
                    <select name="payout_method" required class="w-full !h-11">
                        @foreach($payoutMethods as $key => $label)
                            <option value="{{ $key }}" @selected(old('payout_method', $restaurant->payout_method ?? 'jawwal_pay') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="sm:col-span-2">
                    <span class="block font-bold mb-1">بيانات التحويل</span>
                    <textarea name="transfer_details" rows="2" required placeholder="رقم المحفظة أو الحساب البنكي والاسم الرباعي">{{ old('transfer_details', $restaurant->payout_details) }}</textarea>
                </label>
            </div>
            <button class="admin-btn admin-btn--primary" type="submit">إرسال طلب السحب للإدارة</button>
        </form>
    @else
        <p class="mt-3 text-sm text-on-surface-variant">لا يوجد رصيد متاح للسحب. بعد تسليم الطلبات يظهر صافي المبيعات هنا بعد خصم عمولة المنصة {{ $commissionLabel }}.</p>
    @endif
</section>

<section class="admin-card mt-4">
    <div class="admin-toolbar">
        <h2>سجل طلبات السحب</h2>
        <span class="text-xs text-slate-500">{{ $payouts->total() }} عملية</span>
    </div>
    <div class="admin-table-wrap mt-3">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المبلغ</th>
                    <th>الفترة</th>
                    <th>الطريقة</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>ملاحظة الإدارة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payouts as $payout)
                    <tr>
                        <td class="font-bold">#{{ $payout->id }}</td>
                        <td class="font-mono font-bold">{{ number_format($payout->amount, 2) }} ₪</td>
                        <td>{{ $payout->periodLabel() }}</td>
                        <td class="text-xs">{{ $payout->methodLabel() }}</td>
                        <td><span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold {{ $payout->statusClass() }}">{{ $payout->statusLabel() }}</span></td>
                        <td class="text-xs">{{ $payout->created_at->format('Y/m/d H:i') }}</td>
                        <td class="text-xs text-slate-500">{{ $payout->admin_notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-500 py-8">ما في طلبات سحب بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payouts->links() }}</div>
</section>
@endsection
