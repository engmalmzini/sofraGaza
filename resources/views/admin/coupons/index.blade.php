@extends('layouts.admin')

@section('kicker', 'العروض والخصومات')
@section('title', 'أكواد الخصم والكوبونات')

@section('content')
<div class="space-y-6">

    {{-- 1. Summary Metrics Cards --}}
    <div class="admin-metrics !grid-cols-2 lg:!grid-cols-4">
        <div class="admin-metric">
            <div class="admin-metric__top">
                <span>إجمالي الكوبونات</span>
                <span class="admin-metric__icon">
                    <span class="material-symbols-outlined">local_offer</span>
                </span>
            </div>
            <strong>{{ number_format($totalCount) }}</strong>
            <small class="admin-metric__note">كافة الأكواد المنشأة بالنظام</small>
        </div>

        <div class="admin-metric">
            <div class="admin-metric__top">
                <span>الكوبونات الفعّالة</span>
                <span class="admin-metric__icon !bg-emerald-50 !text-emerald-700 !border-emerald-200">
                    <span class="material-symbols-outlined">check_circle</span>
                </span>
            </div>
            <strong class="!text-emerald-700">{{ number_format($activeCount) }}</strong>
            <small class="admin-metric__note">جاهزة للاستخدام من الزبائن</small>
        </div>

        <div class="admin-metric admin-metric--accent">
            <div class="admin-metric__top">
                <span>مرات الاستخدام</span>
                <span class="admin-metric__icon">
                    <span class="material-symbols-outlined">shopping_cart_checkout</span>
                </span>
            </div>
            <strong>{{ number_format($totalUses) }}</strong>
            <small class="admin-metric__note">إجمالي الطلبات بخصومات مطبقة</small>
        </div>

        <div class="admin-metric">
            <div class="admin-metric__top">
                <span>المعطلة أو المنتهية</span>
                <span class="admin-metric__icon !bg-slate-100 !text-slate-600 !border-slate-200">
                    <span class="material-symbols-outlined">event_busy</span>
                </span>
            </div>
            <strong class="!text-slate-700">{{ number_format($expiredCount + $disabledCount) }}</strong>
            <small class="admin-metric__note">{{ $expiredCount }} منتهية الصلاحية • {{ $disabledCount }} معطلة</small>
        </div>
    </div>

    {{-- 2. Filters, Search & Actions Toolbar --}}
    <div class="admin-toolbar flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <div class="admin-chips overflow-x-auto pb-1 lg:pb-0">
            <a class="admin-chip {{ !request('status') ? 'is-active' : '' }}" 
               href="{{ route('admin.coupons.index', request()->except(['status', 'page'])) }}">
                الكل ({{ $totalCount }})
            </a>
            <a class="admin-chip {{ request('status') === 'active' ? 'is-active' : '' }}" 
               href="{{ route('admin.coupons.index', array_merge(request()->except('page'), ['status' => 'active'])) }}">
                الفعّالة ({{ $activeCount }})
            </a>
            <a class="admin-chip {{ request('status') === 'disabled' ? 'is-active' : '' }}" 
               href="{{ route('admin.coupons.index', array_merge(request()->except('page'), ['status' => 'disabled'])) }}">
                المعطّلة ({{ $disabledCount }})
            </a>
            <a class="admin-chip {{ request('status') === 'expired' ? 'is-active' : '' }}" 
               href="{{ route('admin.coupons.index', array_merge(request()->except('page'), ['status' => 'expired'])) }}">
                المنتهية ({{ $expiredCount }})
            </a>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('admin.coupons.index') }}" class="flex flex-wrap items-center gap-2 flex-1 sm:flex-initial">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                {{-- Restaurant Filter --}}
                <select name="restaurant_id" onchange="this.form.submit()" class="!w-auto !min-h-[2.3rem] !py-1 !px-3 !text-xs !rounded-full">
                    <option value="">كافة النطاقات</option>
                    <option value="general" @selected(request('restaurant_id') === 'general')>كافة المطاعم (عام)</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" @selected(request('restaurant_id') == $restaurant->id)>
                            خاص بمطعم: {{ $restaurant->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Search Input --}}
                <div class="relative flex-1 sm:flex-initial">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="بحث بالرمز أو المطعم..." 
                           class="!min-h-[2.3rem] !py-1 !pr-8 !pl-3 !text-xs !rounded-full w-full sm:w-48">
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 !text-base pointer-events-none">search</span>
                </div>

                @if(request()->anyFilled(['q', 'restaurant_id', 'status']))
                    <a href="{{ route('admin.coupons.index') }}" class="admin-action-btn admin-action-btn--ghost admin-action-btn--sm" title="إعادة تعيين الفلاتر">
                        <span class="material-symbols-outlined">restart_alt</span>
                        <span>إلغاء</span>
                    </a>
                @endif
            </form>

            {{-- Toggle Create Form Button --}}
            <button type="button" 
                    id="btn-toggle-create"
                    onclick="toggleCreateCouponForm()"
                    class="admin-btn admin-btn--primary !min-h-[2.3rem] !px-4 !py-1 !text-xs flex items-center gap-1.5 font-bold shadow-sm">
                <span class="material-symbols-outlined !text-[18px]" id="toggle-create-icon">add</span>
                <span id="toggle-create-text">كود خصم جديد</span>
            </button>
        </div>
    </div>

    {{-- 3. Collapsible Create Coupon Card --}}
    <div id="create-coupon-card" class="{{ $errors->any() && !old('_is_edit') ? 'block' : 'hidden' }} transition-all duration-300">
        <div class="admin-card border-2 border-primary/20 bg-white shadow-md">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[24px]">local_offer</span>
                    <div>
                        <h2 class="text-base font-bold text-on-surface">إنشاء كود خصم جديد</h2>
                        <p class="text-xs text-on-surface-variant">قم بإنشاء كوبون خصم جديد مع تحديد نسبته أو قيمته وشروط استخدامه</p>
                    </div>
                </div>
                <button type="button" 
                        onclick="toggleCreateCouponForm(false)" 
                        class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors"
                        title="إغلاق النموذج">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            @if($errors->any() && !old('_is_edit'))
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="font-bold flex items-center gap-1.5 mb-1">
                        <span class="material-symbols-outlined text-base">error</span>
                        <span>يرجى تصحيح الأخطاء التالية:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pr-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-xs">
                    <div>
                        <label class="block font-bold text-on-surface mb-1">
                            رمز الكود <span class="text-primary">*</span>
                            <span class="text-[10px] text-slate-400 font-normal">(بالإنجليزية)</span>
                        </label>
                        <input type="text" 
                               name="code" 
                               value="{{ old('code') }}" 
                               placeholder="مثلاً: RAMADAN15" 
                               required 
                               class="uppercase font-mono font-black text-sm w-full tracking-wider !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">نطاق الكوبون (المطعم المشمول)</label>
                        <select name="restaurant_id" class="text-xs w-full !h-10 rounded-lg">
                            <option value="">كافة المطاعم (عام)</option>
                            @foreach($restaurants as $restaurant)
                                <option value="{{ $restaurant->id }}" @selected(old('restaurant_id') == $restaurant->id)>
                                    خاص بمطعم: {{ $restaurant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">نوع الخصم <span class="text-primary">*</span></label>
                        <select name="type" required class="text-xs w-full !h-10 rounded-lg">
                            <option value="percent" @selected(old('type') === 'percent')>نسبة مئوية (%)</option>
                            <option value="fixed" @selected(old('type') === 'fixed')>مبلغ ثابت (₪)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">قيمة الخصم <span class="text-primary">*</span></label>
                        <input type="number" 
                               step="0.5" 
                               name="value" 
                               value="{{ old('value') }}" 
                               placeholder="مثلاً: 15 أو 20" 
                               required 
                               class="text-sm font-bold w-full !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">الحد الأدنى للطلب (₪)</label>
                        <input type="number" 
                               step="1" 
                               name="min_order_amount" 
                               value="{{ old('min_order_amount') }}" 
                               placeholder="مثلاً: 50 (اختياري)" 
                               class="text-xs w-full !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">الحد الأقصى للخصم (₪)</label>
                        <input type="number" 
                               step="1" 
                               name="max_discount" 
                               value="{{ old('max_discount') }}" 
                               placeholder="مثلاً: 25 للنسب المئوية" 
                               class="text-xs w-full !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">الحد الأقصى لمرات الاستخدام</label>
                        <input type="number" 
                               name="usage_limit" 
                               value="{{ old('usage_limit') }}" 
                               placeholder="مثلاً: 100 (اختياري)" 
                               class="text-xs w-full !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">تاريخ انتهاء الصلاحية</label>
                        <input type="datetime-local" 
                               name="expires_at" 
                               value="{{ old('expires_at') }}" 
                               class="text-xs w-full !h-10">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block font-bold text-on-surface mb-1">ملاحظات أو وصف الكوبون (اختياري)</label>
                        <input type="text" 
                               name="description" 
                               value="{{ old('description') }}" 
                               placeholder="مثلاً: كود خصم لعملاء العيد الأول أو حملة الترويج..." 
                               class="text-xs w-full !h-10">
                    </div>

                    <div class="flex items-center gap-2 pt-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer font-bold text-xs select-none">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" checked>
                            <span>تفعيل الكوبون فوراً</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="toggleCreateCouponForm(false)" class="admin-btn admin-btn--outline !min-h-[2.5rem] !px-4 !text-xs font-bold">
                        إلغاء
                    </button>
                    <button type="submit" class="admin-btn admin-btn--primary !min-h-[2.5rem] !px-6 !text-xs flex items-center gap-1.5 font-bold shadow-md">
                        <span class="material-symbols-outlined !text-[18px]">check</span>
                        <span>إنشاء وحفظ الكود</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. Coupons List Card --}}
    <div class="admin-card !p-0 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary text-[22px]">confirmation_number</span>
                <div>
                    <h2 class="text-base font-bold text-on-surface">سجل أكواد الخصم والكوبونات</h2>
                    <p class="text-xs text-on-surface-variant">إدارة وتعديل الكوبونات والتحكم في حدود استخدامها وصلاحياتها</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    <span>عدد الكوبونات:</span>
                    <strong class="font-mono text-stone-900">{{ $coupons->total() }}</strong>
                </span>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="w-[22%]">رمز الكود</th>
                        <th class="w-[15%]">قيمة الخصم</th>
                        <th class="w-[16%]">النطاق والمطعم</th>
                        <th class="w-[13%]">الحدود والشروط</th>
                        <th class="w-[10%]">الاستخدام</th>
                        <th class="w-[10%]">الصلاحية</th>
                        <th class="w-[8%]">الحالة</th>
                        <th class="text-center w-[6%]">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- 1. Code & Description --}}
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-stone-900 text-white font-mono font-black text-xs tracking-wider shadow-sm select-all">
                                        <span class="material-symbols-outlined text-[15px] text-primary">local_offer</span>
                                        <span>{{ $coupon->code }}</span>
                                    </span>
                                    <button type="button" 
                                            onclick="copyCouponCode('{{ $coupon->code }}', this)" 
                                            class="text-slate-400 hover:text-primary transition-colors p-1 rounded hover:bg-slate-100" 
                                            title="نسخ رمز الكوبون">
                                        <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                    </button>
                                </div>
                                @if($coupon->description)
                                    <div class="text-[11px] text-slate-500 mt-1 max-w-[220px] truncate" title="{{ $coupon->description }}">
                                        {{ $coupon->description }}
                                    </div>
                                @endif
                            </td>

                            {{-- 2. Discount Value (Clean, no circular blob, no broken wrapping) --}}
                            <td class="whitespace-nowrap">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="font-mono font-black text-stone-900 text-base">
                                        @if($coupon->type === 'percent')
                                            {{ (float) $coupon->value }}%
                                        @else
                                            {{ number_format($coupon->value, 2) }} ₪
                                        @endif
                                    </span>
                                    <span class="text-[11px] font-semibold text-slate-400">
                                        {{ $coupon->type === 'percent' ? 'نسبة مئوية' : 'مبلغ ثابت' }}
                                    </span>
                                </div>
                                @if($coupon->max_discount && $coupon->type === 'percent')
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        حد أقصى للخصم: <strong class="font-mono text-stone-800">{{ number_format($coupon->max_discount, 0) }} ₪</strong>
                                    </div>
                                @endif
                            </td>

                            {{-- 3. Scope & Restaurant --}}
                            <td class="whitespace-nowrap">
                                @if($coupon->restaurant)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-orange-50 text-stone-900 border border-orange-200/80 font-bold text-xs">
                                        <span class="material-symbols-outlined text-[15px] text-primary">storefront</span>
                                        <span class="max-w-[140px] truncate" title="{{ $coupon->restaurant->name }}">{{ $coupon->restaurant->name }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200/70 font-semibold text-xs">
                                        <span class="material-symbols-outlined text-[15px] text-slate-500">public</span>
                                        <span>كافة المطاعم</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 4. Conditions --}}
                            <td class="whitespace-nowrap text-xs">
                                @if($coupon->min_order_amount)
                                    <div class="text-slate-700">
                                        <span class="text-slate-400 text-[11px]">حد أدنى للطلب:</span>
                                        <strong class="font-mono text-stone-900 mr-1">{{ number_format($coupon->min_order_amount, 0) }} ₪</strong>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs">بدون حد أدنى</span>
                                @endif
                            </td>

                            {{-- 5. Usage Count --}}
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-1.5 font-mono text-xs">
                                    <strong class="font-black text-stone-900 text-sm">{{ $coupon->used_count }}</strong>
                                    @if($coupon->usage_limit)
                                        <span class="text-slate-400 font-normal">/ {{ $coupon->usage_limit }}</span>
                                    @else
                                        <span class="text-slate-400 text-[11px] font-sans font-normal">(غير محدود)</span>
                                    @endif
                                </div>
                                @if($coupon->usage_limit)
                                    @php $percent = min(100, round(($coupon->used_count / $coupon->usage_limit) * 100)); @endphp
                                    <div class="w-16 bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                                        <div class="bg-primary h-1 rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                @endif
                            </td>

                            {{-- 6. Expiry / Validity --}}
                            <td class="whitespace-nowrap text-xs">
                                @if($coupon->expires_at)
                                    <div>
                                        <span class="{{ $coupon->isExpired() ? 'text-rose-600 font-bold' : 'text-slate-800 font-semibold' }} font-mono">
                                            {{ $coupon->expires_at->format('Y/m/d') }}
                                        </span>
                                        <div class="text-[10px] mt-0.5">
                                            @if($coupon->isExpired())
                                                <span class="text-rose-600 font-bold">منتهي الصلاحية</span>
                                            @else
                                                <span class="text-slate-400">{{ $coupon->expires_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-500 font-bold text-xs">
                                        <span class="material-symbols-outlined text-[14px] text-slate-400">all_inclusive</span>
                                        <span>دائم</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 7. Status --}}
                            <td class="whitespace-nowrap">
                                @if($coupon->is_active && ! $coupon->isExpired() && ! $coupon->hasReachedLimit())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>مفعّل</span>
                                    </span>
                                @elseif($coupon->isExpired())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-800 border border-rose-200 text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        <span>منتهي</span>
                                    </span>
                                @elseif($coupon->hasReachedLimit())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                        <span>استُنفد</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>معطّل</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 8. Actions (Icons ONLY without text - strictly as user requested!) --}}
                            <td class="whitespace-nowrap text-center">
                                <div class="admin-table-actions justify-center gap-1">
                                    {{-- Edit (Icon Only) --}}
                                    <button type="button" 
                                            class="admin-action-btn admin-action-btn--outline admin-action-btn--icon btn-edit-coupon"
                                            data-id="{{ $coupon->id }}"
                                            data-code="{{ $coupon->code }}"
                                            data-restaurant-id="{{ $coupon->restaurant_id ?? '' }}"
                                            data-type="{{ $coupon->type }}"
                                            data-value="{{ $coupon->value }}"
                                            data-min-order="{{ $coupon->min_order_amount ?? '' }}"
                                            data-max-discount="{{ $coupon->max_discount ?? '' }}"
                                            data-usage-limit="{{ $coupon->usage_limit ?? '' }}"
                                            data-expires-at="{{ $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '' }}"
                                            data-is-active="{{ $coupon->is_active ? '1' : '0' }}"
                                            data-description="{{ $coupon->description ?? '' }}"
                                            onclick="openEditCouponModal(this)"
                                            title="تعديل كود الخصم">
                                        <span class="material-symbols-outlined">edit</span>
                                    </button>

                                    {{-- Toggle (Icon Only) --}}
                                    <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="admin-action-btn {{ $coupon->is_active ? 'admin-action-btn--outline' : 'admin-action-btn--primary' }} admin-action-btn--icon"
                                                title="{{ $coupon->is_active ? 'تعطيل الكود' : 'تفعيل الكود' }}">
                                            <span class="material-symbols-outlined">{{ $coupon->is_active ? 'pause' : 'play_arrow' }}</span>
                                        </button>
                                    </form>

                                    {{-- Delete (Icon Only) --}}
                                    <form method="POST" 
                                          action="{{ route('admin.coupons.destroy', $coupon) }}" 
                                          onsubmit="return confirm('هل أنت متأكد من حذف كود الخصم {{ $coupon->code }} نهائياً؟')" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="admin-action-btn admin-action-btn--danger admin-action-btn--icon" 
                                                title="حذف كود الخصم">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-on-surface-variant">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <span class="material-symbols-outlined text-slate-300 text-5xl">local_offer</span>
                                    <p class="text-sm font-bold text-slate-700">لا توجد أكواد خصم تطابق الفلاتر الحالية</p>
                                    <p class="text-xs text-slate-400">يمكنك إنشاء كود خصم جديد بسهولة بالضغط على زر "كود خصم جديد" أعلاه.</p>
                                    <button type="button" onclick="toggleCreateCouponForm(true)" class="admin-btn admin-btn--primary !min-h-[2.3rem] !px-4 !text-xs font-bold inline-flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">add</span>
                                        <span>إنشاء أول كود خصم</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="p-4 border-t border-slate-100 bg-white">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>

