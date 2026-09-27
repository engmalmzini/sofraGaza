@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', $user->name)

@section('content')
<div class="admin-grid-2">
    {{-- Wallet Balance & Adjust Card --}}
    <section class="admin-card border-primary/20">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]">account_balance_wallet</span>
                <div>
                    <h2 class="text-base font-bold text-on-surface">رصيد المحفظة</h2>
                    <p class="text-xs text-on-surface-variant">رصيد الزبون للشراء والدفع المباشر</p>
                </div>
            </div>
            <span class="text-xl font-black text-primary font-headline-sm">
                {{ number_format($user->wallet_balance ?? 0, 2) }} <span class="ils text-sm">₪</span>
            </span>
        </div>

        <form method="POST" action="{{ route('admin.users.wallet', $user) }}" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">المبلغ المطلوب إضافته أو خصمه (₪)</label>
                <input type="number" step="0.5" name="amount" placeholder="مثال: 50 للإضافة أو -20 للخصم" required class="text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">سبب التعديل</label>
                <input type="text" name="reason" placeholder="مثال: شحن يدوي عبر الكاش، تسوية طلب، مكافأة..." required class="text-sm">
            </div>
            <button class="admin-btn admin-btn--primary w-full">تعديل رصيد المحفظة</button>
        </form>

        <h3 class="mt-5 pt-3 border-t border-slate-100 font-bold text-xs text-on-surface">آخر حركات المحفظة</h3>
        <div class="mt-2 space-y-1.5 max-h-48 overflow-y-auto text-xs">
            @forelse($user->walletTransactions->take(6) as $tx)
                <div class="admin-row py-1.5">
                    <div>
                        <div class="font-medium text-slate-800">{{ $tx->description }}</div>
                        <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('Y/m/d H:i') }}</div>
                    </div>
                    <strong class="{{ $tx->amount > 0 ? 'text-emerald-700' : 'text-rose-600' }} dir-ltr">
                        {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount, 1) }} ₪
                    </strong>
                </div>
            @empty
                <p class="text-xs text-on-surface-variant">لا توجد حركات محفظة سابقة.</p>
            @endforelse
        </div>
    </section>

    {{-- Points Balance & Adjust Card --}}
    <section class="admin-card">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-tertiary text-[24px]">stars</span>
                <div>
                    <h2 class="text-base font-bold text-on-surface">رصيد النقاط</h2>
                    <p class="text-xs text-on-surface-variant">نقاط المكافآت واستبدال الأطباق</p>
                </div>
            </div>
            <span class="text-xl font-black text-tertiary font-headline-sm">
                {{ number_format($user->points_balance) }} نقطة
            </span>
        </div>

        <form method="POST" action="{{ route('admin.users.points', $user) }}" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">النقاط (إضافة أو خصم)</label>
                <input type="number" name="points" placeholder="مثلاً 10 أو -5" required class="text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">سبب التعديل</label>
                <input type="text" name="reason" placeholder="سبب التعديل" required class="text-sm">
            </div>
            <button class="admin-btn admin-btn--secondary w-full">تعديل النقاط يدوياً</button>
        </form>

        <h3 class="mt-5 pt-3 border-t border-slate-100 font-bold text-xs text-on-surface">سجل النقاط</h3>
        <div class="mt-2 space-y-1.5 max-h-48 overflow-y-auto text-xs">
            @forelse($user->pointTransactions->take(6) as $row)
                <div class="admin-row py-1.5">
                    <div>
                        <div class="font-medium text-slate-800">{{ $row->description }}</div>
                        <div class="text-[10px] text-slate-400">{{ $row->created_at->format('Y/m/d H:i') }}</div>
                    </div>
                    <strong class="{{ $row->points > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                        {{ $row->points > 0 ? '+' : '' }}{{ $row->points }}
                    </strong>
                </div>
            @empty
                <p class="text-xs text-on-surface-variant">لا يوجد سجل نقاط.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="admin-grid-2 mt-4">
    <section class="admin-card">
        <h2 class="text-base font-bold text-on-surface mb-3">بيانات الزبون والعضويات</h2>
        @php
            $tier = $user->tier();
        @endphp
        <div class="text-sm leading-7 mb-4">
            <div class="flex items-center gap-2 mb-1">
                <strong>مستوى الزبون:</strong>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full {{ $tier['bg_soft'] }} text-xs font-bold">
                    @include('partials.tier-icon', ['tier' => $tier['key'], 'class' => 'w-4 h-4 shrink-0'])
                    <span>{{ $tier['name'] }}</span>
                </span>
            </div>
            <div><strong>إجمالي المشتريات:</strong> <span class="font-mono font-bold text-stone-900">{{ number_format($tier['current_spent'], 1) }} ₪</span></div>
            <div><strong>الهاتف:</strong> {{ $user->phone }}</div>
            <div><strong>البريد:</strong> {{ $user->email ?: '—' }}</div>
            <div><strong>تاريخ الانضمام:</strong> {{ $user->created_at->format('Y/m/d') }}</div>
        </div>

        <h3 class="font-bold text-xs text-on-surface mb-2">العضويات</h3>
        @forelse($user->subscriptions as $subscription)
            <div class="admin-row">
                <span>{{ $subscription->membership->name }}</span>
                @include('admin.partials.pill', ['status' => $subscription->status, 'label' => $subscription->statusLabel()])
            </div>
        @empty
            <p class="text-xs text-on-surface-variant">لا توجد اشتراكات عضويات.</p>
        @endforelse
    </section>

    <section class="admin-card">
        <h2 class="text-base font-bold text-on-surface mb-3">الطلبات ({{ $user->orders->count() }})</h2>
        <div class="space-y-2 max-h-72 overflow-y-auto">
            @forelse($user->orders as $order)
                <a class="admin-row" href="{{ route('admin.orders.show', $order) }}">
                    <div>
                        <span class="font-bold text-slate-900">#{{ $order->id }}</span>
                        <span class="text-slate-600 mr-1">{{ $order->restaurant->name }}</span>
                        <div class="text-[11px] text-slate-400">{{ $order->created_at->format('Y/m/d H:i') }} • {{ number_format($order->grand_total, 1) }} ₪</div>
                    </div>
                    @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
                </a>
            @empty
                <p class="text-xs text-on-surface-variant">لا توجد طلبات لهذا الزبون.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
