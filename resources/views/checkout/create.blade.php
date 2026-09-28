@extends('layouts.public')

@section('title', 'تأكيد الطلب')
@section('hideFloatingCart', true)
@section('hideWhatsApp', true)

@section('content')
<div class="mx-auto max-w-2xl px-3 sm:px-4 py-3 sm:py-6 pb-36">

    {{-- Main Checkout Form --}}
    <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Hidden Form Fields submitted to Laravel Controller --}}
        <input type="hidden" id="addr" name="address_details" value="{{ old('address_details', auth()->user()->address ?? ($addresses->first()->details ?? 'غزة - شارع عمر المختار')) }}">
        <input type="hidden" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '0590000000') }}">
        <input type="hidden" id="selected-area" name="area" value="{{ old('area', $selectedAreaKey ?? ($addresses->first()->area ?? 'الرمال')) }}">
        <input type="hidden" id="payment-method-input" name="payment_method" value="{{ old('payment_method', 'receipt') }}">

        {{-- ========================================================================= --}}
        {{-- STEP 2: تأكيد الطلب (Order Confirmation)                                  --}}
        {{-- ========================================================================= --}}
        <div id="checkout-step-2" class="space-y-3.5">

            {{-- 1. Contact Information Card (معلومات التواصل) --}}
            <div class="bg-white rounded-2xl p-4 border border-slate-100/80 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-3">
                <div class="flex items-center gap-1.5 text-xs font-bold text-stone-800">
                    <span class="material-symbols-outlined text-primary text-[18px]">account_circle</span>
                    <span>معلومات التواصل</span>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-stone-50/80 border border-slate-100">
                    <div class="w-11 h-11 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">person</span>
                    </div>
                    <div class="space-y-0.5 min-w-0 flex-1 text-right">
                        <h4 class="font-extrabold text-sm text-stone-900 leading-tight truncate">
                            {{ auth()->user()->name ?? 'محمد احمد' }}
                        </h4>
                        <p class="font-mono text-xs text-stone-500" dir="ltr" style="text-align: right;">
                            {{ auth()->user()->phone ?? '0590000000' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- 2. Delivery Address Card (عنوان التوصيل) --}}
            <div class="bg-white rounded-2xl p-4 border border-slate-100/80 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-stone-800">
                        <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                        <span>عنوان التوصيل</span>
                    </div>
                    <button type="button" 
                            onclick="openAddressModal()" 
                            class="text-xs font-bold text-primary hover:text-primary-container flex items-center gap-0.5 cursor-pointer">
                        <span>تغيير</span>
                    </button>
                </div>

                {{-- Selected Address Box --}}
                <div class="rounded-xl border border-primary/30 bg-primary/5 p-3.5 flex items-center justify-between">
                    <div class="space-y-1 min-w-0 pr-1">
                        <h4 id="card-area-name" class="font-extrabold text-sm text-stone-900 leading-tight">
                            {{ $selectedAreaKey ?? ($addresses->first()->area ?? 'الرمال') }}
                        </h4>
                        <p id="card-address-details" class="text-xs text-stone-600 truncate">
                            {{ old('address_details', auth()->user()->address ?? ($addresses->first()->details ?? 'غزة - شارع عمر المختار')) }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">home</span>
                    </div>
                </div>

                {{-- Note to Captain --}}
                <div>
                    <label for="order-notes" class="block text-xs font-bold text-stone-800 mb-1.5">
                        ملاحظة للكابتن (لهذا الطلب فقط)
                    </label>
                    <input type="text" 
                           id="order-notes" 
                           name="notes" 
                           value="{{ old('notes') }}" 
                           placeholder="مثلاً: اتصل قبل الوصول" 
                           class="w-full h-12 rounded-xl bg-stone-50 border border-slate-200/80 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 px-3.5 text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all">
                </div>
            </div>

            {{-- 3. Coupon Code Card (كود الخصم) --}}
            <div class="bg-white rounded-2xl p-4 border border-slate-100/80 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-stone-800">
                        <span class="material-symbols-outlined text-primary text-[18px]">local_offer</span>
                        <span>كود الخصم</span>
                    </div>
                    <span id="coupon-status-badge" class="{{ ($appliedCoupon || ($quote['coupon'] ?? null)) ? '' : 'hidden' }} text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">check</span>
                        <span id="coupon-status-text">مفعّل: {{ $appliedCoupon['code'] ?? ($quote['coupon']['code'] ?? '') }}</span>
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <input type="text" 
                           id="coupon-code-input" 
                           name="coupon_code" 
                           value="{{ old('coupon_code', $appliedCoupon['code'] ?? ($quote['coupon']['code'] ?? '')) }}" 
                           placeholder="أدخل كود الخصم" 
                           class="w-full h-11 rounded-xl border border-slate-200 bg-stone-50/60 focus:bg-white px-3.5 text-sm text-stone-900 placeholder:text-stone-400 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all uppercase font-mono">
                    
                    <button type="button" 
                            id="apply-coupon-btn" 
                            onclick="handleApplyCoupon()" 
                            class="h-11 px-5 rounded-xl bg-primary/15 hover:bg-primary/25 text-primary font-extrabold text-xs transition-colors shrink-0 cursor-pointer">
                        تطبيق
                    </button>
                    <button type="button" 
                            id="remove-coupon-btn" 
                            onclick="handleRemoveCoupon()" 
                            class="{{ ($appliedCoupon || ($quote['coupon'] ?? null)) ? '' : 'hidden' }} h-11 px-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-xs shrink-0 cursor-pointer"
                            title="إلغاء الخصم">
                        إلغاء
                    </button>
                </div>
                <div id="coupon-feedback" class="text-xs font-semibold"></div>
            </div>

            {{-- 4. Order Summary Card (ملخص الطلب) --}}
            <div class="bg-white rounded-2xl p-4 border border-slate-100/80 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-3">
                <div class="flex items-center gap-1.5 text-xs font-bold text-stone-800">
                    <span class="material-symbols-outlined text-primary text-[18px]">shopping_bag</span>
                    <span>ملخص الطلب</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-1.5 font-bold text-stone-900 pb-1.5 border-b border-slate-100">
                        <span class="material-symbols-outlined text-stone-400 text-[16px]">storefront</span>
                        <span>{{ $quote['restaurant']->name }}</span>
                    </div>

                    @foreach($quote['lines'] as $line)
                        <div class="flex items-center justify-between text-stone-800 py-0.5">
                            <div class="flex items-center gap-1">
                                <span class="font-mono text-stone-400">{{ $line['qty'] }}x</span>
                                <span class="font-medium">{{ $line['item']->name }}</span>
                                @if(!empty($line['notes']))
                                    <span class="text-[10px] text-stone-600 bg-stone-100 px-1 rounded">({{ $line['notes'] }})</span>
                                @endif
                            </div>
                            <span class="font-mono font-bold">{{ number_format($line['line_total'], 2) }} ₪</span>
                        </div>
                    @endforeach

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between font-bold text-stone-900">
                        <span>إجمالي {{ $quote['restaurant']->name }}</span>
                        <span class="font-mono">{{ number_format($quote['subtotal'], 2) }} ₪</span>
                    </div>
                </div>
            </div>

            {{-- 5. Bill Breakdown Card (تفاصيل الفاتورة) --}}
            <div class="bg-white rounded-2xl p-4 border border-slate-100/80 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-2.5 text-xs">
                <div class="flex items-center justify-between text-stone-600">
                    <span>السعر الأصلي</span>
                    <span class="font-mono font-bold text-stone-900">{{ number_format($quote['subtotal'], 2) }} ₪</span>
                </div>

                {{-- Discount row --}}
                <div id="summary-discount-row" class="flex items-center justify-between text-emerald-600 font-bold {{ ($quote['discount_amount'] > 0) ? '' : 'hidden' }}">
                    <span id="summary-discount-label">
                        @if(($quote['discount_percent'] ?? 0) > 0)
                            نسبة الخصم {{ $quote['discount_percent'] }}%
                        @else
                            الخصم
                        @endif
                    </span>
                    <span class="font-mono">− <span id="summary-discount-val">{{ number_format($quote['discount_amount'] ?? 0, 2) }}</span> ₪</span>
                </div>

                <div class="flex items-center justify-between text-stone-600">
                    <span>التوصيل</span>
                    <span id="summary-delivery-wrap" class="font-mono font-bold text-stone-900">
                        <span id="summary-delivery-val">{{ number_format($quote['delivery_fee'], 2) }}</span> ₪
                    </span>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-base font-extrabold text-stone-900">
                    <span>السعر النهائي</span>
                    <span class="font-mono text-primary font-black text-lg">
                        <span id="summary-total-val">{{ number_format($quote['total'], 2) }}</span> ₪
                    </span>
                </div>
            </div>

            {{-- Step 2 Floating Action Button: Fixed above bottom navigation (NO arrow, Dark Orange) --}}
            <div class="fixed bottom-[74px] inset-x-0 px-3 sm:px-4 z-40 max-w-2xl mx-auto pointer-events-none">
                <button type="button" 
                        onclick="goToStep(3)" 
                        class="pointer-events-auto w-full h-14 rounded-2xl bg-primary hover:bg-primary-container active:scale-[0.99] text-white font-extrabold text-base flex items-center justify-between px-5 shadow-[0_8px_24px_rgba(163,57,0,0.35)] transition-all cursor-pointer">
                    <span>متابعة لطريقة الدفع</span>
                    <span class="font-mono text-sm bg-black/20 px-3 py-1 rounded-xl" id="step2-btn-total">{{ number_format($quote['total'], 2) }} ₪</span>
                </button>
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 3: الدفع (Payment & Transfer with Sofra Gaza's Real Settings)         --}}
        {{-- ========================================================================= --}}
        <div id="checkout-step-3" class="hidden space-y-4">

            {{-- 1. Amount to Transfer Card (المبلغ المطلوب تحويله) --}}
            <div class="rounded-2xl border border-primary/30 bg-primary/5 p-4 text-stone-900 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-primary">المبلغ المطلوب تحويله</span>
                    <button type="button" 
                            onclick="copyAmount()" 
                            class="w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 flex items-center justify-center transition-colors cursor-pointer" 
                            title="نسخ المبلغ">
                        <span class="material-symbols-outlined text-[18px]">content_copy</span>
                    </button>
                </div>
                
                <div class="text-3xl font-black text-primary font-mono my-1 flex items-baseline gap-1">
                    <span id="step3-amount-val">{{ number_format($quote['total'], 2) }}</span>
                    <span class="text-xl">₪</span>
                </div>

                <div class="border-t border-dashed border-primary/20 my-2.5"></div>

                <div class="flex items-center justify-between text-xs text-stone-600">
                    <span>المجموع <span class="font-mono font-bold text-stone-800">{{ number_format($quote['subtotal'], 2) }} ₪</span></span>
                    <span>التوصيل <span class="font-mono font-bold text-stone-800" id="step3-delivery-val">{{ number_format($quote['delivery_fee'], 2) }} ₪</span></span>
                </div>
            </div>

            {{-- 2. Payment Methods Selection (اختر طريقة الدفع) --}}
            <div class="space-y-2.5">
                <div class="flex items-center gap-1.5 text-xs font-extrabold text-stone-900 px-1">
                    <span class="material-symbols-outlined text-primary text-[18px]">credit_card</span>
                    <span>اختر طريقة الدفع</span>
                </div>

                {{-- Option A: Bank of Palestine (بنك فلسطين) using Sofra Gaza Settings --}}
                <div class="rounded-2xl border-2 transition-all overflow-hidden bg-white {{ old('payment_submethod', 'bop') === 'bop' ? 'border-primary shadow-xs' : 'border-slate-200' }}" id="method-card-bop">
                    <div class="p-3.5 flex items-center justify-between cursor-pointer" onclick="selectPaymentSubmethod('bop')">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ asset('images/payments/bop.png') }}" alt="Bank of Palestine" class="w-7 h-7 rounded-lg object-contain">
                            <div>
                                <h4 class="font-extrabold text-sm text-stone-900">{{ $paymentAccounts['bank_name'] ?? 'بنك فلسطين' }}</h4>
                                <p class="text-xs text-stone-400 mt-0.5">تحويل لحساب البنك والآيبان المعتمد</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center {{ old('payment_submethod', 'bop') === 'bop' ? 'border-primary' : 'border-slate-300' }}" id="radio-bop">
                            <div class="w-2.5 h-2.5 rounded-full bg-primary {{ old('payment_submethod', 'bop') === 'bop' ? '' : 'hidden' }}" id="radio-bop-dot"></div>
                        </div>
                    </div>

                    {{-- Expanded Details Box for BOP --}}
                    <div id="details-bop" class="p-4 bg-[#232029] text-white border-t border-slate-800 space-y-3">
                        <div class="flex items-center justify-between border-b border-white/10 pb-2">
                            <span class="text-xs font-bold text-primary">{{ $paymentAccounts['bank_name'] ?? 'بنك فلسطين' }}</span>
                            <span class="text-[11px] text-stone-400">حساب معتمد لسفرة غزة</span>
                        </div>

                        {{-- Bank Account Number --}}
                        <div class="space-y-1">
                            <span class="text-[11px] text-stone-400 block">رقم الحساب</span>
                            <div class="flex items-center justify-between gap-2 bg-white/5 rounded-xl p-2 px-3 border border-white/10">
                                <span class="font-mono font-bold text-base tracking-wider" dir="ltr">{{ $paymentAccounts['bank_account_number'] }}</span>
                                <button type="button" 
                                        onclick="copyText('{{ $paymentAccounts['bank_account_number'] }}', 'رقم حساب بنك فلسطين')" 
                                        class="p-1 rounded-md text-stone-300 hover:text-white hover:bg-white/10 transition-colors cursor-pointer" 
                                        title="نسخ الرقم">
                                    <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                </button>
                            </div>
                        </div>

                        {{-- Bank IBAN --}}
                        @if(!empty($paymentAccounts['bank_iban']))
                            <div class="space-y-1">
                                <span class="text-[11px] text-stone-400 block">رقم الآيبان (IBAN)</span>
                                <div class="flex items-center justify-between gap-2 bg-white/5 rounded-xl p-2 px-3 border border-white/10">
                                    <span class="font-mono font-bold text-xs tracking-wider truncate" dir="ltr">{{ $paymentAccounts['bank_iban'] }}</span>
                                    <button type="button" 
                                            onclick="copyText('{{ $paymentAccounts['bank_iban'] }}', 'رقم الآيبان')" 
                                            class="p-1 rounded-md text-stone-300 hover:text-white hover:bg-white/10 transition-colors cursor-pointer shrink-0" 
                                            title="نسخ الآيبان">
                                        <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Beneficiary Name --}}
                        <div class="text-xs text-stone-300 pt-1">
                            <span>اسم المستفيد:</span>
                            <strong class="text-white">{{ $paymentAccounts['bank_beneficiary_name'] }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Option B: Jawwal Pay (جوال باي) using Sofra Gaza Settings --}}
                <div class="rounded-2xl border-2 transition-all overflow-hidden bg-white border-slate-200" id="method-card-jawwalpay">
                    <div class="p-3.5 flex items-center justify-between cursor-pointer" onclick="selectPaymentSubmethod('jawwalpay')">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ asset('images/payments/jawwalpay.png') }}" alt="Jawwal Pay" class="w-7 h-7 rounded-lg object-contain">
                            <div>
                                <h4 class="font-extrabold text-sm text-stone-900">جوال باي (Jawwal Pay)</h4>
                                <p class="text-xs text-stone-400 mt-0.5">تحويل عبر محفظة جوال باي</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center" id="radio-jawwalpay">
                            <div class="w-2.5 h-2.5 rounded-full bg-primary hidden" id="radio-jawwalpay-dot"></div>
                        </div>
                    </div>

                    <div id="details-jawwalpay" class="hidden p-4 bg-[#232029] text-white border-t border-slate-800 space-y-3">
                        <div class="space-y-1">
                            <span class="text-[11px] text-stone-400 block">رقم المحفظة / الجوال</span>
                            <div class="flex items-center justify-between gap-2 bg-white/5 rounded-xl p-2 px-3 border border-white/10">
                                <span class="font-mono font-bold text-base tracking-wider" dir="ltr">{{ $paymentAccounts['jawwal_pay_number'] }}</span>
                                <button type="button" 
                                        onclick="copyText('{{ $paymentAccounts['jawwal_pay_number'] }}', 'رقم محفظة جوال باي')" 
                                        class="p-1 rounded-md text-stone-300 hover:text-white hover:bg-white/10 transition-colors cursor-pointer" 
                                        title="نسخ الرقم">
                                    <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                </button>
                            </div>
                        </div>
                        <div class="text-xs text-stone-300">
                            <span>اسم المحفظة / المستفيد:</span>
                            <strong class="text-white">{{ $paymentAccounts['jawwal_pay_name'] }}</strong>
                        </div>
                        @if(!empty($paymentAccounts['jawwal_pay_qr_url']))
                            <div class="bg-white rounded-xl p-3 text-stone-900 text-center max-w-[200px] mx-auto">
                                <img src="{{ $paymentAccounts['jawwal_pay_qr_url'] }}" alt="QR Code جوال باي" class="w-32 h-32 mx-auto object-contain">
                                <p class="text-[10px] text-stone-500 mt-1 font-bold">امسح الكود للدفع</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Option C: PalPay (بال باي) using Sofra Gaza Settings --}}
                <div class="rounded-2xl border-2 transition-all overflow-hidden bg-white border-slate-200" id="method-card-palpay">
                    <div class="p-3.5 flex items-center justify-between cursor-pointer" onclick="selectPaymentSubmethod('palpay')">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ asset('images/payments/palpay.png') }}" alt="PalPay" class="w-7 h-7 rounded-lg object-contain">
                            <div>
                                <h4 class="font-extrabold text-sm text-stone-900">محفظة بال باي (PalPay)</h4>
                                <p class="text-xs text-stone-400 mt-0.5">تحويل عبر محفظتي بال باي</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center" id="radio-palpay">
                            <div class="w-2.5 h-2.5 rounded-full bg-primary hidden" id="radio-palpay-dot"></div>
                        </div>
                    </div>

                    <div id="details-palpay" class="hidden p-4 bg-[#232029] text-white border-t border-slate-800 space-y-3">
                        <div class="space-y-1">
                            <span class="text-[11px] text-stone-400 block">رقم المحفظة / الجوال</span>
                            <div class="flex items-center justify-between gap-2 bg-white/5 rounded-xl p-2 px-3 border border-white/10">
                                <span class="font-mono font-bold text-base tracking-wider" dir="ltr">{{ $paymentAccounts['palpay_number'] }}</span>
                                <button type="button" 
                                        onclick="copyText('{{ $paymentAccounts['palpay_number'] }}', 'رقم محفظة بال باي')" 
                                        class="p-1 rounded-md text-stone-300 hover:text-white hover:bg-white/10 transition-colors cursor-pointer" 
                                        title="نسخ الرقم">
                                    <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                </button>
                            </div>
                        </div>
                        <div class="text-xs text-stone-300">
                            <span>اسم المستفيد:</span>
                            <strong class="text-white">{{ $paymentAccounts['palpay_name'] }}</strong>
                        </div>
                        @if(!empty($paymentAccounts['palpay_qr_url']))
                            <div class="bg-white rounded-xl p-3 text-stone-900 text-center max-w-[200px] mx-auto">
                                <img src="{{ $paymentAccounts['palpay_qr_url'] }}" alt="QR Code بال باي" class="w-32 h-32 mx-auto object-contain">
                                <p class="text-[10px] text-stone-500 mt-1 font-bold">امسح الكود للدفع</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Option D: Sofra Wallet (محفظة سفرة غزة) --}}
                <div class="rounded-2xl border-2 transition-all overflow-hidden bg-white border-slate-200" id="method-card-wallet">
                    <div class="p-3.5 flex items-center justify-between cursor-pointer" onclick="selectPaymentSubmethod('wallet')">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <h4 class="font-extrabold text-sm text-stone-900">محفظة سفرة غزة</h4>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">خصم فوري</span>
                                </div>
                                <p class="text-xs text-stone-500 mt-0.5">رصيدك: <strong class="font-mono text-emerald-700">{{ number_format(auth()->user()->wallet_balance, 2) }} ₪</strong></p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center" id="radio-wallet">
                            <div class="w-2.5 h-2.5 rounded-full bg-primary hidden" id="radio-wallet-dot"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Receipt Upload Section (صورة وصل التحويل) --}}
            <div id="receipt-upload-section" class="space-y-2 pt-1">
                <label class="block text-xs font-bold text-stone-800">
                    صورة وصل التحويل <span class="text-rose-500">*</span>
                </label>

                {{-- Drag & Drop Upload Card --}}
                <div class="border-2 border-dashed border-slate-200 hover:border-primary bg-white rounded-2xl p-5 text-center transition-all cursor-pointer group" onclick="document.getElementById('receipt-input').click()">
                    <input type="file" 
                           id="receipt-input" 
                           name="receipt" 
                           accept="image/*" 
                           class="sr-only" 
                           onchange="previewReceiptFile(this)">

                    <div id="receipt-upload-placeholder" class="space-y-1.5 py-1">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-[24px]">upload</span>
                        </div>
                        <h5 class="text-xs font-extrabold text-stone-900">ارفع صورة وصل التحويل</h5>
                        <p class="text-[11px] text-stone-400">لقطة شاشة من تطبيق البنك أو المحفظة</p>
                    </div>

                    <div id="receipt-preview-container" class="hidden flex items-center justify-between gap-3 p-2 bg-stone-50 rounded-xl border border-slate-200 text-right">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img id="receipt-preview-img" src="" alt="معاينة الوصل" class="w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                            <div class="min-w-0">
                                <p id="receipt-file-name" class="text-xs font-bold text-stone-900 truncate"></p>
                                <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">check</span>
                                    <span>تم اختيار الوصل</span>
                                </span>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="event.stopPropagation(); clearReceiptFile();" 
                                class="p-1.5 text-stone-400 hover:text-rose-600 rounded-lg transition-colors cursor-pointer" 
                                title="إلغاء">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>
                </div>
                @error('receipt')
                    <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror

                {{-- Problem uploading checkbox --}}
                <div class="rounded-xl border border-slate-200 bg-white p-3 flex items-center justify-between">
                    <label for="problem-uploading-check" class="text-xs font-bold text-stone-800 cursor-pointer flex-1">
                        هل تواجه مشكلة في رفع الوصل؟
                    </label>
                    <input type="checkbox" 
                           id="problem-uploading-check" 
                           onchange="handleProblemUploading(this)"
                           class="w-4 h-4 rounded text-primary focus:ring-primary border-slate-300 cursor-pointer">
                </div>
                <div id="problem-uploading-hint" class="hidden p-2.5 rounded-xl bg-primary/5 border border-primary/20 text-stone-800 text-[11px] leading-relaxed">
                    يمكنك تأكيد الطلب الآن وتزويد خدمة العملاء بالوصل عبر واتساب.
                </div>
            </div>

            {{-- 4. Verification Notice Card --}}
            <div class="rounded-xl bg-primary/5 border border-primary/20 p-3 text-[11px] text-stone-700 flex items-start gap-2 leading-relaxed">
                <span class="material-symbols-outlined text-primary text-[18px] shrink-0 mt-0.5">verified_user</span>
                <span>حوّل قيمة الطلب على إحدى الطرق أعلاه، ثم ارفع صورة وصل التحويل. تُراجع التحويلات من قبل الإدارة قبل تحويل الطلب للمطعم.</span>
            </div>

            {{-- Step 3 Floating Action Button: Fixed above bottom navigation (NO arrow, Dark Orange) --}}
            <div class="fixed bottom-[74px] inset-x-0 px-3 sm:px-4 z-40 max-w-2xl mx-auto pointer-events-none">
                <button type="submit" 
                        id="checkout-submit-btn" 
                        class="pointer-events-auto w-full h-14 rounded-2xl bg-primary hover:bg-primary-container active:scale-[0.99] text-white font-extrabold text-base shadow-[0_8px_24px_rgba(163,57,0,0.35)] flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <span id="checkout-submit-label">ارفع الوصل للمتابعة</span>
                </button>
            </div>

        </div>
    </form>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: تعديل العنوان (Address Edit Bottom Sheet)                          --}}
{{-- ========================================================================= --}}
<div id="address-modal" onclick="if(event.target === this) closeAddressModal()" class="hidden fixed inset-0 z-[70] bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl flex flex-col max-h-[88vh] sm:max-h-[90vh] shadow-2xl overflow-hidden">
        {{-- Handle bar & Header (Fixed) --}}
        <div class="px-5 pt-3.5 pb-2.5 border-b border-slate-100 shrink-0">
            <div class="w-12 h-1.5 bg-stone-300 rounded-full mx-auto sm:hidden mb-2.5"></div>
            <div class="flex items-center justify-between">
                <button type="button" onclick="closeAddressModal()" class="w-8 h-8 rounded-full hover:bg-stone-100 flex items-center justify-center text-stone-500 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
                <h3 class="text-sm font-extrabold text-stone-900">
                    تعديل العنوان
                </h3>
                <div class="w-8"></div>
            </div>
        </div>

        {{-- Form Fields (Scrollable) --}}
        <div class="p-5 space-y-3.5 overflow-y-auto flex-1 text-right">
            {{-- Saved addresses quick select if available --}}
            @if($addresses->isNotEmpty())
                <div>
                    <span class="text-[11px] font-bold text-stone-500 block mb-1.5">اختيار من عناويني المحفوظة:</span>
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                        @foreach($addresses as $addr)
                            <button type="button" 
                                    onclick="fillModalFromSaved({{ json_encode($addr->area) }}, {{ json_encode($addr->details) }}, {{ json_encode($addr->label) }})"
                                    class="shrink-0 px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-primary/10 hover:text-primary text-stone-700 text-xs font-bold border border-stone-200/60 transition-colors">
                                {{ $addr->label }} ({{ $addr->area }})
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 1. المنطقة * --}}
            <div>
                <label class="block text-xs font-bold text-stone-800 mb-1">المنطقة <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select id="modal-area-select" class="w-full h-12 rounded-xl border border-slate-200 bg-stone-50 focus:bg-white focus:border-primary pr-3.5 pl-10 text-sm font-bold text-stone-900 appearance-none outline-none">
                        @foreach($deliveryAreas as $area)
                            <option value="{{ $area['key'] }}" 
                                    data-fee="{{ $area['delivery_fee'] ?? \App\Models\Setting::deliveryFeeForArea($area['key']) }}"
                                    data-label="{{ $area['label'] }}"
                                    @selected(old('area', $selectedAreaKey ?? 'الرمال') === $area['key'])>
                                {{ $area['label'] }} (توصيل: {{ number_format($area['delivery_fee'] ?? \App\Models\Setting::deliveryFeeForArea($area['key']), 0) }} ₪)
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 material-symbols-outlined text-[20px] pointer-events-none">expand_more</span>
                </div>
            </div>

            {{-- 2. الشارع * --}}
            <div>
                <label class="block text-xs font-bold text-stone-800 mb-1">الشارع <span class="text-rose-500">*</span></label>
                <input type="text" id="modal-street" placeholder="شارع" class="w-full h-12 rounded-xl border border-primary/40 bg-white px-3.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary/20 outline-none">
            </div>

            {{-- 3. العمارة --}}
            <div>
                <label class="block text-xs font-bold text-stone-800 mb-1">العمارة</label>
                <input type="text" id="modal-building" placeholder="مثلاً: 5 أو برج الشروق" class="w-full h-12 rounded-xl border border-slate-200 bg-stone-50 focus:bg-white px-3.5 text-sm text-stone-900 outline-none">
            </div>

            {{-- 4. إرشادات الوصول (اختياري) --}}
            <div>
                <label class="block text-xs font-bold text-stone-800 mb-1">إرشادات الوصول (اختياري)</label>
                <textarea id="modal-directions" rows="2" placeholder="مثلاً: بجانب صيدلية النور، الطابق الثالث" class="w-full rounded-xl border border-slate-200 bg-stone-50 focus:bg-white p-3 text-sm text-stone-900 outline-none resize-none"></textarea>
                <p class="text-[11px] text-stone-400 mt-1">تبقى مع العنوان وتصل الكابتن في كل طلب - لا تُكتب من جديد.</p>
            </div>

            {{-- 5. اسم العنوان (اختياري) --}}
            <div>
                <label class="block text-xs font-bold text-stone-800 mb-1">اسم العنوان (اختياري)</label>
                <input type="text" id="modal-label" placeholder="مثلاً: البيت، الشغل، بيت الأهل" class="w-full h-12 rounded-xl border border-slate-200 bg-stone-50 focus:bg-white px-3.5 text-sm text-stone-900 outline-none">
            </div>
        </div>

        {{-- Footer / Action Button (Fixed at bottom of modal) --}}
        <div class="p-4 pt-3 bg-white border-t border-slate-100 shrink-0 pb-6 sm:pb-4">
            <button type="button" 
                    onclick="saveAddressFromModal()" 
                    class="w-full h-12.5 rounded-2xl bg-primary hover:bg-primary-container text-white font-extrabold text-sm shadow-md transition-all cursor-pointer flex items-center justify-center">
                حفظ التعديلات واستخدامه
            </button>
        </div>
    </div>