</div>

{{-- 5. Edit Coupon Modal --}}
<div id="edit-coupon-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-stone-900/60 backdrop-blur-sm transition-opacity duration-200">
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-in fade-in zoom-in-95">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-stone-900 text-white">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[24px]">edit_note</span>
                    <div>
                        <h3 class="text-base font-bold text-white">تعديل كود الخصم</h3>
                        <p class="text-xs text-slate-300 font-mono" id="modal-code-preview">--</p>
                    </div>
                </div>
                <button type="button" 
                        onclick="closeEditCouponModal()" 
                        class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-stone-800 transition-colors">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            {{-- Modal Body / Form --}}
            <form id="edit-coupon-form" method="POST" action="" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_is_edit" value="1">

                <div class="grid gap-4 sm:grid-cols-2 text-xs">
                    <div>
                        <label class="block font-bold text-on-surface mb-1">
                            رمز الكود <span class="text-primary">*</span>
                            <span class="text-[10px] text-slate-400 font-normal">(بالإنجليزية)</span>
                        </label>
                        <input type="text" 
                               id="edit-field-code"
                               name="code" 
                               required 
                               class="uppercase font-mono font-black text-sm w-full tracking-wider !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">نطاق الكوبون (المطعم المشمول)</label>
                        <select id="edit-field-restaurant_id" name="restaurant_id" class="text-xs w-full !h-10 rounded-lg">
                            <option value="">كافة المطاعم (عام)</option>
                            @foreach($restaurants as $restaurant)
                                <option value="{{ $restaurant->id }}">خاص بمطعم: {{ $restaurant->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">نوع الخصم <span class="text-primary">*</span></label>
                        <select id="edit-field-type" name="type" required class="text-xs w-full !h-10 rounded-lg">
                            <option value="percent">نسبة مئوية (%)</option>
                            <option value="fixed">مبلغ ثابت (₪)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">قيمة الخصم <span class="text-primary">*</span></label>
                        <input type="number" 
                               id="edit-field-value"
                               step="0.5" 
                               name="value" 
                               required 
                               class="text-sm font-bold w-full !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">الحد الأدنى للطلب (₪)</label>
                        <input type="number" 
                               id="edit-field-min_order_amount"
                               step="1" 
                               name="min_order_amount" 
                               placeholder="اختياري" 
                               class="text-xs w-full !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">الحد الأقصى للخصم (₪)</label>
                        <input type="number" 
                               id="edit-field-max_discount"
                               step="1" 
                               name="max_discount" 
                               placeholder="للنسب المئوية فقط" 
                               class="text-xs w-full !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">الحد الأقصى لمرات الاستخدام</label>
                        <input type="number" 
                               id="edit-field-usage_limit"
                               name="usage_limit" 
                               placeholder="اتركه فارغاً للاستخدام غير المحدود" 
                               class="text-xs w-full !h-10">
                    </div>

                    <div>
                        <label class="block font-bold text-on-surface mb-1">تاريخ انتهاء الصلاحية</label>
                        <input type="datetime-local" 
                               id="edit-field-expires_at"
                               name="expires_at" 
                               class="text-xs w-full !h-10">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-on-surface mb-1">حالة التفعيل</label>
                        <select id="edit-field-is_active" name="is_active" class="text-xs w-full !h-10 rounded-lg">
                            <option value="1">مفعّل (يمكن للزبائن استخدامه)</option>
                            <option value="0">معطّل (موقوف مؤقتاً)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-on-surface mb-1">ملاحظات أو وصف الكوبون</label>
                        <input type="text" 
                               id="edit-field-description"
                               name="description" 
                               placeholder="ملاحظات ترويجية داخلية..." 
                               class="text-xs w-full !h-10">
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditCouponModal()" class="admin-btn admin-btn--outline !min-h-[2.5rem] !px-5 !text-xs font-bold">
                        إلغاء
                    </button>
                    <button type="submit" class="admin-btn admin-btn--primary !min-h-[2.5rem] !px-6 !text-xs flex items-center gap-1.5 font-bold shadow-md">
                        <span class="material-symbols-outlined !text-[18px]">save</span>
                        <span>حفظ التعديلات</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    function toggleCreateCouponForm(forceState) {
        const card = document.getElementById('create-coupon-card');
        const icon = document.getElementById('toggle-create-icon');
        const text = document.getElementById('toggle-create-text');
        
        let shouldOpen;
        if (typeof forceState === 'boolean') {
            shouldOpen = forceState;
        } else {
            shouldOpen = card.classList.contains('hidden');
        }

        if (shouldOpen) {
            card.classList.remove('hidden');
            icon.textContent = 'close';
            text.textContent = 'إغلاق النموذج';
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            card.classList.add('hidden');
            icon.textContent = 'add';
            text.textContent = 'كود خصم جديد';
        }
    }

    function openEditCouponModal(btn) {
        const id = btn.getAttribute('data-id');
        const code = btn.getAttribute('data-code') || '';
        const restaurantId = btn.getAttribute('data-restaurant-id') || '';
        const type = btn.getAttribute('data-type') || 'percent';
        const value = btn.getAttribute('data-value') || '';
        const minOrder = btn.getAttribute('data-min-order') || '';
        const maxDiscount = btn.getAttribute('data-max-discount') || '';
        const usageLimit = btn.getAttribute('data-usage-limit') || '';
        const expiresAt = btn.getAttribute('data-expires-at') || '';
        const isActive = btn.getAttribute('data-is-active') || '1';
        const description = btn.getAttribute('data-description') || '';

        // Set action url
        const form = document.getElementById('edit-coupon-form');
        form.action = "{{ url('admin/coupons') }}/" + id;

        // Populate fields
        document.getElementById('modal-code-preview').textContent = 'رمز الكود: ' + code;
        document.getElementById('edit-field-code').value = code;
        document.getElementById('edit-field-restaurant_id').value = restaurantId;
        document.getElementById('edit-field-type').value = type;
        document.getElementById('edit-field-value').value = value;
        document.getElementById('edit-field-min_order_amount').value = minOrder;
        document.getElementById('edit-field-max_discount').value = maxDiscount;
        document.getElementById('edit-field-usage_limit').value = usageLimit;
        document.getElementById('edit-field-expires_at').value = expiresAt;
        document.getElementById('edit-field-is_active').value = isActive;
        document.getElementById('edit-field-description').value = description;

        // Show modal
        const modal = document.getElementById('edit-coupon-modal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditCouponModal() {
        const modal = document.getElementById('edit-coupon-modal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close modal on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditCouponModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('edit-coupon-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditCouponModal();
        }
    });

    // Copy code helper
    function copyCouponCode(code, btn) {
        navigator.clipboard.writeText(code).then(() => {
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined text-[16px] text-emerald-600">done</span>';
            setTimeout(() => {
                btn.innerHTML = originalIcon;
            }, 1800);
        });
    }
</script>
@endsection
