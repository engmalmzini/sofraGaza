@extends('layouts.public')

@section('title', 'إتمام الطلب')

@section('content')
<div class="mx-auto grid max-w-5xl gap-6 px-margin lg:px-margin-desktop py-5 lg:py-10 lg:grid-cols-5">
    <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs lg:col-span-3">
        @csrf
        <h1 class="font-headline-md text-2xl font-bold text-stone-900">بيانات التوصيل والدفع</h1>
        @if($addresses->isNotEmpty())
            <div class="space-y-2">
                <div class="text-sm font-bold text-on-surface">عناوين محفوظة</div>
                @foreach($addresses as $address)
                    <button type="button" class="block w-full rounded-xl bg-surface-container-low p-3 text-right text-sm"
                        onclick="document.getElementById('addr').value = @json($address->details); document.getElementById('phone').value = @json($address->phone); @if($address->area) const sel = document.getElementById('checkout-area'); if(sel){ sel.value = @json($address->area); sel.dispatchEvent(new Event('change')); } @endif">
                        <strong>{{ $address->label }}</strong> — {{ $address->details }}
                        @if($address->area)
                            <span class="text-xs text-primary font-medium">({{ $address->area }})</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
        <div>
            <label class="mb-1 block text-sm font-bold flex items-center justify-between" for="checkout-area">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                    منطقة التوصيل
                </span>
                <span id="checkout-area-fee-badge" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-surface-container-high text-primary">
                    @if($quote['membership']?->free_delivery)
                        توصيل مجاني (VIP)
                    @else
                        رسوم التوصيل: {{ number_format($quote['delivery_fee'], 2) }} ₪
                    @endif
                </span>
            </label>
            <select id="checkout-area" name="area" class="w-full h-12 rounded-xl bg-surface-container-low border-none px-3 font-body-sm text-[15px] text-on-surface">
                @foreach($deliveryAreas as $area)
                    <option value="{{ $area['key'] }}" 
                            data-fee="{{ $area['delivery_fee'] ?? \App\Models\Setting::deliveryFeeForArea($area['key']) }}"
                            data-label="{{ $area['label'] }}"
                            @selected(old('area', $selectedAreaKey ?? ($deliveryArea['key'] ?? '')) === $area['key'])>
                        {{ $area['label'] }} (رسوم التوصيل: {{ number_format($area['delivery_fee'] ?? \App\Models\Setting::deliveryFeeForArea($area['key']), 0) }} ₪)
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-on-surface-variant">تختلف رسوم التوصيل بحسب المنطقة المحددة ويتم تعديل الإجمالي تلقائياً.</p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">العنوان بالتفصيل</label>
            <textarea id="addr" name="address_details" rows="4" class="w-full rounded-xl bg-surface-container-low border-none px-3 py-3" placeholder="الحي، الشارع، أقرب معلم، رقم البناية">{{ old('address_details') }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">رقم هاتف للتواصل</label>
            <input id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="w-full h-12 rounded-xl bg-surface-container-low border-none px-3">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">ملاحظات</label>
            <input name="notes" value="{{ old('notes') }}" class="w-full h-12 rounded-xl bg-surface-container-low border-none px-3">
        </div>

        {{-- Coupon / Discount Code (Optional) --}}
        <div class="rounded-2xl border border-slate-200 bg-surface-container-low/50 p-4 space-y-2.5">
            <div class="flex items-center justify-between">
                <label for="coupon-code-input" class="text-xs font-bold text-stone-900 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[18px]">local_offer</span>
                    <span>كود الخصم (اختياري إن وجد)</span>
                </label>
                <span id="coupon-status-badge" class="{{ ($appliedCoupon || ($quote['coupon'] ?? null)) ? '' : 'hidden' }} text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                    <span id="coupon-status-text">مفعّل: {{ $appliedCoupon['code'] ?? ($quote['coupon']['code'] ?? '') }}</span>
                </span>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text" 
                           id="coupon-code-input" 
                           name="coupon_code" 
                           value="{{ old('coupon_code', $appliedCoupon['code'] ?? ($quote['coupon']['code'] ?? '')) }}" 
                           placeholder="أدخل رمز الكوبون (مثلاً: GAZA10)" 
                           class="w-full h-11 uppercase rounded-xl border border-slate-200 bg-white px-3 font-mono font-bold text-sm tracking-wider text-stone-900 placeholder:font-sans placeholder:font-normal placeholder:text-xs placeholder:tracking-normal focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                </div>
                <button type="button" 
                        id="apply-coupon-btn" 
                        onclick="handleApplyCoupon()" 
                        class="h-11 px-4 rounded-xl bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs transition-colors shrink-0 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    <span>تطبيق</span>
                </button>
                <button type="button" 
                        id="remove-coupon-btn" 
                        onclick="handleRemoveCoupon()" 
                        class="{{ ($appliedCoupon || ($quote['coupon'] ?? null)) ? '' : 'hidden' }} h-11 px-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-xs transition-colors shrink-0" 
                        title="إلغاء كود الخصم">
                    إلغاء
                </button>
            </div>
            <div id="coupon-feedback" class="text-xs font-semibold"></div>
        </div>

        {{-- Payment Method Selection --}}
        <div class="space-y-3 pt-2">
            <label class="block text-sm font-bold text-stone-900">طريقة الدفع</label>

            @php
                $hasWalletSufficient = (float) auth()->user()->wallet_balance >= (float) $quote['total'];
                $defaultMethod = old('payment_method', $hasWalletSufficient ? 'wallet' : 'receipt');
            @endphp

            <div class="grid sm:grid-cols-2 gap-3">
                {{-- Wallet Option --}}
                <label id="payment-card-wallet" class="payment-card flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ $defaultMethod === 'wallet' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="payment_method" value="wallet" class="mt-1 text-emerald-600 focus:ring-emerald-500" @checked($defaultMethod === 'wallet') onchange="switchPaymentMethod('wallet')">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <span class="font-bold text-sm text-stone-900">رصيد المحفظة</span>
                            <span class="text-xs font-mono font-bold text-emerald-700">{{ number_format(auth()->user()->wallet_balance, 2) }} ₪</span>
                        </div>
                        <p class="text-[11px] text-stone-500 mt-0.5">خصم فوري من رصيدك المتاح دون انتظار تأكيد الحوالة.</p>
                        <div id="wallet-sufficient-alert" class="{{ $hasWalletSufficient ? 'hidden' : '' }} mt-2 text-[11px] text-amber-800 bg-amber-100/70 rounded-lg p-2 leading-relaxed">
                            رصيدك غير كافٍ لتغطية إجمالي الطلب. 
                            <a href="{{ route('account.wallet.topup') }}" target="_blank" class="font-bold underline text-emerald-700">شحن الرصيد الآن</a>
                        </div>
                        <div id="wallet-sufficient-badge" class="{{ $hasWalletSufficient ? '' : 'hidden' }} mt-2 text-[11px] text-emerald-800 bg-emerald-100/80 rounded-lg px-2 py-1 flex items-center gap-1 font-semibold">
                            <span class="material-symbols-outlined text-[15px]">check_circle</span>
                            <span>رصيدك كافٍ لتأكيد الطلب فوراً</span>
                        </div>
                    </div>
                </label>

                {{-- Direct Transfer (Bank / Jawwal Pay / PalPay) Option --}}
                <label id="payment-card-receipt" class="payment-card flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ $defaultMethod === 'receipt' ? 'border-primary bg-primary/5' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="payment_method" value="receipt" class="mt-1 text-primary focus:ring-primary" @checked($defaultMethod === 'receipt') onchange="switchPaymentMethod('receipt')">
                    <div class="min-w-0 flex-1">
                        <span class="font-bold text-sm text-stone-900">تحويل مباشر (جوال باي / بال باي / بنك فلسطين)</span>
                        <p class="text-[11px] text-stone-500 mt-0.5">تحويل مباشر وإرفاق صورة إشعار الحوالة لتأكيد الطلب.</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Direct Transfer Details & Receipt Upload Section --}}
        <div id="transfer-payment-details" class="{{ $defaultMethod === 'wallet' ? 'hidden' : '' }} space-y-4">
            @include('partials.payment-instructions', ['paymentAccounts' => $paymentAccounts, 'requiredAmount' => $quote['total']])

            <div>
                <label class="mb-1 block text-sm font-bold">صورة إشعار الحوالة</label>
                <input type="file" id="receipt-input" name="receipt" accept="image/*" class="w-full rounded-xl border border-dashed border-outline/40 p-3 text-sm bg-surface">
                <p class="mt-1 text-xs text-on-surface-variant">حوّل المبلغ النهائي أعلاه ثم ارفع صورة الإشعار. الطلب يبقى بانتظار التأكيد حتى المراجعة.</p>
            </div>
        </div>

        <button id="checkout-submit-btn" class="w-full rounded-xl bg-primary hover:bg-primary-container text-on-primary py-3.5 font-bold text-base transition-colors flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span id="checkout-submit-label">{{ $defaultMethod === 'wallet' ? 'تأكيد ودفع من المحفظة' : 'إرسال الطلب وإشعار الحوالة' }}</span>
        </button>
    </form>

    <aside class="rounded-2xl bg-primary p-5 text-on-primary lg:col-span-2 h-fit">
        <h2 class="font-bold text-lg">ملخص الطلب</h2>
        <p class="mt-1 text-sm text-on-primary/80">{{ $quote['restaurant']->name }}</p>
        <ul class="mt-4 space-y-2 text-sm">
            @foreach($quote['lines'] as $line)
                <li class="flex justify-between gap-2"><span>{{ $line['item']->name }} × {{ $line['qty'] }}</span><span>{{ number_format($line['line_total'], 2) }}</span></li>
            @endforeach
        </ul>
        <div class="mt-4 border-t border-white/15 pt-4 text-sm">
            <div class="flex justify-between"><span>السعر الأصلي</span><span>{{ number_format($quote['subtotal'], 2) }} <span class="ils">₪</span></span></div>
            
            {{-- Discount Summary Row --}}
            <div id="summary-discount-row" class="mt-2 flex justify-between {{ ($quote['discount_amount'] > 0) ? '' : 'hidden' }}">
                <span>
                    <span id="summary-discount-label">
                        @if(($quote['coupon_discount'] ?? 0) > 0 && ($quote['vip_discount'] ?? 0) > 0)
                            خصم VIP + كود ({{ $quote['coupon_code'] }})
                        @elseif(($quote['coupon_discount'] ?? 0) > 0)
                            كود الخصم ({{ $quote['coupon_code'] }})
                        @else
                            نسبة الخصم {{ $quote['discount_percent'] }}%
                        @endif
                    </span>
                </span>
                <span>− <span id="summary-discount-val">{{ number_format($quote['discount_amount'], 2) }}</span> <span class="ils">₪</span></span>
            </div>

            <div class="mt-2 flex justify-between">
                <span>التوصيل (<span id="summary-area-name">{{ $quote['delivery_area']['label'] ?? ($deliveryArea['label'] ?? '') }}</span>)</span>
                <span id="summary-delivery-wrap">
                    @if($quote['delivery_fee'])
                        <span id="summary-delivery-val">{{ number_format($quote['delivery_fee'], 2) }}</span> <span class="ils">₪</span>
                    @else
                        مجاني
                    @endif
                </span>
            </div>
            <div class="mt-4 flex justify-between text-lg font-bold">
                <span>السعر النهائي</span>
                <span><span id="summary-total-val">{{ number_format($quote['total'], 2) }}</span> <span class="ils">₪</span></span>
            </div>
            <p class="mt-3 text-tertiary-fixed font-medium">ستحصل على {{ $quote['points'] }} نقطة من هذه الطلبية بعد التسليم</p>
        </div>
    </aside>
