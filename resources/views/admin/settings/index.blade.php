@extends('layouts.admin')

@section('kicker', 'النظام')
@section('title', 'إعدادات المنصة العامة')

@section('content')
@php
    $deliveryFeeSetting = $settings->firstWhere('key', 'delivery_fee');
    $pointsEarnVal = $settings->firstWhere('key', 'points_per_amount')?->value ?? '1';
    $pointsRedeemVal = $settings->firstWhere('key', 'points_redeem_per_amount')?->value ?? '1';
    $pointsDeliveryVal = (string) ($settings->firstWhere('key', 'points_include_delivery')?->value ?? '1');
    $drinkPointsVal = $settings->firstWhere('key', 'drink_points')?->value ?? '20';
    $mealPointsVal = $settings->firstWhere('key', 'meal_points')?->value ?? '50';
    $referralInviterVal = $settings->firstWhere('key', 'referral_inviter_points')?->value ?? '50';
    $referralInviteeVal = $settings->firstWhere('key', 'referral_invitee_points')?->value ?? '50';
    $membershipExpiryWarningDaysVal = $settings->firstWhere('key', 'membership_expiry_warning_days')?->value ?? '3';

    $managedKeys = [
        'delivery_fee',
        'delivery_fees_by_area',
        'custom_delivery_areas',
        'bank_name',
        'bank_account_number',
        'bank_iban',
        'bank_beneficiary_name',
        'jawwal_pay_number',
        'jawwal_pay_name',
        'jawwal_pay_qr_path',
        'palpay_number',
        'palpay_name',
        'palpay_qr_path',
        'payment_instructions_note',
        'points_per_amount',
        'points_redeem_per_amount',
        'points_include_delivery',
        'drink_points',
        'meal_points',
        'referral_inviter_points',
        'referral_invitee_points',
        'restaurant_listing_days',
        'restaurant_expiry_warning_days',
        'membership_expiry_warning_days',
        'home_banners',
    ];

    $otherSettings = $settings->whereNotIn('key', $managedKeys);
