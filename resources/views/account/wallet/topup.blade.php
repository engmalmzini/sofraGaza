@extends('layouts.public')

@section('title', 'شحن رصيد المحفظة')

@section('content')
<div class="mx-auto max-w-3xl px-margin lg:px-margin-desktop py-4 sm:py-6 lg:py-8">
    {{-- Top Navigation & Header --}}
    <div class="mb-6">
        <div class="hidden lg:block mb-3">
            <a href="{{ route('account.wallet') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-600 hover:text-primary transition-colors bg-white px-3.5 py-1.5 rounded-full border border-stone-200/80 shadow-2xs">
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                <span>العودة للمحفظة</span>
            </a>
        </div>

        <div class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-500/10 border border-amber-500/20 px-3 py-1 rounded-full mb-2">
            <span class="material-symbols-outlined text-[16px] text-amber-600">add_card</span>
            <span>شحن رصيد إلكتروني</span>
        </div>
        <h1 class="font-headline-md text-2xl lg:text-[30px] font-black text-stone-900 tracking-tight">شحن رصيد المحفظة</h1>
        <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-xl leading-relaxed">
            اختر المبلغ المراد شحنه، حوّل عبر وسيلة الدفع المعتمدة، ثم أرفق إشعار التحويل ليتم اعتماد الرصيد في حسابك فوراً.
        </p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm">
            <div class="flex items-center gap-2 font-bold mb-1">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <span>يرجى تصحيح الأخطاء التالية:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-red-700 mr-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('account.wallet.topup.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- 1. Amount Selection --}}
        <div class="rounded-3xl bg-white border border-stone-200/80 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-3.5">
                <div class="w-9 h-9 rounded-2xl bg-orange-100 text-primary flex items-center justify-center font-black text-sm shrink-0">
                    1
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-stone-900 flex items-center gap-2">
                        <span>اختر مبلغ الشحن</span>
                        <span class="text-xs font-bold text-primary bg-orange-50 px-2 py-0.5 rounded-md border border-orange-200/60">شيكل ₪</span>
                    </h2>
                    <p class="text-xs text-stone-500 mt-0.5">حدد أحد المبالغ السريعة أو أدخل مبلغاً مخصصاً</p>
                </div>
            </div>

            {{-- Quick Presets --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([50, 100, 200, 500] as $preset)
                    @php $isActive = old('amount', 100) == $preset; @endphp
                    <button type="button" 
                            data-amount="{{ $preset }}"
                            class="preset-amount-btn py-3 px-4 rounded-2xl border-2 {{ $isActive ? 'border-primary bg-orange-50/50 text-primary ring-2 ring-primary/20 shadow-2xs' : 'border-stone-200/90 bg-stone-50/40 text-stone-800 hover:border-primary/40 hover:bg-orange-50/20' }} font-bold text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5 active:scale-95"
                            onclick="setTopupAmount({{ $preset }}, this)">
                        <span class="text-xl font-mono font-black">{{ $preset }}</span>
                        <span class="text-xs {{ $isActive ? 'text-primary font-bold' : 'text-stone-500 font-medium' }}">شيكل ₪</span>
                    </button>
                @endforeach
            </div>

            {{-- Custom Input --}}
            <div class="pt-2">
                <label for="topup-amount-input" class="block text-xs font-bold text-stone-700 mb-1.5">
                    أو أدخل مبلغاً مخصصاً (الحد الأدنى 5 ₪ - حتى 5,000 ₪)
                </label>
                <div class="relative max-w-xs sm:max-w-sm">
                    <input type="number" 
                           id="topup-amount-input" 
                           name="amount" 
                           step="1" 
                           min="5" 
                           max="5000" 
                           value="{{ old('amount', 100) }}" 
                           required 
                           oninput="onAmountInputChange(this.value)"
                           class="w-full h-12 rounded-2xl border border-stone-200 pl-16 pr-4 font-mono font-black text-lg text-stone-900 focus:border-primary focus:ring-2 focus:ring-primary/20 bg-stone-50/30 transition-all">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 flex items-center gap-1 font-bold pointer-events-none select-none">
                        <span class="text-[11px] text-stone-400 font-mono">ILS</span>
                        <span class="text-base text-primary font-black">₪</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Payment Method Selection --}}
        <div class="rounded-3xl bg-white border border-stone-200/80 p-5 sm:p-6 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-3.5">
                <div class="w-9 h-9 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-sm shrink-0">
                    2
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-stone-900">طريقة التحويل المعتمدة</h2>
                    <p class="text-xs text-stone-500 mt-0.5">اختر وسيلة الدفع التي ستقوم بالتحويل من خلالها</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-3">
                @php $selectedMethod = old('payment_method', 'jawwal_pay'); @endphp

                {{-- Jawwal Pay --}}
                <label class="payment-method-card relative flex flex-col justify-between p-4 rounded-2xl border-2 {{ $selectedMethod === 'jawwal_pay' ? 'border-primary bg-orange-50/30 ring-2 ring-primary/20 shadow-2xs' : 'border-stone-200/90 bg-white hover:border-stone-300' }} cursor-pointer transition-all active:scale-[0.99]">
                    <input type="radio" name="payment_method" value="jawwal_pay" class="sr-only" {{ $selectedMethod === 'jawwal_pay' ? 'checked' : '' }} onchange="togglePaymentCard(this)">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-stone-50 border border-stone-200/60 p-1 flex items-center justify-center shrink-0">
                            <img src="{{ asset('images/payments/jawwalpay.png') }}" alt="Jawwal Pay" class="w-7 h-7 object-contain">
                        </div>
                        <div class="method-radio-indicator w-5 h-5 rounded-full border-2 {{ $selectedMethod === 'jawwal_pay' ? 'border-primary bg-primary text-white' : 'border-stone-300 bg-white' }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-[13px] {{ $selectedMethod === 'jawwal_pay' ? 'block' : 'hidden' }}">check</span>
                        </div>
                    </div>
                    <div>
                        <span class="block font-extrabold text-sm text-stone-900">جوال باي (Jawwal Pay)</span>
                        <span class="block text-xs text-stone-500 mt-0.5">تحويل فوري برقم الجوال</span>
                    </div>
                </label>

                {{-- PalPay --}}
                <label class="payment-method-card relative flex flex-col justify-between p-4 rounded-2xl border-2 {{ $selectedMethod === 'palpay' ? 'border-primary bg-orange-50/30 ring-2 ring-primary/20 shadow-2xs' : 'border-stone-200/90 bg-white hover:border-stone-300' }} cursor-pointer transition-all active:scale-[0.99]">
                    <input type="radio" name="payment_method" value="palpay" class="sr-only" {{ $selectedMethod === 'palpay' ? 'checked' : '' }} onchange="togglePaymentCard(this)">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-stone-50 border border-stone-200/60 p-1 flex items-center justify-center shrink-0">
                            <img src="{{ asset('images/payments/palpay.png') }}" alt="PalPay" class="w-7 h-7 object-contain">
                        </div>
                        <div class="method-radio-indicator w-5 h-5 rounded-full border-2 {{ $selectedMethod === 'palpay' ? 'border-primary bg-primary text-white' : 'border-stone-300 bg-white' }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-[13px] {{ $selectedMethod === 'palpay' ? 'block' : 'hidden' }}">check</span>
                        </div>
                    </div>
                    <div>
                        <span class="block font-extrabold text-sm text-stone-900">محفظة بال باي (PalPay)</span>
                        <span class="block text-xs text-stone-500 mt-0.5">تحويل برقم المحفظة أو QR</span>
                    </div>
                </label>

                {{-- Bank of Palestine --}}
                <label class="payment-method-card relative flex flex-col justify-between p-4 rounded-2xl border-2 {{ $selectedMethod === 'bank' ? 'border-primary bg-orange-50/30 ring-2 ring-primary/20 shadow-2xs' : 'border-stone-200/90 bg-white hover:border-stone-300' }} cursor-pointer transition-all active:scale-[0.99]">
                    <input type="radio" name="payment_method" value="bank" class="sr-only" {{ $selectedMethod === 'bank' ? 'checked' : '' }} onchange="togglePaymentCard(this)">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-stone-50 border border-stone-200/60 p-1 flex items-center justify-center shrink-0">
                            <img src="{{ asset('images/payments/bop.png') }}" alt="Bank of Palestine" class="w-7 h-7 object-contain">
                        </div>
                        <div class="method-radio-indicator w-5 h-5 rounded-full border-2 {{ $selectedMethod === 'bank' ? 'border-primary bg-primary text-white' : 'border-stone-300 bg-white' }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-[13px] {{ $selectedMethod === 'bank' ? 'block' : 'hidden' }}">check</span>
                        </div>
                    </div>
                    <div>
                        <span class="block font-extrabold text-sm text-stone-900">حساب بنك فلسطين</span>
                        <span class="block text-xs text-stone-500 mt-0.5">حوالة بنكية مباشرة أو آيبان</span>
                    </div>
                </label>
            </div>

            {{-- Include Payment Details Component --}}
            @include('partials.payment-instructions', ['paymentAccounts' => $paymentAccounts])
        </div>

        {{-- 3. Upload Receipt & Note --}}
        <div class="rounded-3xl bg-white border border-stone-200/80 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-3.5">
                <div class="w-9 h-9 rounded-2xl bg-stone-100 text-stone-800 flex items-center justify-center font-black text-sm shrink-0">
                    3
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-stone-900">إرفاق صورة إشعار الحوالة</h2>
                    <p class="text-xs text-stone-500 mt-0.5">صورة لقطة الشاشة لعملية التحويل البنكي أو المحفظة</p>
                </div>
            </div>

            {{-- Dropzone / Upload Box --}}
            <div>
                <label class="block text-xs font-bold text-stone-700 mb-2">صورة الإشعار (لقطة الشاشة للتحويل) <span class="text-primary">*</span></label>
                
                <div id="dropzone-container" 
                     onclick="document.getElementById('receipt-file-input').click()"
                     class="relative group rounded-3xl border-2 border-dashed border-stone-300 hover:border-primary p-6 text-center bg-stone-50/50 hover:bg-orange-50/20 transition-all cursor-pointer">
                    
                    <input type="file" 
                           id="receipt-file-input" 
                           name="receipt" 
                           accept="image/*" 
                           required 
                           onchange="handleReceiptPreview(this)"
                           class="sr-only">

                    {{-- Empty State --}}
                    <div id="upload-empty-state" class="space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 text-primary flex items-center justify-center mx-auto transition-transform group-hover:scale-110">
                            <span class="material-symbols-outlined text-[28px]">add_photo_alternate</span>
                        </div>
                        <div>
                            <span class="font-extrabold text-sm text-stone-800 block">اضغط هنا لرفع صورة الإشعار</span>
                            <span class="text-xs text-stone-500 block mt-0.5">أو اسحب الصورة وأفلتها هنا</span>
                        </div>
                        <span class="inline-block text-[11px] font-medium text-stone-400 bg-white border border-stone-200/80 px-2.5 py-1 rounded-full">
                            JPG, PNG, WEBP حتى 4 ميجابايت
                        </span>
                    </div>

                    {{-- Image Selected State (Preview) --}}
                    <div id="upload-preview-state" class="hidden flex-col items-center justify-center space-y-3">
                        <div class="relative">
                            <img id="receipt-preview-img" src="" alt="إشعار الحوالة" class="max-h-48 rounded-2xl border border-stone-200 shadow-sm object-contain mx-auto bg-white p-1">
                            <span class="absolute -top-2 -right-2 bg-emerald-600 text-white rounded-full p-1 shadow-sm flex items-center justify-center">
                                <span class="material-symbols-outlined text-[16px]">check</span>
                            </span>
                        </div>
                        <div class="text-xs font-bold text-stone-700" id="receipt-preview-filename"></div>
                        <button type="button" 
                                onclick="event.stopPropagation(); document.getElementById('receipt-file-input').click();" 
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-primary bg-orange-50 hover:bg-orange-100 px-3 py-1.5 rounded-xl border border-orange-200/80 transition-colors">
                            <span class="material-symbols-outlined text-[16px]">cached</span>
                            <span>تغيير الصورة</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Optional Note --}}
            <div class="pt-2">
                <label for="topup-notes-input" class="block text-xs font-bold text-stone-700 mb-1.5">ملاحظة إضافية (اختياري)</label>
                <input type="text" 
                       id="topup-notes-input" 
                       name="notes" 
                       value="{{ old('notes') }}" 
                       placeholder="مثال: رقم الحوالة، اسم المحوّل، أو أي ملاحظة للمراجعة..." 
                       class="w-full h-11 rounded-2xl border border-stone-200 px-4 text-xs text-stone-900 placeholder:text-stone-400 focus:border-primary focus:ring-2 focus:ring-primary/20 bg-stone-50/30 transition-all">
            </div>
        </div>

        {{-- Submit Button --}}
        <div>
            <button type="submit" class="w-full rounded-2xl bg-primary hover:bg-[#852e00] text-white py-4 px-6 font-bold text-base shadow-md shadow-primary/25 active:scale-[0.99] transition-all flex items-center justify-center gap-2.5 cursor-pointer">
                <span class="material-symbols-outlined text-[22px]">send</span>
                <span>تأكيد وإرسال إشعار شحن الرصيد</span>
            </button>
            <div class="flex items-center justify-center gap-1.5 text-xs text-stone-500 mt-3 font-medium">
                <span class="material-symbols-outlined text-[16px] text-amber-600">verified_user</span>
                <span>سيتم مراجعة إشعار الحوالة وإيداع الرصيد في محفظتك فوراً بعد التأكيد.</span>
            </div>
        </div>
    </form>