</div>

<script>
let currentSubtotal = {{ (float) $quote['subtotal'] }};
let currentDiscountAmount = {{ (float) ($quote['discount_amount'] ?? 0) }};
let currentDeliveryFee = {{ (float) ($quote['delivery_fee'] ?? 0) }};
const isFreeDelivery = {{ ($quote['membership']?->free_delivery ?? false) ? 'true' : 'false' }};
const userWalletBalance = {{ (float) auth()->user()->wallet_balance }};
const csrfToken = '{{ csrf_token() }}';

document.addEventListener('DOMContentLoaded', function () {
    const areaSelect = document.getElementById('checkout-area');
    const areaBadge = document.getElementById('checkout-area-fee-badge');
    const areaName = document.getElementById('summary-area-name');
    const deliveryWrap = document.getElementById('summary-delivery-wrap');

    if (areaSelect) {
        areaSelect.addEventListener('change', function () {
            const opt = areaSelect.options[areaSelect.selectedIndex];
            if (!opt) return;

            currentDeliveryFee = isFreeDelivery ? 0 : parseFloat(opt.dataset.fee || 0);
            const label = opt.dataset.label || opt.text.split('(')[0].trim();

            if (areaBadge) {
                areaBadge.textContent = isFreeDelivery ? 'توصيل مجاني (VIP)' : `رسوم التوصيل: ${currentDeliveryFee.toFixed(2)} ₪`;
            }
            if (areaName) {
                areaName.textContent = label;
            }
            if (deliveryWrap) {
                if (isFreeDelivery || currentDeliveryFee === 0) {
                    deliveryWrap.innerHTML = 'مجاني';
                } else {
                    deliveryWrap.innerHTML = `${currentDeliveryFee.toFixed(2)} <span class="ils">₪</span>`;
                }
            }

            recalculateTotal();

            // Sync with backend session
            fetch('{{ route("delivery-area.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ area: opt.value })
            }).catch(() => {});
        });
    }
});

