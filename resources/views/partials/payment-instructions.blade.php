@php
    $accounts = $paymentAccounts ?? \App\Models\Setting::paymentAccounts();
    $requiredAmount = $requiredAmount ?? null;
@endphp

<div class="rounded-3xl border border-amber-200/70 bg-gradient-to-br from-amber-50/60 via-stone-50/40 to-orange-50/30 p-4 sm:p-6 shadow-xs text-stone-800 space-y-5 my-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-amber-200/60 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-amber-500/15 text-amber-700 flex items-center justify-center shrink-0 shadow-2xs">
                <span class="material-symbols-outlined text-[24px]">account_balance</span>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-extrabold text-stone-900">بيانات التحويل والدفع المعتمدة</h3>
                <p class="text-xs text-stone-600 mt-0.5">يمكنك الدفع عبر محفظة جوال باي، محفظة بال باي (PalPay)، أو التحويل لحساب بنك فلسطين</p>
            </div>
        </div>
        @if($requiredAmount)
            <div class="self-start sm:self-auto shrink-0 bg-white/95 border border-amber-200 px-3.5 py-1.5 rounded-2xl shadow-2xs flex items-center gap-2">
                <span class="text-xs text-stone-500 font-semibold">المبلغ المطلوب:</span>
                <span class="text-base font-extrabold text-primary font-mono">{{ number_format($requiredAmount, 2) }} ₪</span>
            </div>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- 1. Jawwal Pay Wallet Card --}}
        <div class="rounded-2xl bg-white border border-emerald-100/90 hover:border-emerald-300/80 p-4 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/payments/jawwalpay.png') }}" alt="Jawwal Pay" class="w-6 h-6 rounded-lg object-contain">
                        <h4 class="font-extrabold text-sm text-stone-900">جوال باي (Jawwal Pay)</h4>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 shrink-0">محفظة</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="p-2.5 rounded-xl bg-stone-50/80 border border-stone-200/60 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-[11px] text-stone-500 block">رقم المحفظة</span>
                            <span class="font-mono font-bold text-stone-900 text-sm select-all" dir="ltr">{{ $accounts['jawwal_pay_number'] }}</span>
                        </div>
                        <button type="button" 
                                class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-stone-200 text-stone-700 hover:text-primary hover:border-primary/40 hover:bg-orange-50/30 transition-all cursor-pointer text-xs font-semibold shadow-2xs active:scale-95" 
                                title="نسخ الرقم"
                                onclick="copyToClipboard('{{ $accounts['jawwal_pay_number'] }}', this)">
                            <span class="material-symbols-outlined text-[15px]">content_copy</span>
                            <span class="copy-text">نسخ</span>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-1 text-xs">
                        <span class="text-stone-500">اسم المستفيد:</span>
                        <span class="font-bold text-stone-900 truncate">{{ $accounts['jawwal_pay_name'] }}</span>
                    </div>
                </div>
            </div>

            @if($accounts['jawwal_pay_qr_url'])
                <div class="mt-3.5 pt-3 border-t border-stone-100 flex items-center justify-between gap-3">
                    <span class="text-[11px] text-stone-500 font-medium">امسح كود QR من التطبيق</span>
                    <a href="{{ $accounts['jawwal_pay_qr_url'] }}" target="_blank" class="shrink-0 group relative" title="اضغط لتكبير الباركود">
                        <img src="{{ $accounts['jawwal_pay_qr_url'] }}" alt="باركود جوال باي" class="w-11 h-11 object-contain rounded-xl border border-stone-200 p-0.5 bg-white shadow-2xs group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-[13px] absolute -bottom-1 -right-1 bg-stone-900 text-white rounded-full p-0.5 shadow-sm">zoom_in</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- 2. PalPay Wallet Card --}}
        <div class="rounded-2xl bg-white border border-purple-100/90 hover:border-purple-300/80 p-4 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/payments/palpay.png') }}" alt="PalPay" class="w-6 h-6 rounded-lg object-contain">
                        <h4 class="font-extrabold text-sm text-stone-900">محفظة بال باي (PalPay)</h4>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200/60 shrink-0">محفظتي</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="p-2.5 rounded-xl bg-stone-50/80 border border-stone-200/60 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-[11px] text-stone-500 block">رقم المحفظة / الحساب</span>
                            <span class="font-mono font-bold text-stone-900 text-sm select-all" dir="ltr">{{ $accounts['palpay_number'] }}</span>
                        </div>
                        <button type="button" 
                                class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-stone-200 text-stone-700 hover:text-primary hover:border-primary/40 hover:bg-orange-50/30 transition-all cursor-pointer text-xs font-semibold shadow-2xs active:scale-95" 
                                title="نسخ الرقم"
                                onclick="copyToClipboard('{{ $accounts['palpay_number'] }}', this)">
                            <span class="material-symbols-outlined text-[15px]">content_copy</span>
                            <span class="copy-text">نسخ</span>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-1 text-xs">
                        <span class="text-stone-500">اسم المستفيد:</span>
                        <span class="font-bold text-stone-900 truncate">{{ $accounts['palpay_name'] }}</span>
                    </div>
                </div>
            </div>

            @if(!empty($accounts['palpay_qr_url']))
                <div class="mt-3.5 pt-3 border-t border-stone-100 flex items-center justify-between gap-3">
                    <span class="text-[11px] text-stone-500 font-medium">امسح كود QR من محفظتي</span>
                    <a href="{{ $accounts['palpay_qr_url'] }}" target="_blank" class="shrink-0 group relative" title="اضغط لتكبير الباركود">
                        <img src="{{ $accounts['palpay_qr_url'] }}" alt="باركود بال باي" class="w-11 h-11 object-contain rounded-xl border border-stone-200 p-0.5 bg-white shadow-2xs group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-[13px] absolute -bottom-1 -right-1 bg-stone-900 text-white rounded-full p-0.5 shadow-sm">zoom_in</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- 3. Bank of Palestine Card --}}
        <div class="rounded-2xl bg-white border border-blue-100/90 hover:border-blue-300/80 p-4 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between sm:col-span-2 lg:col-span-1">
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/payments/bop.png') }}" alt="Bank of Palestine" class="w-6 h-6 rounded-lg object-contain">
                        <h4 class="font-extrabold text-sm text-stone-900">{{ $accounts['bank_name'] }}</h4>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60 shrink-0">حساب بنكي</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="p-2.5 rounded-xl bg-stone-50/80 border border-stone-200/60 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-[11px] text-stone-500 block">رقم الحساب</span>
                            <span class="font-mono font-bold text-stone-900 text-sm select-all" dir="ltr">{{ $accounts['bank_account_number'] }}</span>
                        </div>
                        <button type="button" 
                                class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-stone-200 text-stone-700 hover:text-primary hover:border-primary/40 hover:bg-orange-50/30 transition-all cursor-pointer text-xs font-semibold shadow-2xs active:scale-95" 
                                title="نسخ رقم الحساب"
                                onclick="copyToClipboard('{{ $accounts['bank_account_number'] }}', this)">
                            <span class="material-symbols-outlined text-[15px]">content_copy</span>
                            <span class="copy-text">نسخ</span>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-1 text-xs">
                        <span class="text-stone-500">اسم المستفيد:</span>
                        <span class="font-bold text-stone-900 truncate">{{ $accounts['bank_beneficiary_name'] }}</span>
                    </div>

                    @if(! empty($accounts['bank_iban']))
                        <div class="p-2.5 rounded-xl bg-stone-50/80 border border-stone-200/60 flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] text-stone-500 block">آيبان (IBAN)</span>
                                <span class="font-mono text-stone-800 text-[11px] truncate block select-all" dir="ltr">{{ $accounts['bank_iban'] }}</span>
                            </div>
                            <button type="button" 
                                    class="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white border border-stone-200 text-stone-700 hover:text-primary transition-all cursor-pointer text-[11px] font-semibold shadow-2xs active:scale-95" 
                                    title="نسخ IBAN"
                                    onclick="copyToClipboard('{{ $accounts['bank_iban'] }}', this)">
                                <span class="material-symbols-outlined text-[14px]">content_copy</span>
                                <span class="copy-text">نسخ</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(! empty($accounts['instructions_note']))
        <div class="flex items-start gap-2.5 bg-amber-500/10 border border-amber-300/40 rounded-2xl p-3 text-xs text-amber-950 font-medium">
            <span class="material-symbols-outlined text-[18px] text-amber-600 shrink-0 mt-0.5">info</span>
            <span class="leading-relaxed">{{ $accounts['instructions_note'] }}</span>
        </div>
    @endif
</div>

<script>
function copyToClipboard(text, btn) {
    if (!navigator.clipboard) {
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
    } else {
        navigator.clipboard.writeText(text);
    }

    const icon = btn.querySelector('.material-symbols-outlined');
    const textSpan = btn.querySelector('.copy-text');
    if (icon) {
        const origIcon = icon.textContent;
        const origText = textSpan ? textSpan.textContent : '';
        icon.textContent = 'check';
        icon.classList.add('text-emerald-600');
        if (textSpan) {
            textSpan.textContent = 'تم النسخ';
            textSpan.classList.add('text-emerald-700', 'font-bold');
        }
        setTimeout(() => {
            icon.textContent = origIcon;
            icon.classList.remove('text-emerald-600');
            if (textSpan) {
                textSpan.textContent = origText;
                textSpan.classList.remove('text-emerald-700', 'font-bold');
            }
        }, 1800);
    }
}
</script>