</div>

<script>
function setTopupAmount(amt, btn) {
    const input = document.getElementById('topup-amount-input');
    input.value = amt;
    highlightPresetButton(amt);
}

function onAmountInputChange(val) {
    highlightPresetButton(parseFloat(val));
}

function highlightPresetButton(amt) {
    document.querySelectorAll('.preset-amount-btn').forEach(b => {
        const btnAmt = parseFloat(b.dataset.amount);
        const subSpan = b.querySelector('span:last-child');
        if (btnAmt === amt) {
            b.classList.remove('border-stone-200/90', 'bg-stone-50/40', 'text-stone-800');
            b.classList.add('border-primary', 'bg-orange-50/50', 'text-primary', 'ring-2', 'ring-primary/20', 'shadow-2xs');
            if (subSpan) {
                subSpan.classList.remove('text-stone-500', 'font-medium');
                subSpan.classList.add('text-primary', 'font-bold');
            }
        } else {
            b.classList.remove('border-primary', 'bg-orange-50/50', 'text-primary', 'ring-2', 'ring-primary/20', 'shadow-2xs');
            b.classList.add('border-stone-200/90', 'bg-stone-50/40', 'text-stone-800');
            if (subSpan) {
                subSpan.classList.remove('text-primary', 'font-bold');
                subSpan.classList.add('text-stone-500', 'font-medium');
            }
        }
    });
}