function recalculateTotal() {
    const total = Math.max(0, currentSubtotal - currentDiscountAmount + currentDeliveryFee);
    const totalVal = document.getElementById('summary-total-val');
    if (totalVal) {
        totalVal.textContent = total.toFixed(2);
    }

    // Check wallet sufficiency
    const alertBox = document.getElementById('wallet-sufficient-alert');
    const badgeBox = document.getElementById('wallet-sufficient-badge');
    if (userWalletBalance >= total) {
        if (alertBox) alertBox.classList.add('hidden');
        if (badgeBox) badgeBox.classList.remove('hidden');
    } else {
        if (alertBox) alertBox.classList.remove('hidden');
        if (badgeBox) badgeBox.classList.add('hidden');
    }
}

function handleApplyCoupon() {
    const input = document.getElementById('coupon-code-input');
    const feedback = document.getElementById('coupon-feedback');
    const badge = document.getElementById('coupon-status-badge');
    const badgeText = document.getElementById('coupon-status-text');
    const removeBtn = document.getElementById('remove-coupon-btn');
    const discountRow = document.getElementById('summary-discount-row');
    const discountLabel = document.getElementById('summary-discount-label');
    const discountVal = document.getElementById('summary-discount-val');

    const code = input.value.trim();
    if (!code) {
        if (feedback) {
            feedback.className = 'text-xs font-semibold text-rose-600';
            feedback.textContent = 'يرجى كتابة رمز الكود أولاً.';
        }
        return;
    }

    if (feedback) {
        feedback.className = 'text-xs text-stone-500';
        feedback.textContent = 'جارٍ التحقق من كود الخصم...';
    }

    fetch('{{ route("cart.coupon.apply") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ coupon_code: code })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (feedback) {
                feedback.className = 'text-xs font-semibold text-emerald-700';
                feedback.textContent = data.message;
            }
            if (badge) badge.classList.remove('hidden');
            if (badgeText) badgeText.textContent = `مفعّل: ${data.coupon.code}`;
            if (removeBtn) removeBtn.classList.remove('hidden');

            currentDiscountAmount = parseFloat(data.quote.discount_amount);
            if (discountRow) discountRow.classList.remove('hidden');
            if (discountLabel) discountLabel.textContent = `كود الخصم (${data.coupon.code})`;
            if (discountVal) discountVal.textContent = currentDiscountAmount.toFixed(2);

            recalculateTotal();
        } else {
            if (feedback) {
                feedback.className = 'text-xs font-semibold text-rose-600';
                feedback.textContent = data.message || 'كود الخصم غير صحيح.';
            }
        }
    })
    .catch(() => {
        if (feedback) {
            feedback.className = 'text-xs font-semibold text-rose-600';
            feedback.textContent = 'حدث خطأ أثناء فحص الكود. حاول ثانية.';
        }
    });
}

