@extends('layouts.courier')

@section('title', 'أرباحي والمحفظة')

@section('content')
<div class="space-y-4 pb-12">

    {{-- Main Balance Card --}}
    <section class="rounded-3xl bg-linear-to-br from-stone-900 via-stone-850 to-stone-900 text-white p-5 shadow-xl border border-white/10 relative overflow-hidden">
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-primary/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-stone-300 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[18px]">account_balance_wallet</span>
                    <span>الرصيد المتاح للسحب حالياً</span>
                </span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-white/10 text-stone-200">
                    خصم المنصة: 15% · حصتك: 85%
                </span>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-white">
                    {{ number_format($availableBalance, 2) }}
                </span>
                <span class="text-lg font-bold text-primary">₪</span>
            </div>

            @if($pendingPayouts > 0)
                <div class="flex items-center gap-1.5 text-xs text-amber-300 bg-amber-500/15 border border-amber-500/30 px-3 py-1.5 rounded-xl">
                    <span class="material-symbols-outlined text-[16px]">hourglass_top</span>
                    <span>يوجد <strong>{{ number_format($pendingPayouts, 2) }} ₪</strong> قيد مراجعة التحويل حالياً.</span>
                </div>
            @endif

            {{-- Quick Financial Overview Grid --}}
            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-white/10 text-center">
                <div class="p-2 rounded-xl bg-white/5">
                    <span class="text-[10px] text-stone-400 block">إجمالي أرباحك (85%)</span>
                    <strong class="text-xs font-bold text-emerald-400 block mt-0.5 font-mono">{{ number_format($lifetimeNet, 2) }} ₪</strong>
                </div>
                <div class="p-2 rounded-xl bg-white/5">
                    <span class="text-[10px] text-stone-400 block">إجمالي ما سحبته</span>
                    <strong class="text-xs font-bold text-stone-200 block mt-0.5 font-mono">{{ number_format($totalWithdrawn, 2) }} ₪</strong>
                </div>
                <div class="p-2 rounded-xl bg-white/5">
                    <span class="text-[10px] text-stone-400 block">رسوم التوصيل الأصلية</span>
                    <strong class="text-xs font-bold text-stone-300 block mt-0.5 font-mono">{{ number_format($totalGross, 2) }} ₪</strong>
                </div>
            </div>
        </div>
    </section>

    {{-- Request Payout Section --}}
    <section class="rounded-2xl bg-white p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-stone-900 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-[20px]">payments</span>
                <span>طلب سحب الأرباح وتحويلها</span>
            </h2>
            @if($availableBalance > 0)
                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">
                    جاهز للسحب
                </span>
            @endif
        </div>

        @if($availableBalance > 0)
            <form method="POST" action="{{ route('courier.wallet.payout') }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block font-bold text-stone-800 mb-1">المبلغ المطلوب سحبه (₪)</label>
                        <div class="relative">
                            <input type="number" step="0.5" name="amount" min="1" max="{{ $availableBalance }}" value="{{ old('amount', $availableBalance) }}" required class="w-full h-11 rounded-xl border border-slate-200 bg-stone-50 px-3 font-mono font-bold text-base focus:bg-white focus:border-primary outline-none">
                            <button type="button" onclick="this.previousElementSibling.value = '{{ $availableBalance }}'" class="absolute left-2.5 top-2.5 text-[11px] text-primary font-bold bg-primary/10 hover:bg-primary/20 px-2 py-0.5 rounded-md">
                                كامل الرصيد
                            </button>
                        </div>
                        <small class="text-slate-400 text-[10px]">الحد الأقصى المتاح الآن: {{ number_format($availableBalance, 2) }} ₪</small>
                    </div>

                    <div>
                        <label class="block font-bold text-stone-800 mb-1">طريقة التحويل المفضلة</label>
                        <select name="payout_method" required class="w-full h-11 rounded-xl border border-slate-200 bg-stone-50 px-3 font-semibold text-xs focus:bg-white focus:border-primary outline-none">
                            @foreach($payoutMethods as $key => $label)
                                <option value="{{ $key }}" {{ old('payout_method', $user->payout_method ?? 'jawwal_pay') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-stone-800 mb-1">بيانات التحويل (رقم الحساب / المحفظة / الاسم)</label>
                    <textarea name="transfer_details" rows="2" placeholder="اكتب رقم محفظة جوال باي أو الحساب البنكي والاسم الرباعي كاملاً..." required class="w-full rounded-xl border border-slate-200 bg-stone-50 p-2.5 text-xs focus:bg-white focus:border-primary outline-none leading-relaxed">{{ old('transfer_details', $user->payout_details) }}</textarea>
                    <small class="text-slate-400 text-[10px]">سيتم حفظ بيانات التحويل تلقائياً لتسهيل طلباتك القادمة.</small>
                </div>

                <button type="submit" class="w-full h-11 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-bold text-sm shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">send</span>
                    <span>إرسال طلب السحب للإدارة</span>
                </button>
            </form>
        @else
            <div class="p-3.5 rounded-xl bg-surface-container-low text-xs text-slate-600 flex items-start gap-2 leading-relaxed">
                <span class="material-symbols-outlined text-slate-400 text-[18px] shrink-0 mt-0.5">info</span>
                <div>
                    <span>لا يوجد رصيد متاح للسحب حالياً. بمجرد إتمام أي توصيلة طلب، ستُضاف أجرة التوصيل فوراً مع خصم 15% للمنصة وإضافة 85% لحسابك مباشرة.</span>
                </div>
            </div>
        @endif
    </section>

    {{-- Time Filters & Earnings Breakdown --}}
    <section class="rounded-2xl bg-white p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-stone-900 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[20px]">calendar_month</span>
                    <span>كشف حساب الأرباح حسب الفترة</span>
                </h2>
                <p class="text-[11px] text-slate-500">اختر الفترة لمعرفة كم عملت وحصتك الصافية بدقة</p>
            </div>

            {{-- Period Filter Pills --}}
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 text-xs">
                @php
                    $periods = [
                        'today' => 'اليوم',
                        'yesterday' => 'أمس',
                        'week' => 'الأسبوع',
                        'month' => 'الشهر',
                        'all' => 'الكل',
                    ];
                @endphp
                @foreach($periods as $pKey => $pLabel)
                    <a href="{{ route('courier.wallet', ['period' => $pKey]) }}" class="px-3 py-1.5 rounded-xl font-bold transition-all shrink-0 {{ $period === $pKey ? 'bg-primary text-on-primary shadow-xs' : 'bg-surface-container-high text-slate-700 hover:bg-slate-200' }}">
                        {{ $pLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Filtered Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
            <div class="p-3 rounded-xl bg-surface-container-lowest border border-slate-200/80 shadow-xs">
                <span class="text-[11px] text-slate-500 block">التوصيلات المكتملة</span>
                <strong class="text-base font-black text-stone-900 block mt-0.5 font-mono">{{ $earnings['count'] }} طلب</strong>
            </div>

            <div class="p-3 rounded-xl bg-surface-container-lowest border border-slate-200/80 shadow-xs">
                <span class="text-[11px] text-slate-500 block">إجمالي رسوم التوصيل</span>
                <strong class="text-base font-black text-stone-900 block mt-0.5 font-mono">{{ number_format($earnings['total_fees'], 2) }} ₪</strong>
            </div>

            <div class="p-3 rounded-xl bg-surface-container-lowest border border-slate-200/80 shadow-xs">
                <span class="text-[11px] text-slate-500 block">خصم المنصة (15%)</span>
                <strong class="text-base font-black text-rose-600 block mt-0.5 font-mono">-{{ number_format($earnings['platform_fee'], 2) }} ₪</strong>
            </div>

            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200/80 shadow-xs">
                <span class="text-[11px] text-emerald-800 font-bold block">صافي مستحقاتك (85%)</span>
                <strong class="text-lg font-black text-emerald-700 block mt-0.5 font-mono">+{{ number_format($earnings['net_earnings'], 2) }} ₪</strong>
            </div>
        </div>

        {{-- Deliveries List for this Period --}}
        <div class="pt-2">
            <h3 class="text-xs font-bold text-stone-800 mb-2">طلبات التوصيل المسلّمة خلال هذه الفترة:</h3>
            @if($earnings['orders']->isEmpty())
                <div class="py-6 text-center text-xs text-slate-400 bg-stone-50 rounded-xl">
                    لا توجد توصيلات مكتملة مسجلة في هذه الفترة.
                </div>
            @else
                <div class="space-y-2">
                    @foreach($earnings['orders'] as $order)
                        @php
                            $fee = (float) $order->delivery_fee;
                            $courierNet = round($fee * 0.85, 2);
                            $platformCut = round($fee * 0.15, 2);
                        @endphp
                        <div class="p-3 rounded-xl bg-stone-50/80 border border-slate-200/70 flex items-center justify-between gap-2 text-xs">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('courier.orders.show', $order) }}" class="font-bold text-primary hover:underline">
                                        طلب #{{ $order->id }}
                                    </a>
                                    <span class="text-slate-400 font-mono text-[10px]">
                                        {{ $order->delivered_at ? $order->delivered_at->format('Y/m/d H:i') : $order->created_at->format('Y/m/d H:i') }}
                                    </span>
                                </div>
                                <div class="text-slate-600 font-medium">
                                    {{ $order->restaurant->name }} ← {{ $order->deliveryAreaLabel() ?: $order->address_details }}
                                </div>
                            </div>

                            <div class="text-left shrink-0">
                                <div class="font-bold font-mono text-emerald-700 text-sm">
                                    +{{ number_format($courierNet, 2) }} ₪
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    (رسم: {{ number_format($fee, 1) }} - خصم: {{ number_format($platformCut, 1) }})
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Payout History Section (سجل التحويلات والسحوبات) --}}
    <section class="rounded-2xl bg-white p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-stone-900 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-[20px]">history</span>
                <span>سجل طلبات السحب والتحويل</span>
            </h2>
            <span class="text-xs text-slate-500 font-semibold">{{ $payouts->total() }} عمليات</span>
        </div>

        @if($payouts->isEmpty())
            <div class="py-6 text-center text-xs text-slate-400 bg-stone-50 rounded-xl">
                لم تقم بأي طلبات سحب سابقة بعد.
            </div>
        @else
            <div class="space-y-2.5">
                @foreach($payouts as $payout)
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:bg-stone-50/50 transition-colors text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-sm font-mono text-stone-900">
                                    {{ number_format($payout->amount, 2) }} ₪
                                </span>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $payout->statusClass() }}">
                                    {{ $payout->statusLabel() }}
                                </span>
                            </div>
                            <span class="text-[11px] font-mono text-slate-400">
                                {{ $payout->created_at->format('Y/m/d H:i') }}
                            </span>
                        </div>

                        <div class="text-slate-600 text-[11px] flex items-start gap-1">
                            <span class="font-bold text-stone-700 shrink-0">{{ $payout->methodLabel() }}:</span>
                            <span class="text-stone-600 break-all">{{ $payout->transfer_details }}</span>
                        </div>

                        @if($payout->admin_notes)
                            <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/60 text-[11px] text-slate-700">
                                <strong>ملاحظة الإدارة:</strong> {{ $payout->admin_notes }}
                            </div>
                        @endif

                        @if($payout->isCompleted())
                            <div class="text-[10px] text-emerald-700 font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                <span>تم التحويل وتصفير الرصيد المسحوب في: {{ $payout->processed_at?->format('Y/m/d H:i') }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($payouts->hasPages())
                <div class="pt-2">
                    {{ $payouts->links() }}
                </div>
            @endif
        @endif
    </section>

</div>
@endsection
