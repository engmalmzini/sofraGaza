@extends('layouts.public')

@section('title', 'محفظتي ورصيد الحساب')

@section('content')
<div class="mx-auto max-w-4xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <div class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full mb-1">
                <span class="material-symbols-outlined text-[15px]">account_balance_wallet</span>
                <span>المحفظة الرقمية</span>
            </div>
            <h1 class="font-headline-md text-2xl lg:text-[28px] font-bold text-stone-900">رصيد محفظتي</h1>
            <p class="text-sm text-stone-500 mt-1">اشحن رصيدك مرة واحدة وادفع لطلباتك فوراً دون الحاجة لرفع إشعار حوالة عند كل طلب.</p>
        </div>

        <a href="{{ route('account.wallet.topup') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 font-bold shadow-xs transition-colors">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            <span>شحن رصيد جديد</span>
        </a>
    </div>

    {{-- Balance Summary Card --}}
    <div class="rounded-3xl bg-gradient-to-br from-emerald-600 via-teal-700 to-stone-900 text-white p-6 sm:p-8 shadow-md relative overflow-hidden mb-8">
        <div class="absolute -bottom-10 -left-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-6">
            <div>
                <span class="text-emerald-100 text-sm font-medium block mb-1">الرصيد المتاح للدفع</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl sm:text-5xl font-black tracking-tight font-mono">{{ number_format($user->wallet_balance, 2) }}</span>
                    <span class="text-2xl font-bold ils">₪</span>
                </div>
                <p class="mt-3 text-emerald-100/90 text-xs sm:text-sm flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">verified_user</span>
                    <span>رصيدك مؤكد ومتاح للاستخدام الفوري على كافة مطاعم المنصة.</span>
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <a href="{{ route('account.wallet.topup') }}" class="rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 px-5 py-3 font-bold text-center text-sm shadow-sm transition-colors">
                    + شحن الرصيد
                </a>
                <a href="{{ route('restaurants.index') }}" class="rounded-xl bg-white/15 hover:bg-white/25 text-white border border-white/20 px-5 py-3 font-bold text-center text-sm transition-colors">
                    تصفح المطاعم واطلب
                </a>
            </div>
        </div>
    </div>

    {{-- Pending Topups Alert (if any) --}}
    @if($pendingTopups->isNotEmpty())
        <div class="mb-8 space-y-3">
            <h2 class="text-base font-bold text-stone-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600 text-[20px]">pending</span>
                <span>طلبات شحن رصيد بانتظار التأكيد ({{ $pendingTopups->count() }})</span>
            </h2>
            @foreach($pendingTopups as $topup)
                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 flex flex-wrap items-center justify-between gap-3 text-stone-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">schedule</span>
                        </div>
                        <div>
                            <div class="font-bold text-sm">طلب شحن بقيمة {{ number_format($topup->amount, 2) }} ₪</div>
                            <div class="text-xs text-stone-600">عبر {{ $topup->paymentMethodLabel() }} · {{ $topup->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-200/80 text-amber-900">
                        قيد مراجعة الحوالة من الإدارة
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Features / Benefits of Wallet --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-8">
        <div class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-[18px]">bolt</span>
            </div>
            <h3 class="font-bold text-sm text-stone-900">تأكيد فوري للطلب</h3>
            <p class="text-xs text-stone-500 mt-1">لا داعي لتحويل بنكي أو رفع إشعار عند كل وجبة؛ طلبك يُعتمد فوراً ويصل للمطعم.</p>
        </div>

        <div class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-[18px]">security</span>
            </div>
            <h3 class="font-bold text-sm text-stone-900">أمان وحفظ الحقوق</h3>
            <p class="text-xs text-stone-500 mt-1">في حال إلغاء أي طلب أو تعذر تنفيذه، يُعاد المبلغ كاملاً إلى محفظتك في ثوانٍ.</p>
        </div>

        <div class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-[18px]">history</span>
            </div>
            <h3 class="font-bold text-sm text-stone-900">سجل حركات شفاف</h3>
            <p class="text-xs text-stone-500 mt-1">تتبع دقيق لكل شيكل تم شحنه أو خصمه أو استرداده مع التاريخ والتفاصيل الكاملة.</p>
        </div>
    </div>

    {{-- Transactions History Table --}}
    <section class="rounded-2xl bg-surface-container-lowest border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-stone-900">سجل عمليات المحفظة</h2>
                <p class="text-xs text-stone-500">كافة عمليات الشحن، الدفع، والاسترداد السابقة</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-stone-50/80 text-stone-500 border-b border-slate-100">
                        <th class="py-3 px-4 font-semibold">نوع العملية</th>
                        <th class="py-3 px-4 font-semibold">الوصف</th>
                        <th class="py-3 px-4 font-semibold">المبلغ</th>
                        <th class="py-3 px-4 font-semibold">الرصيد بعدها</th>
                        <th class="py-3 px-4 font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-stone-900">
                                @if($tx->isCredit())
                                    <span class="inline-flex items-center gap-1 text-emerald-700">
                                        <span class="material-symbols-outlined text-[15px]">arrow_downward</span>
                                        <span>{{ $tx->typeLabel() }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-rose-700">
                                        <span class="material-symbols-outlined text-[15px]">arrow_upward</span>
                                        <span>{{ $tx->typeLabel() }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-stone-600 max-w-xs truncate">{{ $tx->description }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-sm {{ $tx->isCredit() ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $tx->isCredit() ? '+' : '' }}{{ number_format($tx->amount, 2) }} ₪
                            </td>
                            <td class="py-3.5 px-4 font-mono font-medium text-stone-800">{{ number_format($tx->balance_after, 2) }} ₪</td>
                            <td class="py-3.5 px-4 text-stone-500 whitespace-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-stone-400">
                                <span class="material-symbols-outlined text-4xl block mb-2 opacity-50">account_balance_wallet</span>
                                لا توجد حركات سابقة على المحفظة بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
