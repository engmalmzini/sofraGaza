@extends('layouts.admin')

@section('kicker', 'العروض والخصومات')
@section('title', 'أكواد الخصم والكوبونات')

@section('content')
<div class="space-y-6">

    {{-- Create Coupon Card --}}
    <div class="admin-card">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
            <span class="material-symbols-outlined text-primary text-[22px]">local_offer</span>
            <div>
                <h2 class="text-base font-bold text-on-surface">إضافة كود خصم جديد</h2>
                <p class="text-xs text-on-surface-variant">أنشئ كوبون خصم يمكن للزبائن استخدامه أثناء الدفع</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.coupons.store') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end text-xs">
            @csrf
            <div>
                <label class="block font-bold text-on-surface mb-1">رمز الكود (بالإنجليزية)</label>
                <input type="text" name="code" placeholder="مثلاً: GAZA10" required class="uppercase font-mono font-bold text-sm w-full">
            </div>

            <div>
                <label class="block font-bold text-on-surface mb-1">نوع الخصم</label>
                <select name="type" required class="text-xs w-full h-10 rounded-lg">
                    <option value="percent">نسبة مئوية (%)</option>
                    <option value="fixed">مبلغ ثابت (₪)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-on-surface mb-1">قيمة الخصم (% أو ₪)</label>
                <input type="number" step="0.5" name="value" placeholder="مثلاً: 10 أو 15" required class="text-sm w-full">
            </div>

            <div>
                <label class="block font-bold text-on-surface mb-1">الحد الأدنى للطلب (₪) - اختياري</label>
                <input type="number" step="1" name="min_order_amount" placeholder="مثلاً: 30" class="text-xs w-full">
            </div>

            <div>
                <label class="block font-bold text-on-surface mb-1">الحد الأقصى للخصم (₪) - اختياري</label>
                <input type="number" step="1" name="max_discount" placeholder="مثلاً: 20" class="text-xs w-full">
            </div>

            <div>
                <label class="block font-bold text-on-surface mb-1">الحد الأقصى لمرات الاستخدام - اختياري</label>
                <input type="number" name="usage_limit" placeholder="مثلاً: 100" class="text-xs w-full">
            </div>

            <div>
                <label class="block font-bold text-on-surface mb-1">تاريخ انتهاء الصلاحية - اختياري</label>
                <input type="datetime-local" name="expires_at" class="text-xs w-full">
            </div>

            <div>
                <button type="submit" class="admin-btn admin-btn--primary w-full h-10 flex items-center justify-center gap-1.5 font-bold">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>إنشاء الكود الآن</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Coupons List --}}
    <div class="admin-card overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-base font-bold text-on-surface">قائمة أكواد الخصم ({{ $coupons->total() }})</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant border-b border-slate-200">
                        <th class="py-3 px-4 font-bold">رمز الكود</th>
                        <th class="py-3 px-4 font-bold">قيمة الخصم</th>
                        <th class="py-3 px-4 font-bold">الشروط والحدود</th>
                        <th class="py-3 px-4 font-bold">الاستخدام</th>
                        <th class="py-3 px-4 font-bold">الصلاحية</th>
                        <th class="py-3 px-4 font-bold">الحالة</th>
                        <th class="py-3 px-4 font-bold">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-1 rounded-lg bg-surface-container-high font-mono font-black text-sm text-stone-900 tracking-wider">
                                    {{ $coupon->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-800 text-sm">
                                {{ $coupon->formatDiscountLabel() }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                @if($coupon->min_order_amount)
                                    <div>الحد الأدنى: {{ number_format($coupon->min_order_amount, 0) }} ₪</div>
                                @endif
                                @if($coupon->max_discount && $coupon->type === 'percent')
                                    <div>أقصى خصم: {{ number_format($coupon->max_discount, 0) }} ₪</div>
                                @endif
                                @if(! $coupon->min_order_amount && ! $coupon->max_discount)
                                    <span class="text-slate-400">بدون قيود</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold">
                                {{ $coupon->used_count }}
                                @if($coupon->usage_limit)
                                    <span class="text-slate-400">/ {{ $coupon->usage_limit }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($coupon->expires_at)
                                    <span class="{{ $coupon->isExpired() ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                        {{ $coupon->expires_at->format('Y/m/d') }}
                                        @if($coupon->isExpired())
                                            (منتهي)
                                        @endif
                                    </span>
                                @else
                                    <span class="text-slate-400">دائم</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($coupon->is_active && ! $coupon->isExpired() && ! $coupon->hasReachedLimit())
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-semibold text-[11px]">
                                        مفعّل
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-semibold text-[11px]">
                                        معطّل
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}">
                                        @csrf
                                        <button class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 font-semibold text-[11px] text-slate-700 transition-colors">
                                            {{ $coupon->is_active ? 'تعطيل' : 'تفعيل' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('حذف كود الخصم هذا نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-2 py-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold text-[11px] transition-colors">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-on-surface-variant">
                                لا توجد أكواد خصم بعد. استخدم النموذج أعلاه لإنشاء أول كود خصم.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
