@extends('layouts.public')

@section('title', 'شحن رصيد المحفظة')

@section('content')
<div class="mx-auto max-w-3xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <a href="{{ route('account.wallet') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-500 hover:text-stone-900 mb-4 transition-colors">
        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        <span>العودة للمحفظة</span>
    </a>

    <div class="mb-6">
        <h1 class="font-headline-md text-2xl lg:text-[28px] font-bold text-stone-900">شحن رصيد المحفظة</h1>
        <p class="text-sm text-stone-500 mt-1">
            اختر المبلغ المراد شحنه، حوّل عبر محفظة جوال باي أو بنك فلسطين، ثم ارفع صورة إشعار التحويل لتأكيد الرصيد بحسابك.
        </p>
    </div>

    <form method="POST" action="{{ route('account.wallet.topup.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Amount Selection --}}
        <div class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-stone-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[20px]">payments</span>
                <span>1. اختر مبلغ الشحن (شيكل)</span>
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([50, 100, 200, 500] as $preset)
                    <button type="button" 
                            class="preset-amount-btn py-3 px-4 rounded-xl border border-slate-200 text-stone-800 font-bold text-center hover:border-emerald-500 hover:bg-emerald-50/50 transition-all cursor-pointer"
                            onclick="setTopupAmount({{ $preset }}, this)">
                        <span class="text-lg font-mono">{{ $preset }}</span>
                        <span class="text-xs mr-0.5">₪</span>
                    </button>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1.5">أو أدخل مبلغاً مخصصاً (الحد الأدنى 5 ₪)</label>
                <div class="relative max-w-xs">
                    <input type="number" 
                           id="topup-amount-input" 
                           name="amount" 
                           step="1" 
                           min="5" 
                           max="5000" 
                           value="{{ old('amount', 100) }}" 
                           required 
                           class="w-full h-12 rounded-xl border border-slate-200 px-4 font-mono font-bold text-lg text-stone-900 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                    <span class="absolute left-4 top-3 text-stone-400 font-bold text-sm pointer-events-none">ILS ₪</span>
                </div>
            </div>
        </div>

        {{-- Payment Method Selection --}}
        <div class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-stone-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[20px]">account_balance</span>
                <span>2. طريقة التحويل المعتمدة</span>
            </h2>

            <div class="grid sm:grid-cols-3 gap-3">
                <label class="payment-method-card flex items-center gap-3 p-4 rounded-xl border-2 border-emerald-500 bg-emerald-50/40 cursor-pointer transition-all">
                    <input type="radio" name="payment_method" value="jawwal_pay" class="sr-only" checked onchange="togglePaymentCard(this)">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">smartphone</span>
                    </div>
                    <div>
                        <span class="block font-bold text-sm text-stone-900">جوال باي (Jawwal Pay)</span>
                        <span class="block text-xs text-stone-500">تحويل فوري برقم الجوال</span>
                    </div>
                </label>

                <label class="payment-method-card flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-slate-300 cursor-pointer transition-all">
                    <input type="radio" name="payment_method" value="palpay" class="sr-only" onchange="togglePaymentCard(this)">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">account_balance_wallet</span>
                    </div>
                    <div>
                        <span class="block font-bold text-sm text-stone-900">محفظة بال باي (PalPay)</span>
                        <span class="block text-xs text-stone-500">تحويل برقم المحفظة أو QR</span>
                    </div>
                </label>

                <label class="payment-method-card flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-slate-300 cursor-pointer transition-all">
                    <input type="radio" name="payment_method" value="bank" class="sr-only" onchange="togglePaymentCard(this)">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">account_balance</span>
                    </div>
                    <div>
                        <span class="block font-bold text-sm text-stone-900">حساب بنك فلسطين</span>
                        <span class="block text-xs text-stone-500">حوالة بنكية مباشرة أو آيبان</span>
                    </div>
                </label>
            </div>

            {{-- Include Payment Details Component --}}
            @include('partials.payment-instructions', ['paymentAccounts' => $paymentAccounts])
        </div>

        {{-- Upload Receipt --}}
        <div class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-stone-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[20px]">upload_file</span>
                <span>3. إرفاق صورة إشعار الحوالة</span>
            </h2>

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1.5">صورة الإشعار (لقطة الشاشة للتحويل)</label>
                <div class="rounded-2xl border-2 border-dashed border-slate-200 hover:border-emerald-500 p-5 text-center bg-stone-50/50 transition-colors">
                    <span class="material-symbols-outlined text-3xl text-stone-400 block mb-1">add_photo_alternate</span>
                    <input type="file" name="receipt" accept="image/*" required class="block w-full text-xs text-stone-600 file:mr-0 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                    <p class="text-[11px] text-stone-400 mt-2">الملفات المدعومة: JPG, PNG, WEBP حتى 4 ميجابايت</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1">ملاحظة إضافية (اختياري)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="مثال: رقم الحوالة، اسم المحول، إلخ." class="w-full h-11 rounded-xl border border-slate-200 px-3 text-xs text-stone-900">
            </div>
        </div>

        <button type="submit" class="w-full rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white py-3.5 font-bold text-base shadow-sm transition-colors flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[20px]">send</span>
            <span>تأكيد وإرسال إشعار شحن الرصيد</span>
        </button>
    </form>
</div>

<script>
function setTopupAmount(amt, btn) {
    document.getElementById('topup-amount-input').value = amt;
    document.querySelectorAll('.preset-amount-btn').forEach(b => {
        b.classList.remove('border-emerald-500', 'bg-emerald-50/50', 'text-emerald-800');
        b.classList.add('border-slate-200', 'text-stone-800');
    });
    btn.classList.remove('border-slate-200', 'text-stone-800');
    btn.classList.add('border-emerald-500', 'bg-emerald-50/50', 'text-emerald-800');
}

function togglePaymentCard(radio) {
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('border-emerald-500', 'bg-emerald-50/40');
        card.classList.add('border-slate-200');
    });
    if (radio.checked) {
        radio.closest('.payment-method-card').classList.add('border-emerald-500', 'bg-emerald-50/40');
        radio.closest('.payment-method-card').classList.remove('border-slate-200');
    }
}
</script>
@endsection
