@extends('layouts.admin')

@section('kicker', 'النظام')
@section('title', 'إعدادات المنصة')

@section('content')
@php
    $hints = [
        'points_per_amount' => 'سعر اكتساب النقاط لكل المطاعم. يمكن تخصيص مطعم من صفحة المطعم، أو صنف من المنيو.',
        'points_redeem_per_amount' => 'سعر استبدال النقاط العام. يُتجاوز بسعر المطعم أو رقم ثابت للصنف.',
        'points_include_delivery' => '1 = طلب بـ 40₪ (طعام + توصيل) يكسب 40 نقطة عندما يكون السعر 1. 0 = الطعام فقط.',
        'restaurant_expiry_warning_days' => 'عدد الأيام قبل انتهاء عرض المطعم لإرسال إشعار تنبيه للمشرف وصاحب المطعم.',
        'membership_expiry_warning_days' => 'عدد الأيام قبل انتهاء اشتراك عضوية الزبون لإرسال إشعار تجديد.',
        'restaurant_listing_days' => 'المدة الافتراضية لعرض المطعم على المنصة بالأيام بعد موافقة الإدارة على اشتراكه.',
        'drink_points' => 'عدد النقاط الافتراضي المطلوب لاستبدال مشروب مجاني.',
        'meal_points' => 'عدد النقاط الافتراضي المطلوب لاستبدال وجبة مجانية.',
    ];