</div>

<script>
let currentStep = 2; // Step 1 is cart.index, Step 2 is order confirmation, Step 3 is payment
let currentSubtotal = {{ (float) $quote['subtotal'] }};
let currentDiscountAmount = {{ (float) ($quote['discount_amount'] ?? 0) }};
let currentDeliveryFee = {{ (float) ($quote['delivery_fee'] ?? 0) }};
const isFreeDelivery = {{ ($quote['membership']?->free_delivery ?? false) ? 'true' : 'false' }};
const userWalletBalance = {{ (float) auth()->user()->wallet_balance }};
const csrfToken = '{{ csrf_token() }}';

// -------------------------------------------------------------
// Step Switching & Header Integration
// -------------------------------------------------------------
function goToStep(step) {
    if (step === 3) {
        const addr = document.getElementById('addr').value.trim();
        if (addr.length < 3) {
            openAddressModal();
            return;
        }

        document.getElementById('checkout-step-2').classList.add('hidden');
        document.getElementById('checkout-step-3').classList.remove('hidden');

        // Header Title
        const titleEl = document.getElementById('mobile-subpage-title');
        if (titleEl) titleEl.textContent = 'الدفع';

        currentStep = 3;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else if (step === 2) {
        document.getElementById('checkout-step-3').classList.add('hidden');
        document.getElementById('checkout-step-2').classList.remove('hidden');

        const titleEl = document.getElementById('mobile-subpage-title');
        if (titleEl) titleEl.textContent = 'تأكيد الطلب';

        currentStep = 2;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        window.location.href = '{{ route("cart.index") }}';
    }
}

// Global hook for public layout back arrow
window.customBackHandler = function() {
    if (currentStep === 3) {
        goToStep(2);
    } else {
        window.location.href = '{{ route("cart.index") }}';
    }
};

// -------------------------------------------------------------
// Address Modal Functions
// -------------------------------------------------------------
function openAddressModal() {
    const modal = document.getElementById('address-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function closeAddressModal() {
    const modal = document.getElementById('address-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

function fillModalFromSaved(area, details, label) {
    const areaSel = document.getElementById('modal-area-select');
    if (areaSel && area) {
        areaSel.value = area;
    }
    const streetInput = document.getElementById('modal-street');
    if (streetInput) streetInput.value = details || '';
    const labelInput = document.getElementById('modal-label');
    if (labelInput) labelInput.value = label || '';
}

function saveAddressFromModal() {
    const areaSel = document.getElementById('modal-area-select');
    const street = document.getElementById('modal-street').value.trim();
    const building = document.getElementById('modal-building').value.trim();
    const directions = document.getElementById('modal-directions').value.trim();

    if (!street) {
        alert('يرجى إدخال اسم الشارع على الأقل.');
        return;
    }

    let fullDetails = street;
    if (building) fullDetails += '، ' + building;
    if (directions) fullDetails += ' (' + directions + ')';

    // Update hidden input and display card
    document.getElementById('addr').value = fullDetails;
    document.getElementById('card-address-details').textContent = fullDetails;

    if (areaSel) {
        const selectedArea = areaSel.value;
        const opt = areaSel.options[areaSel.selectedIndex];
        const fee = parseFloat(opt.dataset.fee || 0);

        document.getElementById('selected-area').value = selectedArea;
        document.getElementById('card-area-name').textContent = opt.dataset.label || selectedArea;

        currentDeliveryFee = isFreeDelivery ? 0 : fee;

        // Sync backend session
        fetch('{{ route("delivery-area.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ area: selectedArea })
        }).catch(() => {});
    }

    recalculateTotal();
    closeAddressModal();
}

// -------------------------------------------------------------
// Payment Submethods in Step 3
// -------------------------------------------------------------
function selectPaymentSubmethod(method) {
    const methods = ['bop', 'jawwalpay', 'palpay', 'wallet'];
    const paymentMethodInput = document.getElementById('payment-method-input');
    const receiptSection = document.getElementById('receipt-upload-section');
    const submitLabel = document.getElementById('checkout-submit-label');

    methods.forEach(m => {
        const card = document.getElementById('method-card-' + m);
        const radio = document.getElementById('radio-' + m);
        const dot = document.getElementById('radio-' + m + '-dot');
        const details = document.getElementById('details-' + m);

        if (m === method) {
            if (card) {
                card.classList.add('border-primary', 'shadow-xs');
                card.classList.remove('border-slate-200');
            }
            if (radio) radio.classList.add('border-primary');
            if (dot) dot.classList.remove('hidden');
            if (details) details.classList.remove('hidden');
        } else {
            if (card) {
                card.classList.remove('border-primary', 'shadow-xs');
                card.classList.add('border-slate-200');
            }
            if (radio) radio.classList.remove('border-primary');
            if (dot) dot.classList.add('hidden');
            if (details) details.classList.add('hidden');
        }
    });

    if (method === 'wallet') {
        paymentMethodInput.value = 'wallet';
        if (receiptSection) receiptSection.classList.add('hidden');
        if (submitLabel) submitLabel.textContent = 'تأكيد ودفع من المحفظة';
        document.getElementById('receipt-input').required = false;
    } else {
        paymentMethodInput.value = 'receipt';
        if (receiptSection) receiptSection.classList.remove('hidden');
        if (submitLabel) submitLabel.textContent = 'ارفع الوصل للمتابعة';
        const problemCheck = document.getElementById('problem-uploading-check');
        document.getElementById('receipt-input').required = !(problemCheck && problemCheck.checked);
    }
}

function handleProblemUploading(checkbox) {
    const hint = document.getElementById('problem-uploading-hint');
    const receiptInput = document.getElementById('receipt-input');
    if (checkbox.checked) {
        if (hint) hint.classList.remove('hidden');
        if (receiptInput) receiptInput.required = false;
    } else {
        if (hint) hint.classList.add('hidden');
        if (receiptInput && document.getElementById('payment-method-input').value === 'receipt') {
            receiptInput.required = true;
        }
    }
}

// -------------------------------------------------------------
// Financial Recalculation
// -------------------------------------------------------------
function recalculateTotal() {
    const total = Math.max(0, currentSubtotal - currentDiscountAmount + currentDeliveryFee);

    // Step 2 values
    const delVal = document.getElementById('summary-delivery-val');
    const totalVal = document.getElementById('summary-total-val');
    const step2BtnTotal = document.getElementById('step2-btn-total');

    if (delVal) {
        delVal.textContent = currentDeliveryFee.toFixed(2);
    }
    if (totalVal) {
        totalVal.textContent = total.toFixed(2);
    }
    if (step2BtnTotal) {
        step2BtnTotal.textContent = `${total.toFixed(2)} ₪`;
    }

    // Step 3 values
    const step3Amount = document.getElementById('step3-amount-val');
    const step3Del = document.getElementById('step3-delivery-val');
    if (step3Amount) {
        step3Amount.textContent = total.toFixed(2);
    }
    if (step3Del) {
        step3Del.textContent = `${currentDeliveryFee.toFixed(2)} ₪`;
    }
}

// -------------------------------------------------------------
// Coupon Handling
// -------------------------------------------------------------
function handleApplyCoupon() {
    const input = document.getElementById('coupon-code-input');
    const feedback = document.getElementById('coupon-feedback');
    const badge = document.getElementById('coupon-status-badge');
    const badgeText = document.getElementById('coupon-status-text');
    const removeBtn = document.getElementById('remove-coupon-btn');
    const discountRow = document.getElementById('summary-discount-row');
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
            feedback.textContent = 'تعذر فحص الكود، يرجى المحاولة ثانية.';
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

// -------------------------------------------------------------
// Receipt Image Preview
// -------------------------------------------------------------
function previewReceiptFile(input) {
    const previewContainer = document.getElementById('receipt-preview-container');
    const previewImg = document.getElementById('receipt-preview-img');
    const fileName = document.getElementById('receipt-file-name');
    const placeholder = document.getElementById('receipt-upload-placeholder');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            fileName.textContent = file.name;
            previewContainer.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function clearReceiptFile() {
    const input = document.getElementById('receipt-input');
    const previewContainer = document.getElementById('receipt-preview-container');
    const placeholder = document.getElementById('receipt-upload-placeholder');
    if (input) input.value = '';
    if (previewContainer) previewContainer.classList.add('hidden');
    if (placeholder) placeholder.classList.remove('hidden');
}

// -------------------------------------------------------------
// Copy Utilities
// -------------------------------------------------------------
function copyAmount() {
    const totalVal = document.getElementById('summary-total-val').textContent.trim();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(totalVal).then(() => {
            showToast('تم نسخ المبلغ المطلوب: ' + totalVal + ' ₪');
        });
    }
}

function copyText(text, label) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('تم نسخ ' + (label || 'الرقم') + ' بنجاح');
        });
    }
}

function showToast(msg) {
    const toast = document.getElementById('app-toast');
    if (toast) {
        const txt = toast.querySelector('.app-toast__text');
        if (txt) txt.textContent = msg;
        toast.hidden = false;
        setTimeout(() => { toast.hidden = true; }, 2500);
    } else {
        alert(msg);
    }
}

// Auto open Step 3 if server errors relate to payment or receipt
@if($errors->has('receipt') || old('payment_method') === 'receipt')
document.addEventListener('DOMContentLoaded', function() {
    goToStep(3);
});
@endif
</script>
@endsection
