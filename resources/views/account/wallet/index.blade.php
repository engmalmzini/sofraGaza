@extends('layouts.public')

@section('title', 'محفظتي ورصيد الحساب')

@section('content')
<div class="mx-auto max-w-4xl px-margin lg:px-margin-desktop py-4 sm:py-6 lg:py-8">
    {{-- 1. Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-500/10 border border-amber-500/20 px-3 py-1 rounded-full mb-2">
                <span class="material-symbols-outlined text-[16px] text-amber-600">account_balance_wallet</span>
                <span>المحفظة الرقمية</span>
            </div>
            <h1 class="font-headline-md text-2xl lg:text-[30px] font-black text-stone-900 tracking-tight">رصيد محفظتي</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-xl leading-relaxed">
                اشحن رصيدك مرة واحدة وادفع لطلباتك فوراً دون الحاجة لرفع إشعار حوالة عند كل طلب.
            </p>
        </div>

        <a href="{{ route('account.wallet.topup') }}" class="self-start sm:self-auto inline-flex items-center gap-2 rounded-2xl bg-primary hover:bg-primary-container text-white px-5 py-3 font-bold text-sm shadow-2xs hover:shadow-xs active:scale-95 transition-all">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            <span>شحن رصيد جديد</span>
        </a>
    </div>

    {{-- 2. Luxury Wallet Card (Brand Identity & Dark Theme) --}}
    <div class="rounded-3xl text-white p-6 sm:p-8 shadow-md relative overflow-hidden mb-8 border border-amber-500/20"
         style="background: radial-gradient(circle at top right, rgba(163, 57, 0, 0.45), transparent 70%), linear-gradient(135deg, #1c1917 0%, #292524 50%, #18181b 100%);">
        {{-- Decorative circles --}}
        <div class="absolute -bottom-16 -left-16 w-56 h-56 rounded-full bg-orange-600/10 blur-3xl pointer-events-none"></div>
        <div class="absolute top-0 right-1/4 w-32 h-32 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10">
            {{-- Card Top: Brand & Chip --}}
            <div class="flex items-center justify-between gap-4 mb-6 border-b border-white/10 pb-4">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-amber-400 text-[26px]">credit_card</span>
                    <span class="text-xs sm:text-sm font-extrabold tracking-wider text-stone-200">محفظة سفرة غزة كاش</span>
                </div>
                <div class="flex items-center gap-2 text-stone-400">
                    <span class="material-symbols-outlined text-[22px]">contactless</span>
                </div>
            </div>

            {{-- Card Middle: Balance & Info --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                <div>
                    <span class="text-stone-400 text-xs sm:text-sm font-semibold block mb-1">الرصيد المتاح للدفع</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl sm:text-5xl font-black tracking-tight font-mono text-white">{{ number_format($user->wallet_balance, 2) }}</span>
                        <span class="text-2xl font-bold ils text-amber-400">₪</span>
                    </div>
                    <p class="mt-3 text-stone-300 text-xs sm:text-sm flex items-center gap-1.5 font-medium">
                        <span class="material-symbols-outlined text-amber-400 text-[18px]">verified</span>
                        <span>رصيدك مؤكد ومتاح للاستخدام الفوري على كافة مطاعم المنصة.</span>
                    </p>
                </div>

                {{-- Card Actions --}}
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <a href="{{ route('account.wallet.topup') }}" class="rounded-2xl bg-primary hover:bg-primary-container text-white px-5 py-3 font-bold text-center text-xs sm:text-sm shadow-xs transition-all active:scale-95 flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>شحن الرصيد</span>
                    </a>
                    <a href="{{ route('restaurants.index') }}" class="rounded-2xl bg-white/10 hover:bg-white/20 text-white border border-white/15 px-5 py-3 font-bold text-center text-xs sm:text-sm transition-all flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">storefront</span>
                        <span>تصفح المطاعم واطلب</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Pending Top-ups Alert (if any) --}}
    @if($pendingTopups->isNotEmpty())
        <div class="mb-8 space-y-3">
            <h2 class="text-sm sm:text-base font-extrabold text-stone-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600 text-[20px]">hourglass_top</span>
                <span>طلبات شحن رصيد بانتظار التأكيد ({{ $pendingTopups->count() }})</span>
            </h2>
            @foreach($pendingTopups as $topup)
                <div class="rounded-2xl border border-amber-200/90 bg-amber-50/70 p-4 flex flex-wrap items-center justify-between gap-3 text-stone-800 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">schedule</span>
                        </div>
                        <div>
                            <div class="font-extrabold text-sm">طلب شحن بقيمة {{ number_format($topup->amount, 2) }} ₪</div>
                            <div class="text-xs text-stone-600 mt-0.5">عبر {{ $topup->paymentMethodLabel() }} · {{ $topup->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-amber-200/90 text-amber-900 border border-amber-300/60">
                        قيد مراجعة الحوالة من الإدارة
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- 4. Features / Benefits of Wallet --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-8">
        <div class="rounded-3xl bg-white border border-stone-200/80 p-4 sm:p-5 shadow-2xs">
            <div class="w-9 h-9 rounded-2xl bg-orange-100 text-primary flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-[20px]">bolt</span>
            </div>
            <h3 class="font-extrabold text-sm text-stone-900">تأكيد فوري للطلب</h3>
            <p class="text-xs text-stone-500 mt-1 leading-relaxed">لا داعي لتحويل بنكي أو رفع إشعار عند كل وجبة؛ طلبك يُعتمد فوراً ويصل للمطعم.</p>
        </div>

        <div class="rounded-3xl bg-white border border-stone-200/80 p-4 sm:p-5 shadow-2xs">
            <div class="w-9 h-9 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-[20px]">security</span>
            </div>
            <h3 class="font-extrabold text-sm text-stone-900">أمان وحفظ الحقوق</h3>
            <p class="text-xs text-stone-500 mt-1 leading-relaxed">في حال إلغاء أي طلب أو تعذر تنفيذه، يُعاد المبلغ كاملاً إلى محفظتك في ثوانٍ.</p>
        </div>

        <div class="rounded-3xl bg-white border border-stone-200/80 p-4 sm:p-5 shadow-2xs">
            <div class="w-9 h-9 rounded-2xl bg-stone-100 text-stone-700 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-[20px]">history</span>
            </div>
            <h3 class="font-extrabold text-sm text-stone-900">سجل حركات شفاف</h3>
            <p class="text-xs text-stone-500 mt-1 leading-relaxed">تتبع دقيق لكل شيكل تم شحنه أو خصمه أو استرداده مع التاريخ والتفاصيل الكاملة.</p>
        </div>
    </div>

    {{-- 5. Transactions History Table / Cards --}}
    <section class="rounded-3xl bg-white border border-stone-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-stone-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-stone-900">سجل عمليات المحفظة</h2>
                <p class="text-xs text-stone-500 mt-0.5">كافة عمليات الشحن، الدفع، والاسترداد السابقة</p>
            </div>
            <span class="text-xs font-bold text-stone-400 bg-stone-50 px-2.5 py-1 rounded-lg border border-stone-200/60">
                {{ $transactions->total() }} حركة
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-stone-50/80 text-stone-500 border-b border-stone-100">
                        <th class="py-3.5 px-4 font-bold">نوع العملية</th>
                        <th class="py-3.5 px-4 font-bold">الوصف</th>
                        <th class="py-3.5 px-4 font-bold">المبلغ</th>
                        <th class="py-3.5 px-4 font-bold">الرصيد بعدها</th>
                        <th class="py-3.5 px-4 font-bold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-stone-900">
                                @if($tx->isCredit())
                                    <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-md font-extrabold text-[11px]">
                                        <span class="material-symbols-outlined text-[13px]">arrow_downward</span>
                                        <span>{{ $tx->typeLabel() }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-stone-700 bg-stone-100 border border-stone-200 px-2 py-0.5 rounded-md font-extrabold text-[11px]">
                                        <span class="material-symbols-outlined text-[13px]">arrow_upward</span>
                                        <span>{{ $tx->typeLabel() }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-stone-600 max-w-xs truncate">{{ $tx->description }}</td>
                            <td class="py-3.5 px-4 font-mono font-black text-sm {{ $tx->isCredit() ? 'text-emerald-700' : 'text-stone-900' }}">
                                {{ $tx->isCredit() ? '+' : '−' }}{{ number_format(abs($tx->amount), 2) }} ₪
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-stone-800">{{ number_format($tx->balance_after, 2) }} ₪</td>
                            <td class="py-3.5 px-4 text-stone-500 whitespace-nowrap font-mono text-[11px]">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-stone-400">
                                <span class="material-symbols-outlined text-4xl block mb-2 opacity-40">account_balance_wallet</span>
                                <span class="text-xs font-semibold">لا توجد حركات سابقة على المحفظة بعد.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