function togglePaymentCard(radio) {
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-orange-50/30', 'ring-2', 'ring-primary/20', 'shadow-2xs');
        card.classList.add('border-stone-200/90', 'bg-white');
        
        const indicator = card.querySelector('.method-radio-indicator');
        if (indicator) {
            indicator.classList.remove('border-primary', 'bg-primary', 'text-white');
            indicator.classList.add('border-stone-300', 'bg-white');
            const checkIcon = indicator.querySelector('.material-symbols-outlined');
            if (checkIcon) checkIcon.classList.add('hidden');
        }
    });

    if (radio.checked) {
        const card = radio.closest('.payment-method-card');
        card.classList.add('border-primary', 'bg-orange-50/30', 'ring-2', 'ring-primary/20', 'shadow-2xs');
        card.classList.remove('border-stone-200/90', 'bg-white');

        const indicator = card.querySelector('.method-radio-indicator');
        if (indicator) {
            indicator.classList.add('border-primary', 'bg-primary', 'text-white');
            indicator.classList.remove('border-stone-300', 'bg-white');
            const checkIcon = indicator.querySelector('.material-symbols-outlined');
            if (checkIcon) checkIcon.classList.remove('hidden');
        }
    }
}

function handleReceiptPreview(input) {
    const emptyState = document.getElementById('upload-empty-state');
    const previewState = document.getElementById('upload-preview-state');
    const previewImg = document.getElementById('receipt-preview-img');
    const filenameLabel = document.getElementById('receipt-preview-filename');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            filenameLabel.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            emptyState.classList.add('hidden');
            previewState.classList.remove('hidden');
            previewState.classList.add('flex');
        };
        reader.readAsDataURL(file);
    }
}

window.customBackHandler = function() {
    window.location.href = "{{ route('account.wallet') }}";
};
</script>
@endsection