@endphp

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="w-full max-w-none space-y-6 pb-20">
    @csrf

    {{-- 1. Application Segmented Tab Bar (Native App Style) --}}
    <div class="app-tab-bar-wrap">
        <div class="app-tab-bar" id="settings-tab-list" role="tablist">
            <button type="button" 
                    onclick="switchSettingsTab('payments')" 
                    id="tab-btn-payments"
                    class="app-tab-item is-active"
                    role="tab"
                    aria-selected="true">
                <span class="material-symbols-outlined app-tab-icon">payments</span>
                <span>حسابات التحويل والدفع</span>
            </button>
            <button type="button" 
                    onclick="switchSettingsTab('delivery')" 
                    id="tab-btn-delivery"
                    class="app-tab-item"
                    role="tab"
                    aria-selected="false">
                <span class="material-symbols-outlined app-tab-icon">local_shipping</span>
                <span>رسوم ومناطق التوصيل</span>
                <span class="app-tab-badge">{{ count($areasWithFees) }}</span>
            </button>
            <button type="button" 
                    onclick="switchSettingsTab('points')" 
                    id="tab-btn-points"
                    class="app-tab-item"
                    role="tab"
                    aria-selected="false">
                <span class="material-symbols-outlined app-tab-icon">stars</span>
                <span>نقاط الولاء</span>
            </button>
            <button type="button" 
                    onclick="switchSettingsTab('banners')" 
                    id="tab-btn-banners"
                    class="app-tab-item"
                    role="tab"
                    aria-selected="false">
                <span class="material-symbols-outlined app-tab-icon">view_carousel</span>
                <span>البانرات والإعلانات</span>
                <span class="app-tab-badge">{{ count($homeBanners) }}</span>
            </button>
            <button type="button" 
                    onclick="switchSettingsTab('system')" 
                    id="tab-btn-system"
                    class="app-tab-item"
                    role="tab"
                    aria-selected="false">
                <span class="material-symbols-outlined app-tab-icon">tune</span>
                <span>النظام والمدد</span>
            </button>
            <button type="button" 
                    onclick="toggleShowAllSettings(this)" 
                    id="btn-show-all"
                    class="app-tab-item app-tab-item--secondary" 
                    title="التبديل بين التبويبات الفردية أو عرض كافة الأقسام معاً">
                <span class="material-symbols-outlined app-tab-icon">unfold_more</span>
                <span>عرض الكل</span>
            </button>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TAB 1: حسابات الدفع والتحويل المعتمدة (Payment Accounts)    --}}
    {{-- ========================================================= --}}
    <div id="section-payments" class="settings-section space-y-6">
        <div class="admin-card !p-0 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-stone-900 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <span class="material-symbols-outlined text-primary text-[24px]">payments</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-stone-900">بيانات الدفع والتحويل المعتمدة</h2>
                        <p class="text-xs text-slate-500">تظهر هذه الحسابات للزبائن في الدفع المباشر، شحن المحفظة، واشتراكات العضويات</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-stone-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>3 طرق تحويل معتمدة</span>
                </span>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    {{-- 1. Jawwal Pay --}}
                    <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs space-y-4 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-[20px]">account_balance_wallet</span>
                                    <h3 class="font-bold text-sm text-stone-900">محفظة جوال باي (Jawwal Pay)</h3>
                                </div>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-stone-100 text-stone-800">محفظة إلكترونية</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">رقم المحفظة / الجوال</label>
                                <input type="text" 
                                       name="settings[jawwal_pay_number]" 
                                       value="{{ $paymentAccounts['jawwal_pay_number'] }}" 
                                       placeholder="0599000000" 
                                       class="text-sm font-mono w-full !h-10" 
                                       dir="ltr">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">اسم صاحب المحفظة (المستفيد)</label>
                                <input type="text" 
                                       name="settings[jawwal_pay_name]" 
                                       value="{{ $paymentAccounts['jawwal_pay_name'] }}" 
                                       placeholder="محفظة سفرة غزة" 
                                       class="text-sm w-full !h-10">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">صورة باركود QR المحفظة</label>
                                <input type="file" 
                                       name="jawwal_pay_qr" 
                                       accept="image/*" 
                                       class="text-xs w-full p-2 border border-dashed border-slate-300 rounded-lg bg-slate-50/50 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">امسح الكود عبر التطبيق أو ارفع صورة جديدة للباركود.</p>
                            </div>
                        </div>

                        @if($paymentAccounts['jawwal_pay_qr_url'])
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3 bg-slate-50 p-2.5 rounded-xl">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img src="{{ $paymentAccounts['jawwal_pay_qr_url'] }}" alt="QR جوال باي" class="w-12 h-12 object-contain rounded-lg border border-slate-200 bg-white p-0.5 shrink-0">
                                    <div class="text-xs truncate">
                                        <a href="{{ $paymentAccounts['jawwal_pay_qr_url'] }}" target="_blank" class="font-bold text-stone-900 hover:text-primary flex items-center gap-1">
                                            <span>معاينة الرمز</span>
                                            <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                                        </a>
                                        <span class="text-[10px] text-slate-400">مفعل حالياً</span>
                                    </div>
                                </div>
                                <label class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 hover:text-rose-700 cursor-pointer shrink-0">
                                    <input type="checkbox" name="remove_jawwal_pay_qr" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                    <span>حذف</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    {{-- 2. PalPay --}}
                    <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs space-y-4 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-[20px]">qr_code_scanner</span>
                                    <h3 class="font-bold text-sm text-stone-900">محفظة بال باي (PalPay)</h3>
                                </div>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-stone-100 text-stone-800">محفظتي</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">رقم المحفظة / نقطة البيع</label>
                                <input type="text" 
                                       name="settings[palpay_number]" 
                                       value="{{ $paymentAccounts['palpay_number'] }}" 
                                       placeholder="0599000000" 
                                       class="text-sm font-mono w-full !h-10" 
                                       dir="ltr">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">اسم صاحب المحفظة (المستفيد)</label>
                                <input type="text" 
                                       name="settings[palpay_name]" 
                                       value="{{ $paymentAccounts['palpay_name'] }}" 
                                       placeholder="محفظة بال باي — سفرة غزة" 
                                       class="text-sm w-full !h-10">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">صورة باركود QR بال باي</label>
                                <input type="file" 
                                       name="palpay_qr" 
                                       accept="image/*" 
                                       class="text-xs w-full p-2 border border-dashed border-slate-300 rounded-lg bg-slate-50/50 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">يُمسح عبر تطبيق محفظتي لدفع المبالغ فوراً.</p>
                            </div>
                        </div>

                        @if($paymentAccounts['palpay_qr_url'])
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3 bg-slate-50 p-2.5 rounded-xl">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img src="{{ $paymentAccounts['palpay_qr_url'] }}" alt="QR بال باي" class="w-12 h-12 object-contain rounded-lg border border-slate-200 bg-white p-0.5 shrink-0">
                                    <div class="text-xs truncate">
                                        <a href="{{ $paymentAccounts['palpay_qr_url'] }}" target="_blank" class="font-bold text-stone-900 hover:text-primary flex items-center gap-1">
                                            <span>معاينة الرمز</span>
                                            <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                                        </a>
                                        <span class="text-[10px] text-slate-400">مفعل حالياً</span>
                                    </div>
                                </div>
                                <label class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 hover:text-rose-700 cursor-pointer shrink-0">
                                    <input type="checkbox" name="remove_palpay_qr" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                    <span>حذف</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    {{-- 3. Bank of Palestine --}}
                    <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs space-y-4 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-[20px]">account_balance</span>
                                    <h3 class="font-bold text-sm text-stone-900">حساب بنك فلسطين</h3>
                                </div>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-stone-100 text-stone-800">حساب بنكي</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">اسم البنك</label>
                                <input type="text" 
                                       name="settings[bank_name]" 
                                       value="{{ $paymentAccounts['bank_name'] }}" 
                                       placeholder="بنك فلسطين" 
                                       class="text-sm w-full !h-10">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">رقم الحساب البنكي</label>
                                <input type="text" 
                                       name="settings[bank_account_number]" 
                                       value="{{ $paymentAccounts['bank_account_number'] }}" 
                                       placeholder="2345678" 
                                       class="text-sm font-mono w-full !h-10" 
                                       dir="ltr">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">رقم الآيبان (IBAN)</label>
                                <input type="text" 
                                       name="settings[bank_iban]" 
                                       value="{{ $paymentAccounts['bank_iban'] }}" 
                                       placeholder="PS04PALS000000000002345678" 
                                       class="text-sm font-mono w-full !h-10" 
                                       dir="ltr">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-800 mb-1">اسم المستفيد البنكي</label>
                                <input type="text" 
                                       name="settings[bank_beneficiary_name]" 
                                       value="{{ $paymentAccounts['bank_beneficiary_name'] }}" 
                                       placeholder="سفرة غزة — Sofra Gaza" 
                                       class="text-sm w-full !h-10">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Instructions Note --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <label class="block text-xs font-bold text-stone-900 mb-1 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[18px]">info</span>
                        <span>ملاحظات وتعليمات التحويل الموجهة للزبائن</span>
                    </label>
                    <input type="text" 
                           name="settings[payment_instructions_note]" 
                           value="{{ $paymentAccounts['instructions_note'] ?? '' }}" 
                           class="text-sm w-full !h-11 bg-white" 
                           placeholder="مثال: يرجى كتابة رقم الهاتف في ملاحظات التحويل، ورفع صورة الإشعار للمراجعة الفورية...">
                    <p class="text-[11px] text-slate-500 mt-1">تظهر هذه الملاحظة التوجيهية للزبائن في كافة شاشات الدفع والتحويل وشحن الرصيد بالمتجر.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TAB 2: رسوم ومناطق التوصيل (Delivery Fees & Areas)          --}}
    {{-- ========================================================= --}}
    <div id="section-delivery" class="settings-section space-y-6 hidden">
        <div class="admin-card !p-0 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-stone-900 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <span class="material-symbols-outlined text-primary text-[24px]">local_shipping</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-stone-900">إعدادات رسوم التوصيل</h2>
                        <p class="text-xs text-slate-500">حدد تكلفة التوصيل العامة وسعر كل منطقة محددة في قطاع غزة</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-stone-800">
                    <span>المناطق المعتمدة:</span>
                    <strong class="font-mono text-stone-900">{{ count($areasWithFees) }}</strong>
                </span>
            </div>

            <div class="p-6 space-y-6">
                {{-- Fallback Default Delivery Fee --}}
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-stone-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[18px]">tune</span>
                            <span>رسوم التوصيل العامة الافتراضية (الأساسية)</span>
                        </h3>
                        <p class="text-xs text-slate-500">
                            تُعتمد هذه الرسوم كخيار أساسي وافتراضي في حال لم يتم تحديد سعر خاص للمنطقة أدناه.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <input type="number" 
                               step="0.5" 
                               min="0" 
                               max="999" 
                               name="settings[delivery_fee]" 
                               value="{{ $deliveryFeeSetting?->value ?? 10 }}" 
                               class="text-base font-bold font-mono text-center w-24 !h-11 bg-white rounded-xl shadow-2xs">
                        <span class="text-sm font-bold text-stone-700">₪ شيكل</span>
                    </div>
                </div>

                {{-- Area by Area Fees with Search --}}
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-stone-900">رسوم التوصيل المخصصة حسب كل منطقة</h3>
                            <p class="text-xs text-slate-500">يتم احتساب هذا السعر تلقائياً عند اختيار الزبون للمنطقة أثناء إتمام الطلب</p>
                        </div>
                        <div class="relative w-full sm:w-64">
                            <input type="text" 
                                   id="area-search-input"
                                   oninput="filterDeliveryAreas(this.value)"
                                   placeholder="تصفية المناطق..." 
                                   class="!min-h-[2.3rem] !py-1 !pr-8 !pl-3 !text-xs !rounded-full w-full">
                            <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 !text-base pointer-events-none">search</span>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4" id="delivery-areas-grid">
                        @foreach($areasWithFees as $area)
                            <div class="area-card flex items-center justify-between gap-3 p-3.5 rounded-xl bg-white border border-slate-200/80 hover:border-primary/40 transition-colors shadow-2xs"
                                 data-name="{{ $area['label'] }} {{ $area['key'] }}">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-primary text-[17px]">location_on</span>
                                        <span class="font-bold text-xs text-stone-900 truncate">{{ $area['label'] }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mr-5 mt-0.5">
                                        الرمز: <code class="font-mono text-stone-700 font-bold">{{ $area['key'] }}</code>
                                        @if(! empty($area['is_custom_area']))
                                            <span class="mr-1 inline-block px-1.5 py-0.2 rounded bg-stone-100 text-stone-800 text-[9px] font-bold">مخصصة</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <input type="number" 
                                           step="0.5" 
                                           min="0" 
                                           max="999" 
                                           name="delivery_fees_by_area[{{ $area['key'] }}]" 
                                           value="{{ $area['delivery_fee'] }}" 
                                           class="font-mono font-bold text-xs text-center w-16 !h-9 bg-slate-50 rounded-lg"
                                           placeholder="{{ $deliveryFeeSetting?->value ?? 10 }}">
                                    <span class="text-xs font-bold text-slate-500">₪</span>
                                    @if(! empty($area['is_custom_area']))
                                        <button type="submit" 
                                                name="remove_area_key" 
                                                value="{{ $area['key'] }}"
                                                class="admin-action-btn admin-action-btn--danger admin-action-btn--icon admin-action-btn--sm"
                                                title="حذف هذه المنطقة المخصصة"
                                                onclick="return confirm('هل تريد حذف هذه المنطقة المخصصة نهائياً؟');">
                                            <span class="material-symbols-outlined text-[15px]">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Add New Custom Area Card --}}
                <div class="p-5 rounded-2xl bg-white border border-dashed border-slate-300 space-y-3">
                    <div class="flex items-center gap-2 text-stone-900 font-bold text-sm">
                        <span class="material-symbols-outlined text-primary text-[20px]">add_location_alt</span>
                        <span>إضافة منطقة توصيل جديدة للمنصة</span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-stone-800 mb-1">اسم المنطقة مع المدينة</label>
                            <input type="text" name="new_area_label" placeholder="مثلاً: غزة • الشيخ رضوان" class="text-xs w-full !h-10">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-800 mb-1">الاسم المختصر (الرمز الفريد)</label>
                            <input type="text" name="new_area_key" placeholder="الشيخ رضوان" class="text-xs font-mono w-full !h-10">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-800 mb-1">رسوم التوصيل (شيكل)</label>
                            <input type="number" step="0.5" min="0" max="999" name="new_area_fee" placeholder="10" class="text-xs font-mono w-full !h-10">
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400">ستُضاف المنطقة وتظهر في كافة أنحاء المتجر والتطبيق عند الضغط على "حفظ كافة الإعدادات".</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TAB 3: برنامج نقاط الولاء والمكافآت (Points & Rewards)       --}}
    {{-- ========================================================= --}}
    <div id="section-points" class="settings-section space-y-6 hidden">
        <div class="admin-card !p-0 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-stone-900 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <span class="material-symbols-outlined text-primary text-[24px]">stars</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-stone-900">نظام احتساب واستبدال النقاط (برنامج الولاء)</h2>
                        <p class="text-xs text-slate-500">التحكم في معدل اكتساب النقاط لكل طلب ومعدل استبدالها بوجبات ومكافآت مجانية</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-stone-800">
                    نقاط الولاء العامة
                </span>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    {{-- 1. Earn Rate --}}
                    <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[20px]">add_circle</span>
                                <h3 class="font-bold text-sm text-stone-900">معدل اكتساب النقاط (عند الشراء)</h3>
                            </div>
                            <span class="text-xs font-mono font-bold text-slate-500">شيكل / نقطة</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-800 mb-1">المبلغ المنفق لكسب نقطة واحدة</label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0.01" 
                                   id="global_points_earn" 
                                   name="settings[points_per_amount]" 
                                   value="{{ $pointsEarnVal }}" 
                                   class="text-base font-bold font-mono w-full !h-11">
                        </div>

                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                            <span class="text-[11px] text-slate-400 font-bold">خيارات سريعة:</span>
                            <button type="button" onclick="setGlobalEarnRate(10)" class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-800 text-[11px] font-bold transition-colors cursor-pointer">كل 10 ₪ = 1 نقطة</button>
                            <button type="button" onclick="setGlobalEarnRate(5)" class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-800 text-[11px] font-bold transition-colors cursor-pointer">كل 5 ₪ = 1 نقطة</button>
                            <button type="button" onclick="setGlobalEarnRate(1)" class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-800 text-[11px] font-bold transition-colors cursor-pointer">كل 1 ₪ = 1 نقطة</button>
                        </div>

                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/70 text-xs text-slate-600 leading-relaxed">
                            <strong class="text-stone-900 block mb-1 font-bold">💡 مثال توضيحي:</strong>
                            إذا وضعت <span class="font-mono font-bold text-stone-900">10</span>: كل 10 شواكل مشتريات تمنح الزبون نقطة واحدة (طلب بـ 100 ₪ = 10 نقاط).
                        </div>
                    </div>

                    {{-- 2. Redeem Rate --}}
                    <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[20px]">redeem</span>
                                <h3 class="font-bold text-sm text-stone-900">معدل استبدال النقاط (بوجبات مجانية)</h3>
                            </div>
                            <span class="text-xs font-mono font-bold text-slate-500">شيكل / نقطة</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-800 mb-1">قيمة النقطة الواحدة بالشيكل عند الخصم</label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0.01" 
                                   id="global_points_redeem" 
                                   name="settings[points_redeem_per_amount]" 
                                   value="{{ $pointsRedeemVal }}" 
                                   class="text-base font-bold font-mono w-full !h-11">
                        </div>

                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                            <span class="text-[11px] text-slate-400 font-bold">خيارات سريعة:</span>
                            <button type="button" onclick="setGlobalRedeemRate(0.10)" class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-800 text-[11px] font-bold transition-colors cursor-pointer">كل 1 ₪ = 10 نقاط</button>
                            <button type="button" onclick="setGlobalRedeemRate(0.20)" class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-800 text-[11px] font-bold transition-colors cursor-pointer">كل 1 ₪ = 5 نقاط</button>
                            <button type="button" onclick="setGlobalRedeemRate(1)" class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-800 text-[11px] font-bold transition-colors cursor-pointer">كل 1 ₪ = 1 نقطة</button>
                        </div>

                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/70 text-xs text-slate-600 leading-relaxed">
                            <strong class="text-stone-900 block mb-1 font-bold">💡 مثال توضيحي:</strong>
                            إذا وضعت <span class="font-mono font-bold text-stone-900">0.10</span>: كل 1 ₪ يحتاج 10 نقاط (وجبة سعرها 30 ₪ تتطلب 300 نقطة).
                        </div>
                    </div>
                </div>

                {{-- Delivery Fee in Points & Default Rewards --}}
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                        <label class="block text-xs font-bold text-stone-900 mb-1">احتساب النقاط على رسوم التوصيل؟</label>
                        <select name="settings[points_include_delivery]" class="text-xs font-bold w-full rounded-lg !h-10 bg-white">
                            <option value="1" @selected($pointsDeliveryVal === '1')>نعم (شامل قيمة الطعام + التوصيل)</option>
                            <option value="0" @selected($pointsDeliveryVal === '0')>لا (قيمة الطعام والوجبات فقط)</option>
                        </select>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                        <label class="block text-xs font-bold text-stone-900 mb-1">نقاط استبدال مشروب مجاني افتراضي</label>
                        <input type="number" name="settings[drink_points]" value="{{ $drinkPointsVal }}" class="text-sm font-bold font-mono w-full !h-10 bg-white">
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                        <label class="block text-xs font-bold text-stone-900 mb-1">نقاط استبدال وجبة مجانية افتراضية</label>
                        <input type="number" name="settings[meal_points]" value="{{ $mealPointsVal }}" class="text-sm font-bold font-mono w-full !h-10 bg-white">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-100">
                        <label class="block text-xs font-bold text-stone-900 mb-1">نقاط الداعي عند انضمام صديق (برنامج دائم)</label>
                        <input type="number" min="0" name="settings[referral_inviter_points]" value="{{ $referralInviterVal }}" class="text-sm font-bold font-mono w-full !h-10 bg-white">
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-100">
                        <label class="block text-xs font-bold text-stone-900 mb-1">نقاط الصديق الجديد عند إدخال كود الدعوة</label>
                        <input type="number" min="0" name="settings[referral_invitee_points]" value="{{ $referralInviteeVal }}" class="text-sm font-bold font-mono w-full !h-10 bg-white">
                    </div>
                </div>

                {{-- Live Interactive Preview Calculator --}}
                <div class="p-4 rounded-xl bg-stone-900 text-white space-y-3">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <span class="material-symbols-outlined text-primary text-[20px]">calculate</span>
                        <span>معاينة حية ومباشرة للأرقام المدخلة أعلاه:</span>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-stone-800/80 rounded-xl border border-stone-700">
                            <span class="text-slate-400 block text-[11px]">طلب بقيمة 100 ₪ من أي مطعم:</span>
                            <div class="font-bold text-white mt-1 text-sm" id="global_preview_earn">
                                يكسب الزبون: <span class="font-mono text-primary text-base">100</span> نقطة
                            </div>
                        </div>
                        <div class="p-3 bg-stone-800/80 rounded-xl border border-stone-700">
                            <span class="text-slate-400 block text-[11px]">وجبة من المنيو سعرها 30 ₪:</span>
                            <div class="font-bold text-white mt-1 text-sm" id="global_preview_redeem">
                                تتطلب للاستبدال: <span class="font-mono text-primary text-base">30</span> نقطة
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TAB 4: البانرات والإعلانات (Banners & Ads)                 --}}
    {{-- ========================================================= --}}
    <div id="section-banners" class="settings-section space-y-6 hidden">
        <div class="admin-card !p-0 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-stone-900 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <span class="material-symbols-outlined text-primary text-[24px]">view_carousel</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-stone-900">إدارة البانرات الإعلانية (الشريط العلوي في شاشة الهاتف)</h2>
                        <p class="text-xs text-slate-500">تظهر هذه الصور كشريط إعلاني مدمج بدون نصوص أسفل الهيدر مباشرة في شاشات الجوال</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-stone-800">
                    <span>البانرات الحالية:</span>
                    <strong class="font-mono text-stone-900">{{ count($homeBanners) }}</strong>
                </span>
            </div>

            <div class="p-6 space-y-6">
                {{-- Current Active Banners Gallery --}}
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-stone-900">البانرات المفعلة حالياً</h3>

                    @if(count($homeBanners) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($homeBanners as $bIdx => $bannerItem)
                                @php
                                    $bPath = is_array($bannerItem) ? ($bannerItem['image'] ?? $bannerItem['url'] ?? '') : (string) $bannerItem;
                                    $bUrl = (str_starts_with($bPath, 'http://') || str_starts_with($bPath, 'https://'))
                                        ? $bPath
                                        : \Illuminate\Support\Facades\Storage::disk('public')->url($bPath);
                                @endphp
                                <div class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-2xs flex flex-col group">
                                    <div class="w-full h-24 bg-slate-100 overflow-hidden relative">
                                        <img src="{{ $bUrl }}" alt="بانر {{ $bIdx + 1 }}" class="w-full h-full object-cover">
                                        <span class="absolute top-2 right-2 bg-stone-900/80 text-white text-[10px] font-mono font-bold px-2 py-0.5 rounded-md">
                                            #{{ $bIdx + 1 }}
                                        </span>
                                    </div>
                                    <div class="p-3 bg-white flex items-center justify-between border-t border-slate-100">
                                        <a href="{{ $bUrl }}" target="_blank" class="text-xs font-semibold text-stone-600 hover:text-primary flex items-center gap-1 truncate max-w-[150px]">
                                            <span>معاينة الرابط</span>
                                            <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                        </a>
                                        <button type="submit" 
                                                name="remove_banner" 
                                                value="{{ $bPath }}" 
                                                class="admin-action-btn admin-action-btn--danger admin-action-btn--icon admin-action-btn--sm"
                                                title="حذف هذا البانر نهائياً">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center rounded-2xl border border-dashed border-slate-300 bg-slate-50/50">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-1">imagesmode</span>
                            <p class="text-xs font-bold text-stone-700">لا توجد صور بانرات خاصة مرفوعة حالياً</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">يتم عرض البانرات الافتراضية الأنيقة تلقائياً في شاشات الجوال.</p>
                        </div>
                    @endif
                </div>

                {{-- Add New Banners --}}
                <div class="grid gap-4 md:grid-cols-2 pt-4 border-t border-slate-100">
                    {{-- File upload --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <label class="block text-xs font-bold text-stone-900 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-primary text-[18px]">upload_file</span>
                            <span>رفع صور بانرات جديدة (يمكنك تحديد عدة صور)</span>
                        </label>
                        <input type="file" 
                               name="new_banners[]" 
                               multiple 
                               accept="image/*" 
                               class="text-xs w-full p-2.5 border border-dashed border-slate-300 rounded-lg bg-white cursor-pointer">
                        <p class="text-[11px] text-slate-500">
                            💡 المقاس الموصى به: نسبة شريطية عرضية (مثلاً 1200×300 أو 800×200 بكسل).
                        </p>
                    </div>

                    {{-- URL input --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <label class="block text-xs font-bold text-stone-900 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-primary text-[18px]">link</span>
                            <span>أو إضافة رابط مباشر لصورة إعلان</span>
                        </label>
                        <input type="url" 
                               name="new_banner_url" 
                               placeholder="https://example.com/banner.jpg" 
                               class="text-xs font-mono w-full !h-10 bg-white" 
                               dir="ltr">
                        <p class="text-[11px] text-slate-500">
                            يمكنك إدراج رابط صورة خارجي مباشر لإضافتها فوراً لسلايدر البانرات.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TAB 5: النظام والمدد الزمنية والتنبيهات (System & Durations) --}}
    {{-- ========================================================= --}}
    <div id="section-system" class="settings-section space-y-6 hidden">
        <div class="admin-card !p-0 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-stone-900 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <span class="material-symbols-outlined text-primary text-[24px]">tune</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-stone-900">إعدادات النظام والمدد الزمنية والتنبيهات</h2>
                        <p class="text-xs text-slate-500">التحكم في فترات الصلاحيات والاشتراكات وتنبيهات التجديد التلقائية</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-stone-800">
                    سياسات النظام
                </span>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid gap-5 sm:grid-cols-2">
                    {{-- Membership expiry warning --}}
                    <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-2xs space-y-2">
                        <label class="block text-xs font-bold text-stone-900 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-primary text-[18px]">card_membership</span>
                            <span>تنبيه انتهاء اشتراك العضوية</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" 
                                   min="1" 
                                   max="60" 
                                   name="settings[membership_expiry_warning_days]" 
                                   value="{{ $membershipExpiryWarningDaysVal }}" 
                                   class="text-sm font-bold font-mono w-full !h-10">
                            <span class="text-xs font-bold text-slate-500">يوم</span>
                        </div>
                        <p class="text-[11px] text-slate-400">عدد الأيام قبل انتهاء اشتراك بطاقة الزبون لتنبيهه بتجديد العضوية.</p>
                    </div>
                </div>

                {{-- Other dynamic settings if any --}}
                @if($otherSettings->isNotEmpty())
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <h3 class="text-xs font-bold text-stone-900">إعدادات إضافية مسجلة بالنظام</h3>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($otherSettings as $setting)
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                                    <label class="block text-xs font-bold text-stone-900">{{ $setting->label ?: $setting->key }}</label>
                                    <input name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="text-xs font-bold w-full bg-white !h-9">
                                    <span class="text-[10px] font-mono text-slate-400 block">{{ $setting->key }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- Sticky Floating Save Bar                                  --}}
    {{-- ========================================================= --}}
    <div class="fixed bottom-4 left-4 right-4 md:right-72 z-40">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-4 p-4 rounded-2xl bg-stone-900/95 text-white backdrop-blur-md border border-stone-800 shadow-2xl">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="material-symbols-outlined text-primary text-[22px] shrink-0">check_circle</span>
                <div class="text-xs leading-tight">
                    <div class="font-bold text-white">جاهز لحفظ التغييرات؟</div>
                    <div class="text-[11px] text-slate-400 truncate">تطبق كافة التعديلات فوراً على الموقع وتطبيق الهواتف.</div>
                </div>
            </div>

            <button type="submit" class="admin-btn admin-btn--primary !min-h-[2.6rem] !px-8 !text-xs flex items-center gap-2 font-bold shadow-md shrink-0">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>حفظ كافة الإعدادات</span>
            </button>
        </div>
    </div>
</form>

{{-- Scripts --}}
<script>
    // Tab switching logic with URL Hash support
    function switchSettingsTab(tabName) {
        // Hide all sections unless show all is active
        document.querySelectorAll('.settings-section').forEach(sec => {
            sec.classList.add('hidden');
        });

        // Remove active class from all buttons
        document.querySelectorAll('#settings-tab-list .app-tab-item:not(.app-tab-item--secondary)').forEach(btn => {
            btn.classList.remove('is-active');
            btn.setAttribute('aria-selected', 'false');
        });

        // Show active section
        const targetSection = document.getElementById('section-' + tabName);
        const targetBtn = document.getElementById('tab-btn-' + tabName);
        if (targetSection) targetSection.classList.remove('hidden');
        if (targetBtn) {
            targetBtn.classList.add('is-active');
            targetBtn.setAttribute('aria-selected', 'true');
        }

        // Reset show all button text if it was active
        const showAllBtn = document.getElementById('btn-show-all');
        if (showAllBtn) {
            showAllBtn.classList.remove('is-active');
            showAllBtn.innerHTML = '<span class="material-symbols-outlined app-tab-icon">unfold_more</span><span>عرض الكل</span>';
        }

        // Update hash without jumping
        if (history.replaceState) {
            history.replaceState(null, null, '#' + tabName);
        }
    }

    function toggleShowAllSettings(btn) {
        const sections = document.querySelectorAll('.settings-section');
        const isShowingAll = !sections[0].classList.contains('hidden') && !sections[1].classList.contains('hidden');

        if (isShowingAll) {
            // Re-apply single tab
            switchSettingsTab('payments');
            btn.classList.remove('is-active');
            btn.innerHTML = '<span class="material-symbols-outlined app-tab-icon">unfold_more</span><span>عرض الكل</span>';
        } else {
            // Show all sections
            sections.forEach(sec => sec.classList.remove('hidden'));
            document.querySelectorAll('#settings-tab-list .app-tab-item:not(.app-tab-item--secondary)').forEach(b => {
                b.classList.add('is-active');
                b.setAttribute('aria-selected', 'true');
            });
            btn.classList.add('is-active');
            btn.innerHTML = '<span class="material-symbols-outlined app-tab-icon">unfold_less</span><span>تبويبات منفصلة</span>';
        }
    }

    // Filter delivery areas by name
    function filterDeliveryAreas(searchTerm) {
        const term = searchTerm.trim().toLowerCase();
        const cards = document.querySelectorAll('#delivery-areas-grid .area-card');
        cards.forEach(card => {
            const dataName = (card.getAttribute('data-name') || '').toLowerCase();
            if (!term || dataName.includes(term)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Interactive Points Preview Calculator
    (function() {
        const earnInput = document.getElementById('global_points_earn');
        const redeemInput = document.getElementById('global_points_redeem');
        const earnText = document.getElementById('global_preview_earn');
        const redeemText = document.getElementById('global_preview_redeem');

        function updateGlobalPreviews() {
            const earnRate = parseFloat(earnInput ? earnInput.value : '') || 1;
            const redeemRate = parseFloat(redeemInput ? redeemInput.value : '') || 1;

            const sampleOrder = 100;
            const earnedPoints = Math.floor(sampleOrder / earnRate);
            if (earnText) {
                earnText.innerHTML = `يكسب الزبون: <span class="font-mono text-primary font-black text-base">${earnedPoints}</span> نقطة (كل ${earnRate} ₪ = 1 نقطة)`;
            }

            const sampleItemPrice = 30;
            const redeemPoints = Math.round(sampleItemPrice / redeemRate);
            const ptsPerShekel = (1 / redeemRate).toFixed(1).replace(/\.0$/, '');
            if (redeemText) {
                redeemText.innerHTML = `تتطلب للاستبدال: <span class="font-mono text-primary font-black text-base">${redeemPoints}</span> نقطة (كل 1 ₪ = ${ptsPerShekel} نقطة)`;
            }
        }

        window.setGlobalEarnRate = function(val) {
            if (earnInput) {
                earnInput.value = val;
                updateGlobalPreviews();
            }
        };

        window.setGlobalRedeemRate = function(val) {
            if (redeemInput) {
                redeemInput.value = val;
                updateGlobalPreviews();
            }
        };

        if (earnInput && redeemInput) {
            earnInput.addEventListener('input', updateGlobalPreviews);
            redeemInput.addEventListener('input', updateGlobalPreviews);
            updateGlobalPreviews();
        }

        // Check URL hash on page load
        const hash = window.location.hash.replace('#', '');
        const validTabs = ['payments', 'delivery', 'points', 'banners', 'system'];
        if (validTabs.includes(hash)) {
            switchSettingsTab(hash);
        }
    })();
</script>
@endsection
