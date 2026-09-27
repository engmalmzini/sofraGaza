@php
    $accounts = $paymentAccounts ?? \App\Models\Setting::paymentAccounts();
    $requiredAmount = $requiredAmount ?? null;
@endphp

<div class="rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/70 via-stone-50/50 to-orange-50/40 p-4 sm:p-5 shadow-xs text-stone-800 space-y-4 my-4">
    <div class="flex items-center justify-between gap-3 border-b border-amber-200/60 pb-3">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">account_balance</span>
            </div>
            <div>
                <h3 class="text-base font-bold text-stone-900">بيانات التحويل والدفع المعتمدة</h3>
                <p class="text-xs text-stone-600">يمكنك الدفع عبر محفظة جوال باي، محفظة بال باي (PalPay)، أو التحويل لحساب بنك فلسطين</p>
            </div>
        </div>
        @if($requiredAmount)
            <div class="text-left shrink-0 bg-white/90 border border-amber-200 px-3 py-1.5 rounded-xl shadow-2xs">
                <span class="text-[11px] block text-stone-500 font-medium">المبلغ المطلوب</span>
                <span class="text-base font-bold text-primary font-mono">{{ number_format($requiredAmount, 2) }} ₪</span>
            </div>
        @endif
    </div>

    <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
        {{-- 1. Jawwal Pay Wallet Card --}}
        <div class="rounded-xl bg-white border border-emerald-100 p-4 shadow-2xs flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -top-6 -left-6 w-20 h-20 rounded-full bg-emerald-50 pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <div class="flex items-center gap-1.5">
                        <img src="{{ asset('images/payments/jawwalpay.png') }}" alt="Jawwal Pay" class="w-5 h-5 rounded-md object-contain">
                        <h4 class="font-bold text-sm text-stone-900">جوال باي (Jawwal Pay)</h4>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">محفظة</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-stone-50 border border-stone-100">
                        <span class="text-stone-500">رقم المحفظة:</span>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono font-bold text-stone-900 text-sm" dir="ltr">{{ $accounts['jawwal_pay_number'] }}</span>
                            <button type="button" 
                                    class="p-1 rounded text-stone-500 hover:text-primary hover:bg-stone-200 transition-colors cursor-pointer" 
                                    title="نسخ الرقم"
                                    onclick="copyToClipboard('{{ $accounts['jawwal_pay_number'] }}', this)">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-1 text-stone-600">
                        <span>اسم المستفيد:</span>
                        <span class="font-bold text-stone-900">{{ $accounts['jawwal_pay_name'] }}</span>
                    </div>
                </div>
            </div>

            @if($accounts['jawwal_pay_qr_url'])
                <div class="mt-3 pt-3 border-t border-stone-100 flex items-center justify-between gap-3">
                    <div class="text-[11px] text-stone-500 leading-tight">
                        امسح كود QR من التطبيق
                    </div>
                    <a href="{{ $accounts['jawwal_pay_qr_url'] }}" target="_blank" class="shrink-0 group relative" title="اضغط لتكبير الباركود">
                        <img src="{{ $accounts['jawwal_pay_qr_url'] }}" alt="باركود جوال باي" class="w-12 h-12 object-contain rounded-lg border border-stone-200 p-0.5 bg-white shadow-2xs group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-[14px] absolute -bottom-1 -right-1 bg-stone-900 text-white rounded-full p-0.5 shadow-sm">zoom_in</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- 2. PalPay Wallet Card --}}
        <div class="rounded-xl bg-white border border-purple-100 p-4 shadow-2xs flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -top-6 -left-6 w-20 h-20 rounded-full bg-purple-50 pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <div class="flex items-center gap-1.5">
                        <img src="{{ asset('images/payments/palpay.png') }}" alt="PalPay" class="w-5 h-5 rounded-md object-contain">
                        <h4 class="font-bold text-sm text-stone-900">محفظة بال باي (PalPay)</h4>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">محفظتي</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-stone-50 border border-stone-100">
                        <span class="text-stone-500">رقم المحفظة / الحساب:</span>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono font-bold text-stone-900 text-sm" dir="ltr">{{ $accounts['palpay_number'] }}</span>
                            <button type="button" 
                                    class="p-1 rounded text-stone-500 hover:text-primary hover:bg-stone-200 transition-colors cursor-pointer" 
                                    title="نسخ الرقم"
                                    onclick="copyToClipboard('{{ $accounts['palpay_number'] }}', this)">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-1 text-stone-600">
                        <span>اسم المستفيد:</span>
                        <span class="font-bold text-stone-900">{{ $accounts['palpay_name'] }}</span>
                    </div>
                </div>
            </div>

            @if(!empty($accounts['palpay_qr_url']))
                <div class="mt-3 pt-3 border-t border-stone-100 flex items-center justify-between gap-3">
                    <div class="text-[11px] text-stone-500 leading-tight">
                        امسح كود QR من محفظتي
                    </div>
                    <a href="{{ $accounts['palpay_qr_url'] }}" target="_blank" class="shrink-0 group relative" title="اضغط لتكبير الباركود">
                        <img src="{{ $accounts['palpay_qr_url'] }}" alt="باركود بال باي" class="w-12 h-12 object-contain rounded-lg border border-stone-200 p-0.5 bg-white shadow-2xs group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-[14px] absolute -bottom-1 -right-1 bg-stone-900 text-white rounded-full p-0.5 shadow-sm">zoom_in</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- 3. Bank of Palestine Card --}}
        <div class="rounded-xl bg-white border border-blue-100 p-4 shadow-2xs flex flex-col justify-between relative overflow-hidden sm:col-span-2 lg:col-span-1">
            <div class="absolute -top-6 -left-6 w-20 h-20 rounded-full bg-blue-50 pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <div class="flex items-center gap-1.5">
                        <img src="{{ asset('images/payments/bop.png') }}" alt="Bank of Palestine" class="w-5 h-5 rounded-md object-contain">
                        <h4 class="font-bold text-sm text-stone-900">{{ $accounts['bank_name'] }}</h4>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">حساب بنكي</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-stone-50 border border-stone-100">
                        <span class="text-stone-500">رقم الحساب:</span>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono font-bold text-stone-900 text-sm" dir="ltr">{{ $accounts['bank_account_number'] }}</span>
                            <button type="button" 
                                    class="p-1 rounded text-stone-500 hover:text-primary hover:bg-stone-200 transition-colors cursor-pointer" 
                                    title="نسخ رقم الحساب"
                                    onclick="copyToClipboard('{{ $accounts['bank_account_number'] }}', this)">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-1 text-stone-600">
                        <span>اسم المستفيد:</span>
                        <span class="font-bold text-stone-900">{{ $accounts['bank_beneficiary_name'] }}</span>
                    </div>

                    @if(! empty($accounts['bank_iban']))
                        <div class="flex items-center justify-between gap-2 p-1.5 rounded-lg bg-stone-50 border border-stone-100 text-[11px]">
                            <span class="text-stone-500">آيبان (IBAN):</span>
                            <div class="flex items-center gap-1">
                                <span class="font-mono text-stone-800 truncate max-w-[130px]" dir="ltr">{{ $accounts['bank_iban'] }}</span>
                                <button type="button" 
                                        class="p-0.5 rounded text-stone-500 hover:text-primary transition-colors cursor-pointer" 
                                        title="نسخ IBAN"
                                        onclick="copyToClipboard('{{ $accounts['bank_iban'] }}', this)">
                                    <span class="material-symbols-outlined text-[14px]">content_copy</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(! empty($accounts['instructions_note']))
        <div class="flex items-start gap-2 bg-amber-500/10 rounded-xl p-2.5 text-xs text-amber-950 font-medium">
            <span class="material-symbols-outlined text-[17px] text-amber-600 shrink-0 mt-0.5">info</span>
            <span>{{ $accounts['instructions_note'] }}</span>
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
    if (icon) {
        const orig = icon.textContent;
        icon.textContent = 'check';
        icon.classList.add('text-emerald-600');
        setTimeout(() => {
            icon.textContent = orig;
            icon.classList.remove('text-emerald-600');
        }, 1800);
    }
}
</script>