@endphp

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="w-full max-w-none space-y-6">
    @csrf

    {{-- Banner Ads Management Section --}}
    <div id="setting-home-banners" class="rounded-2xl border border-slate-200/80 bg-white/95 p-5 sm:p-6 space-y-5 shadow-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">view_carousel</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-on-surface">إدارة البانرات الإعلانية (الشريط العلوي في شاشة الهاتف)</h2>
                    <p class="text-xs text-on-surface-variant">تظهر هذه الصور كشريط إعلاني مدمج بدون أي نصوص أسفل الهيدر مباشرة. يمكنك إضافة عدد غير محدود من الصور وحذفها في أي وقت.</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-100 text-amber-800 shrink-0">
                إعلانات الهاتف
            </span>
        </div>

        {{-- Current Banners Gallery --}}
        @php
            $rawBanners = \App\Models\Setting::rawHomeBanners();
        @endphp
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-stone-700">البانرات الحالية المفعلة ({{ count($rawBanners) }})</h3>
                <span class="text-[11px] text-stone-400">انقر على "حذف" لأي صورة لإزالتها فوراً</span>
            </div>

            @if(count($rawBanners) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5">
                    @foreach($rawBanners as $bIdx => $bannerItem)
                        @php
                            $bPath = is_array($bannerItem) ? ($bannerItem['image'] ?? $bannerItem['url'] ?? '') : (string) $bannerItem;
                            $bUrl = (str_starts_with($bPath, 'http://') || str_starts_with($bPath, 'https://'))
                                ? $bPath
                                : \Illuminate\Support\Facades\Storage::disk('public')->url($bPath);
                        @endphp
                        <div class="relative group rounded-xl border border-stone-200 overflow-hidden bg-stone-50 shadow-2xs flex flex-col">
                            <div class="w-full h-20 bg-stone-100 overflow-hidden relative">
                                <img src="{{ $bUrl }}" alt="بانر {{ $bIdx + 1 }}" class="w-full h-full object-cover">
                                <span class="absolute top-1.5 right-1.5 bg-black/60 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                                    #{{ $bIdx + 1 }}
                                </span>
                            </div>
                            <div class="p-2 bg-white flex items-center justify-between border-t border-stone-100">
                                <a href="{{ $bUrl }}" target="_blank" class="text-[11px] text-stone-500 hover:text-primary truncate max-w-[100px]">
                                    معاينة
                                </a>
                                <button type="submit" name="remove_banner" value="{{ $bPath }}" class="text-[11px] text-error font-bold hover:underline flex items-center gap-0.5 cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">delete</span>
                                    <span>حذف</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-stone-300 p-6 text-center bg-stone-50/50">
                    <span class="material-symbols-outlined text-3xl text-stone-300 mb-1">imagesmode</span>
                    <p class="text-xs text-stone-500 font-medium">لا توجد صور بانرات خاصة مرفوعة حالياً (يتم عرض البانرات الافتراضية الأنيقة تلقائياً).</p>
                    <p class="text-[11px] text-stone-400 mt-0.5">ارفع أول صورة إعلانية بالأسفل للبدء بعرض إعلاناتك الخاصة.</p>
                </div>
            @endif
        </div>

        {{-- Add New Banners --}}
        <div class="grid gap-4 md:grid-cols-2 pt-2 border-t border-slate-100">
            {{-- Upload multiple files --}}
            <div class="rounded-xl bg-stone-50/70 border border-stone-200/80 p-3.5 space-y-2">
                <label class="block text-xs font-bold text-stone-800">
                    <span class="material-symbols-outlined text-[16px] align-middle text-primary">upload_file</span>
                    رفع صور إعلانات جديدة (يمكنك تحديد عدة صور معاً)
                </label>
                <input type="file" name="new_banners[]" multiple accept="image/*" class="text-xs w-full p-2 border border-dashed border-stone-300 rounded-lg bg-white cursor-pointer">
                <p class="text-[11px] text-stone-500 leading-relaxed">
                    💡 يُفضل أن تكون الصورة بنسبة عرضية شريطية (مثلاً: 1200×300 أو 800×200 بكسل) لتظهر كإعلان بانر مدمج بدون نصوص.
                </p>
            </div>

            {{-- Or paste image URL --}}
            <div class="rounded-xl bg-stone-50/70 border border-stone-200/80 p-3.5 space-y-2">
                <label class="block text-xs font-bold text-stone-800">
                    <span class="material-symbols-outlined text-[16px] align-middle text-primary">link</span>
                    أو إضافة رابط صورة إعلان مباشر
                </label>
                <input type="url" name="new_banner_url" placeholder="https://example.com/banner.jpg" class="text-xs font-mono w-full p-2 border border-stone-300 rounded-lg bg-white" dir="ltr">
                <p class="text-[11px] text-stone-500 leading-relaxed">
                    يمكنك كتابة رابط مباشر لأي صورة على الويب لإضافتها فوراً لسلايدر البانرات.
                </p>
            </div>
        </div>
    </div>

    {{-- 1. Payment Accounts & Direct Transfer Section --}}
    <div id="setting-payment-accounts" class="rounded-2xl border border-slate-200/80 bg-white/95 p-5 sm:p-6 space-y-5 shadow-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">payments</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-on-surface">بيانات الدفع والتحويل المعتمدة (جوال باي، بال باي PalPay، بنك فلسطين)</h2>
                    <p class="text-xs text-on-surface-variant">تظهر هذه الحسابات والباركودات للزبائن في الدفع المباشر، شحن رصيد المحفظة، واشتراكات العضويات والمطاعم</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 shrink-0">
                حسابات التحويل المعتمدة
            </span>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            {{-- Jawwal Pay Wallet Settings --}}
            <div class="rounded-xl bg-surface-container-low border border-slate-200/70 p-4.5 space-y-3.5 flex flex-col justify-between">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                        <div class="flex items-center gap-2 text-stone-900 font-bold text-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>محفظة جوال باي (Jawwal Pay)</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">محفظة</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">رقم المحفظة / الجوال</label>
                        <input type="text" name="settings[jawwal_pay_number]" value="{{ $paymentAccounts['jawwal_pay_number'] }}" placeholder="0599000000" class="text-sm font-mono" dir="ltr">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">اسم صاحب المحفظة (المستفيد)</label>
                        <input type="text" name="settings[jawwal_pay_name]" value="{{ $paymentAccounts['jawwal_pay_name'] }}" placeholder="محفظة سفرة غزة" class="text-sm">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">صورة باركود / QR جوال باي</label>
                        <input type="file" name="jawwal_pay_qr" accept="image/*" class="text-xs w-full p-2 border border-dashed border-slate-300 rounded-lg bg-white">
                        <p class="mt-1 text-[11px] text-on-surface-variant">ارفع صورة كود QR ليتمكن الزبائن من مسحها والدفع فوراً.</p>
                    </div>
                </div>

                @if($paymentAccounts['jawwal_pay_qr_url'])
                    <div class="mt-2 pt-2 border-t border-slate-200/80 flex items-center gap-3 p-2 bg-white rounded-lg border border-slate-200">
                        <img src="{{ $paymentAccounts['jawwal_pay_qr_url'] }}" alt="باركود جوال باي" class="w-14 h-14 object-contain rounded border p-0.5 shrink-0">
                        <div class="text-xs space-y-1 min-w-0">
                            <a href="{{ $paymentAccounts['jawwal_pay_qr_url'] }}" target="_blank" class="font-bold text-primary hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                معاينة الباركود الحالي
                            </a>
                            <label class="flex items-center gap-1.5 text-error text-[11px] cursor-pointer">
                                <input type="checkbox" name="remove_jawwal_pay_qr" value="1">
                                <span>حذف صورة الباركود</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>

            {{-- PalPay Wallet Settings --}}
            <div class="rounded-xl bg-surface-container-low border border-slate-200/70 p-4.5 space-y-3.5 flex flex-col justify-between">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                        <div class="flex items-center gap-2 text-stone-900 font-bold text-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                            <span>محفظة بال باي (PalPay)</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">محفظتي</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">رقم المحفظة / نقطة البيع</label>
                        <input type="text" name="settings[palpay_number]" value="{{ $paymentAccounts['palpay_number'] }}" placeholder="0599000000" class="text-sm font-mono" dir="ltr">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">اسم صاحب المحفظة (المستفيد)</label>
                        <input type="text" name="settings[palpay_name]" value="{{ $paymentAccounts['palpay_name'] }}" placeholder="محفظة بال باي — سفرة غزة" class="text-sm">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">صورة باركود / QR بال باي</label>
                        <input type="file" name="palpay_qr" accept="image/*" class="text-xs w-full p-2 border border-dashed border-slate-300 rounded-lg bg-white">
                        <p class="mt-1 text-[11px] text-on-surface-variant">ارفع كود QR الخاص بمحفظة بال باي ليتمكن الزبائن من مسحه فوراً عبر تطبيق محفظتي.</p>
                    </div>
                </div>

                @if($paymentAccounts['palpay_qr_url'])
                    <div class="mt-2 pt-2 border-t border-slate-200/80 flex items-center gap-3 p-2 bg-white rounded-lg border border-slate-200">
                        <img src="{{ $paymentAccounts['palpay_qr_url'] }}" alt="باركود بال باي" class="w-14 h-14 object-contain rounded border p-0.5 shrink-0">
                        <div class="text-xs space-y-1 min-w-0">
                            <a href="{{ $paymentAccounts['palpay_qr_url'] }}" target="_blank" class="font-bold text-primary hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                معاينة الباركود الحالي
                            </a>
                            <label class="flex items-center gap-1.5 text-error text-[11px] cursor-pointer">
                                <input type="checkbox" name="remove_palpay_qr" value="1">
                                <span>حذف صورة الباركود</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Bank of Palestine Settings --}}
            <div class="rounded-xl bg-surface-container-low border border-slate-200/70 p-4.5 space-y-3.5 flex flex-col justify-between">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                        <div class="flex items-center gap-2 text-stone-900 font-bold text-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span>حساب بنك فلسطين</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">حساب بنكي</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">اسم البنك</label>
                        <input type="text" name="settings[bank_name]" value="{{ $paymentAccounts['bank_name'] }}" placeholder="بنك فلسطين" class="text-sm">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">رقم الحساب البنكي</label>
                        <input type="text" name="settings[bank_account_number]" value="{{ $paymentAccounts['bank_account_number'] }}" placeholder="2345678" class="text-sm font-mono" dir="ltr">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">رقم الآيبان (IBAN)</label>
                        <input type="text" name="settings[bank_iban]" value="{{ $paymentAccounts['bank_iban'] }}" placeholder="PS04PALS000000000002345678" class="text-sm font-mono" dir="ltr">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-on-surface">اسم المستفيد البنكي</label>
                        <input type="text" name="settings[bank_beneficiary_name]" value="{{ $paymentAccounts['bank_beneficiary_name'] }}" placeholder="سفرة غزة — Sofra Gaza" class="text-sm">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-xs font-bold text-on-surface">ملاحظات وتعليمات التحويل للزبائن</label>
            <input type="text" name="settings[payment_instructions_note]" value="{{ $paymentAccounts['instructions_note'] ?? '' }}" class="text-sm" placeholder="مثال: يرجى كتابة رقم الهاتف في ملاحظات التحويل...">
            <p class="mt-1 text-[11px] text-on-surface-variant">تظهر هذه الملاحظة التوجيهية للزبائن في كافة شاشات الدفع والتحويل وشحن الرصيد.</p>
        </div>
    </div>

    {{-- 2. Delivery Fees Section --}}
    @php
        $deliveryFeeSetting = $settings->firstWhere('key', 'delivery_fee');
    @endphp
    <div id="setting-delivery_fee" class="rounded-2xl border border-slate-200/80 bg-white/95 p-5 sm:p-6 space-y-5 shadow-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">local_shipping</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-on-surface">إعدادات رسوم التوصيل</h2>
                    <p class="text-xs text-on-surface-variant">حدد تكلفة التوصيل العامة وسعر كل منطقة محددة في قطاع غزة</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-surface-container-high text-on-surface-variant shrink-0">
                {{ count($areasWithFees) }} مناطق معتمدة
            </span>
        </div>

        {{-- Default fallback delivery fee --}}
        <div>
            <label class="mb-1 block text-sm font-bold text-on-surface">رسوم التوصيل العامة الافتراضية (شيكل)</label>
            <div class="flex items-center gap-2 max-w-xs">
                <input type="number" step="0.5" min="0" max="999" name="settings[delivery_fee]" value="{{ $deliveryFeeSetting?->value ?? 10 }}" class="text-sm font-bold font-mono">
                <span class="text-sm font-bold text-stone-500">₪</span>
            </div>
            <p class="mt-1 text-xs leading-6 text-on-surface-variant">
                تُعتمد هذه الرسوم كخيار أساسي وافتراضي في حال لم يتم تحديد سعر خاص للمنطقة أدناه.
            </p>
        </div>

        {{-- Area by Area Delivery Fees --}}
        <div class="space-y-3 pt-2 border-t border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-bold text-on-surface">
                        رسوم التوصيل المخصصة حسب كل منطقة (شيكل)
                    </label>
                    <p class="text-xs text-on-surface-variant">
                        يتم احتساب هذا السعر تلقائياً عند اختيار الزبون للمنطقة في المتجر أو أثناء إتمام الطلب:
                    </p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                @foreach($areasWithFees as $area)
                    <div class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-surface-container-low border border-slate-200/70 hover:border-slate-300 transition-colors">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                                <span class="font-bold text-sm text-on-surface truncate">{{ $area['label'] }}</span>
                            </div>
                            <div class="text-[11px] text-on-surface-variant mr-6">
                                الرمز: <code class="text-primary font-mono">{{ $area['key'] }}</code>
                                @if(! empty($area['is_custom_area']))
                                    <span class="mr-1 inline-block px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">مخصصة</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <input type="number" step="0.5" min="0" max="999" 
                                   name="delivery_fees_by_area[{{ $area['key'] }}]" 
                                   value="{{ $area['delivery_fee'] }}" 
                                   style="width: 5.5rem; text-align: center; font-weight: bold; padding: 0.5rem;"
                                   class="font-mono text-sm"
                                   placeholder="{{ $deliveryFeeSetting?->value ?? 10 }}">
                            <span class="text-xs font-bold text-on-surface-variant">₪</span>
                            @if(! empty($area['is_custom_area']))
                                <button type="submit" name="remove_area_key" value="{{ $area['key'] }}"
                                        class="text-error hover:opacity-75 p-1"
                                        title="حذف هذه المنطقة المخصصة"
                                        onclick="return confirm('هل تريد حذف هذه المنطقة المخصصة؟');">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Add new area form --}}
        <details class="rounded-xl bg-surface-container-low border border-dashed border-slate-300 p-4 mt-3">
            <summary class="cursor-pointer text-xs font-bold text-primary flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                إضافة منطقة توصيل جديدة للمنصة
            </summary>
            <div class="mt-3 grid gap-3 sm:grid-cols-3 pt-3 border-t border-slate-200">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">اسم المنطقة مع المدينة</label>
                    <input type="text" name="new_area_label" placeholder="غزة • الشيخ رضوان" class="text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">اسم المنطقة المختصر (الرمز)</label>
                    <input type="text" name="new_area_key" placeholder="الشيخ رضوان" class="text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">رسوم التوصيل (شيكل)</label>
                    <input type="number" step="0.5" min="0" max="999" name="new_area_fee" placeholder="10" class="text-xs font-mono">
                </div>
            </div>
            <p class="text-[11px] text-on-surface-variant mt-2">عند النقر على "حفظ كافة الإعدادات"، ستُضاف المنطقة وتظهر في كافة أنحاء المتجر والتطبيق.</p>
        </details>
    </div>

    {{-- 3. Dedicated Points & Loyalty Rewards Section --}}
    @php
        $pointsEarnVal = $settings->firstWhere('key', 'points_per_amount')?->value ?? '1';
        $pointsRedeemVal = $settings->firstWhere('key', 'points_redeem_per_amount')?->value ?? '1';
        $pointsDeliveryVal = (string) ($settings->firstWhere('key', 'points_include_delivery')?->value ?? '1');
    @endphp
    <div id="setting-points-system" class="rounded-2xl border border-amber-200/80 bg-white/95 p-5 sm:p-6 space-y-5 shadow-xs">
        <div class="flex items-center justify-between border-b border-amber-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px] fill-1">stars</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-on-surface">نظام احتساب واستبدال النقاط (العام للمنصة)</h2>
                    <p class="text-xs text-on-surface-variant">التحكم في معدل اكتساب النقاط لكل طلب ومعدل استبدالها بوجبات مجانية لجميع المطاعم</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-100 text-amber-900 shrink-0">
                برنامج ولاء الزبائن
            </span>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            {{-- Earn Points --}}
            <div class="rounded-xl bg-amber-50/40 p-4.5 border border-amber-200/80 space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-extrabold text-stone-900 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>اكتساب النقاط (عند شراء الوجبات)</span>
                    </label>
                    <span class="text-[11px] font-mono text-stone-500 font-bold">شيكل / نقطة</span>
                </div>
                <input type="number" step="0.01" min="0.01" id="global_points_earn" name="settings[points_per_amount]" value="{{ $pointsEarnVal }}" class="text-sm font-bold font-mono">
                <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                    <span class="text-[10px] text-stone-500 font-bold">خيارات سريعة:</span>
                    <button type="button" onclick="setGlobalEarnRate(10)" class="px-2.5 py-1 rounded-md bg-emerald-100 hover:bg-emerald-200 text-emerald-900 text-[10px] font-bold transition-colors cursor-pointer">كل 10 ₪ = نقطة</button>
                    <button type="button" onclick="setGlobalEarnRate(5)" class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition-colors cursor-pointer">كل 5 ₪ = نقطة</button>
                    <button type="button" onclick="setGlobalEarnRate(1)" class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition-colors cursor-pointer">كل 1 ₪ = نقطة</button>
                </div>
                <div class="text-[11px] leading-relaxed text-slate-600 bg-white p-3 rounded-lg border border-slate-200">
                    <strong class="text-emerald-700 block mb-0.5 font-bold">💡 شرح مبسط:</strong>
                    المبلغ الذي ينفقه الزبون ليكسب <span class="font-bold text-stone-900">نقطة واحدة</span>.
                    <br>
                    • إذا وضعت <span class="font-mono font-bold text-stone-900">10</span> (أو ضغطت الزر أعلاه): كل <span class="font-bold">10 شيكل</span> مشتريات = <span class="font-bold">1 نقطة</span> (طلب بقيمة 100 ₪ يكسب الزبون 10 نقاط).
                    <br>
                    • إذا وضعت <span class="font-mono font-bold text-stone-900">1</span>: كل 1 شيكل مشتريات = 1 نقطة.
                </div>
            </div>

            {{-- Redeem Points --}}
            <div class="rounded-xl bg-amber-50/40 p-4.5 border border-amber-200/80 space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-extrabold text-stone-900 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>استبدال النقاط (طلب وجبة مجانية)</span>
                    </label>
                    <span class="text-[11px] font-mono text-stone-500 font-bold">شيكل / نقطة</span>
                </div>
                <input type="number" step="0.01" min="0.01" id="global_points_redeem" name="settings[points_redeem_per_amount]" value="{{ $pointsRedeemVal }}" class="text-sm font-bold font-mono">
                <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                    <span class="text-[10px] text-stone-500 font-bold">خيارات سريعة:</span>
                    <button type="button" onclick="setGlobalRedeemRate(0.10)" class="px-2.5 py-1 rounded-md bg-amber-100 hover:bg-amber-200 text-amber-900 text-[10px] font-bold transition-colors cursor-pointer">كل 1 ₪ = 10 نقاط</button>
                    <button type="button" onclick="setGlobalRedeemRate(0.20)" class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition-colors cursor-pointer">كل 1 ₪ = 5 نقاط</button>
                    <button type="button" onclick="setGlobalRedeemRate(1)" class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition-colors cursor-pointer">كل 1 ₪ = نقطة</button>
                </div>
                <div class="text-[11px] leading-relaxed text-slate-600 bg-white p-3 rounded-lg border border-slate-200">
                    <strong class="text-amber-800 block mb-0.5 font-bold">💡 شرح مبسط:</strong>
                    قيمة النقطة بالشيكل عند استبدالها بوجبة من المنيو.
                    <br>
                    • إذا وضعت <span class="font-mono font-bold text-amber-700">0.10</span> (أو ضغطت الزر أعلاه): كل 1 ₪ يحتاج <span class="font-bold">10 نقاط</span> (وجبة بـ 30 ₪ تتطلب 300 نقطة).
                    <br>
                    • إذا وضعت <span class="font-mono font-bold text-stone-900">1</span>: كل 1 ₪ يحتاج 1 نقطة (وجبة بـ 30 ₪ تتطلب 30 نقطة).
                </div>
            </div>
        </div>

        {{-- Include delivery fee in points calculation --}}
        <div class="rounded-xl bg-surface-container-low p-4 border border-slate-200/70 flex items-center justify-between gap-4">
            <div>
                <label class="block text-xs font-bold text-stone-900">احتساب النقاط على رسوم التوصيل أيضاً؟</label>
                <p class="text-[11px] text-on-surface-variant">عند التفعيل، يحصل الزبون على نقاط عن قيمة الوجبات + رسوم التوصيل معاً.</p>
            </div>
            <select name="settings[points_include_delivery]" class="text-xs font-bold w-44 rounded-lg">
                <option value="1" @selected($pointsDeliveryVal === '1')>نعم (شامل التوصيل)</option>
                <option value="0" @selected($pointsDeliveryVal === '0')>لا (الوجبات فقط)</option>
            </select>
        </div>

        {{-- Live Preview Calculator --}}
        <div class="rounded-xl bg-surface-container-low border border-slate-200/80 p-4 text-xs">
            <div class="font-bold text-stone-900 flex items-center gap-1.5 mb-2">
                <span class="material-symbols-outlined text-primary text-[18px]">calculate</span>
                <span>معاينة حية ومباشرة للحسبة حسب الأرقام العامة المدخلة:</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 text-stone-700">
                <div class="p-3 bg-white rounded-lg border border-slate-200">
                    <div class="text-slate-500 text-[11px]">طلب بقيمة 100 ₪ من أي مطعم عام:</div>
                    <div class="font-bold text-emerald-800 mt-1" id="global_preview_earn">
                        يكسب الزبون: <strong>100 نقطة</strong>
                    </div>
                </div>
                <div class="p-3 bg-white rounded-lg border border-slate-200">
                    <div class="text-slate-500 text-[11px]">وجبة من المنيو سعرها 30 ₪:</div>
                    <div class="font-bold text-amber-900 mt-1" id="global_preview_redeem">
                        تتطلب للاستبدال: <strong>30 نقطة</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. General System & Duration Settings --}}
    @php
        $excludedKeys = [
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
        ];
        $generalSettings = $settings->whereNotIn('key', $excludedKeys);
    @endphp

    @if($generalSettings->isNotEmpty())
        <div id="setting-general-system" class="rounded-2xl border border-slate-200/80 bg-white/95 p-5 sm:p-6 space-y-5 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-stone-700 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px]">tune</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-on-surface">إعدادات النظام والمدد والتنبيهات</h2>
                        <p class="text-xs text-on-surface-variant">التحكم في فترات الصلاحيات، وتنبيهات انتهاء العضويات واشتراكات المطاعم</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-stone-700 shrink-0">
                    إعدادات عامة
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($generalSettings as $setting)
                    <div id="setting-{{ $setting->key }}" class="p-4 rounded-xl bg-surface-container-low border border-slate-200/70 space-y-2">
                        <label class="block text-xs font-bold text-on-surface">{{ $setting->label }}</label>
                        <input name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="text-sm font-bold">
                        @if(! empty($hints[$setting->key]))
                            <p class="text-[11px] leading-relaxed text-on-surface-variant">{{ $hints[$setting->key] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Sticky Floating Save Bar --}}
    <div class="sticky bottom-4 z-20 flex items-center justify-between gap-4 p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-slate-200 shadow-lg">
        <div class="flex items-center gap-2 text-stone-600 text-xs font-medium">
            <span class="material-symbols-outlined text-amber-600 text-[20px]">info</span>
            <span>تأكد من مراجعة الإعدادات، ثم اضغط حفظ لتطبيق التغييرات فوراً في كامل المنصة.</span>
        </div>
        <button type="submit" class="admin-btn admin-btn--primary px-8 py-3 text-base flex items-center gap-2 shadow-sm cursor-pointer hover:opacity-95 transition-opacity shrink-0">
            <span class="material-symbols-outlined text-[20px]">save</span>
            <span>حفظ كافة الإعدادات</span>
        </button>
    </div>
</form>

<script>
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
                earnText.innerHTML = `يكسب الزبون: <span class="text-stone-900 font-mono text-sm">${earnedPoints} نقطة</span> (كل ${earnRate} ₪ = 1 نقطة)`;
            }

            const sampleItemPrice = 30;
            const redeemPoints = Math.round(sampleItemPrice / redeemRate);
            const ptsPerShekel = (1 / redeemRate).toFixed(1).replace(/\.0$/, '');
            if (redeemText) {
                redeemText.innerHTML = `تتطلب للاستبدال: <span class="text-stone-900 font-mono text-sm">${redeemPoints} نقطة</span> (كل 1 ₪ = ${ptsPerShekel} نقطة)`;
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
    })();
</script>
@endsection