function handleRemoveCoupon() {
    const input = document.getElementById('coupon-code-input');
    const feedback = document.getElementById('coupon-feedback');
    const badge = document.getElementById('coupon-status-badge');
    const removeBtn = document.getElementById('remove-coupon-btn');
    const discountRow = document.getElementById('summary-discount-row');
    const discountVal = document.getElementById('summary-discount-val');

    fetch('{{ route("cart.coupon.remove") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            if (feedback) {
                feedback.className = 'text-xs font-semibold text-stone-600';
                feedback.textContent = 'تم إلغاء كود الخصم.';
            }
            if (badge) badge.classList.add('hidden');
            if (removeBtn) removeBtn.classList.add('hidden');

            currentDiscountAmount = parseFloat(data.quote.discount_amount);
            if (currentDiscountAmount > 0) {
                if (discountVal) discountVal.textContent = currentDiscountAmount.toFixed(2);
            } else {
                if (discountRow) discountRow.classList.add('hidden');
            }

            recalculateTotal();
        }
    });
}

function switchPaymentMethod(method) {
    const transferWrap = document.getElementById('transfer-payment-details');
    const receiptInput = document.getElementById('receipt-input');
    const submitLabel = document.getElementById('checkout-submit-label');
    const cardWallet = document.getElementById('payment-card-wallet');
    const cardReceipt = document.getElementById('payment-card-receipt');

    if (method === 'wallet') {
        if (transferWrap) transferWrap.classList.add('hidden');
        if (receiptInput) receiptInput.required = false;
        if (submitLabel) submitLabel.textContent = 'تأكيد ودفع من المحفظة';
        if (cardWallet) {
            cardWallet.classList.add('border-emerald-600', 'bg-emerald-50/50');
            cardWallet.classList.remove('border-slate-200');
        }
        if (cardReceipt) {
            cardReceipt.classList.remove('border-primary', 'bg-primary/5');
            cardReceipt.classList.add('border-slate-200');
        }
    } else {
        if (transferWrap) transferWrap.classList.remove('hidden');
        if (receiptInput) receiptInput.required = true;
        if (submitLabel) submitLabel.textContent = 'إرسال الطلب وإشعار الحوالة';
        if (cardReceipt) {
            cardReceipt.classList.add('border-primary', 'bg-primary/5');
            cardReceipt.classList.remove('border-slate-200');
        }
        if (cardWallet) {
            cardWallet.classList.remove('border-emerald-600', 'bg-emerald-50/50');
            cardWallet.classList.add('border-slate-200');
        }
    }
}
</script>
@endsection
