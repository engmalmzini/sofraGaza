@extends('layouts.public')

@section('title', 'تفاصيل الطلب')

@section('content')
<div class="mx-auto max-w-2xl px-margin lg:px-margin-desktop py-4 sm:py-6 lg:py-8">
    {{-- 1. Back link (Desktop only; mobile uses the sticky top app bar) --}}
    <div class="hidden lg:block mb-4">
        <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-stone-500 hover:text-primary transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            <span>العودة لقائمة الطلبات</span>
        </a>
    </div>

    {{-- 2. Page Header & Order ID --}}
    <div class="flex items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight">طلب #{{ $order->id }}</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">
                {{ $order->restaurant->name }} · {{ $order->statusLabel() }}
                @if($order->isGroupOrder())
                    · طلب جماعي
                @endif
            </p>
        </div>

        <div class="text-left shrink-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold
                @if($order->status === 'delivered') bg-emerald-50 text-emerald-700 border border-emerald-200
                @elseif(in_array($order->status, ['cancelled', 'rejected'])) bg-rose-50 text-rose-700 border border-rose-200
                @else bg-primary/10 text-primary border border-primary/20 @endif">
                <span class="w-2 h-2 rounded-full 
                    @if($order->status === 'delivered') bg-emerald-500
                    @elseif(in_array($order->status, ['cancelled', 'rejected'])) bg-rose-500
                    @else bg-primary animate-pulse @endif"></span>
                <span>{{ $order->statusLabel() }}</span>
            </span>
        </div>
    </div>

    {{-- 3. Live Status Stepper Tracker --}}
    @php
        $isCancelled = in_array($order->status, ['cancelled', 'rejected']);
        $step = match($order->status) {
            'pending_confirmation' => 1,
            'confirmed', 'preparing' => 2,
            'delivering' => 3,
            'delivered' => 4,
            default => 1,
        };
    @endphp

    @if(!$isCancelled)
        <div class="rounded-3xl bg-white border border-stone-200/80 p-4 sm:p-5 shadow-2xs mb-4">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-stone-500">متابعة مسار الطلب</span>
                <span class="text-[11px] text-stone-400 font-mono" dir="ltr">{{ $order->created_at?->format('H:i') }}</span>
            </div>

            <div class="relative mt-2 mb-2 px-2">
                {{-- Progress Track --}}
                <div class="absolute top-4 inset-x-8 h-1 bg-stone-100 -translate-y-1/2 z-0">
                    <div class="h-full bg-primary transition-all duration-500" 
                         style="width: {{ $step === 1 ? '15%' : ($step === 2 ? '48%' : ($step === 3 ? '80%' : '100%')) }};"></div>
                </div>

                {{-- Steps Icons --}}
                <div class="relative z-10 flex items-center justify-between">
                    {{-- Step 1 --}}
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                            {{ $step >= 1 ? 'bg-primary text-white shadow-2xs ring-2 ring-primary/20' : 'bg-stone-100 text-stone-400' }}">
                            <span class="material-symbols-outlined text-[17px]">receipt_long</span>
                        </div>
                        <span class="text-[11px] font-bold mt-1.5 {{ $step >= 1 ? 'text-stone-900 font-extrabold' : 'text-stone-400' }}">تم الطلب</span>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                            {{ $step >= 2 ? 'bg-primary text-white shadow-2xs ring-2 ring-primary/20' : 'bg-stone-100 text-stone-400' }}">
                            <span class="material-symbols-outlined text-[17px]">soup_kitchen</span>
                        </div>
                        <span class="text-[11px] font-bold mt-1.5 {{ $step >= 2 ? 'text-stone-900 font-extrabold' : 'text-stone-400' }}">التحضير</span>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                            {{ $step >= 3 ? 'bg-primary text-white shadow-2xs ring-2 ring-primary/20' : 'bg-stone-100 text-stone-400' }}">
                            <span class="material-symbols-outlined text-[17px]">moped</span>
                        </div>
                        <span class="text-[11px] font-bold mt-1.5 {{ $step >= 3 ? 'text-stone-900 font-extrabold' : 'text-stone-400' }}">في الطريق</span>
                    </div>

                    {{-- Step 4 --}}
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                            {{ $step >= 4 ? 'bg-emerald-600 text-white shadow-2xs ring-2 ring-emerald-600/20' : 'bg-stone-100 text-stone-400' }}">
                            <span class="material-symbols-outlined text-[17px]">check_circle</span>
                        </div>
                        <span class="text-[11px] font-bold mt-1.5 {{ $step >= 4 ? 'text-emerald-700 font-extrabold' : 'text-stone-400' }}">تم الاستلام</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Cancelled/Rejected Alert --}}
        <div class="rounded-3xl bg-rose-50 border border-rose-200/90 p-4 shadow-2xs mb-4 flex items-start gap-3">
            <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">cancel</span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-extrabold text-rose-950">
                    {{ $order->status === 'rejected' ? 'تم رفض الطلب من قِبل المطعم' : 'تم إلغاء هذا الطلب' }}
                </h3>
                @if($order->rejection_reason)
                    <p class="text-xs text-rose-800 mt-1">سبب الرفض: {{ $order->rejection_reason }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- 4. Restaurant Info Card --}}
    <div class="rounded-3xl bg-white border border-stone-200/80 p-4 shadow-2xs mb-4">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-orange-50 border border-orange-200/60 flex items-center justify-center text-primary shrink-0 overflow-hidden shadow-2xs">
                    @if($order->restaurant->logo_path)
                        <img src="{{ asset('storage/' . $order->restaurant->logo_path) }}" alt="{{ $order->restaurant->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-[24px]">restaurant</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <a href="{{ route('restaurants.show', $order->restaurant) }}" class="font-extrabold text-sm sm:text-base text-stone-900 hover:text-primary transition-colors truncate block">
                        {{ $order->restaurant->name }}
                    </a>
                    <span class="text-xs text-stone-500 block mt-0.5">
                        {{ $order->type === 'redemption' ? 'استبدال نقاط مكافآت' : 'طلب شراء أونلاين' }}
                    </span>
                </div>
            </div>

            <div class="text-left shrink-0">
                <span class="text-xs font-mono font-bold text-stone-600 block" dir="ltr">{{ $order->created_at?->format('Y/m/d') }}</span>
                <span class="text-[11px] font-mono text-stone-400 block" dir="ltr">{{ $order->created_at?->format('H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- 5. Order Items Card (Clean Modern Food Rows) --}}
    <div class="rounded-3xl bg-white border border-stone-200/80 p-4 sm:p-5 shadow-2xs mb-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3 mb-2">
            <h3 class="font-extrabold text-sm text-stone-900 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-[19px]">lunch_dining</span>
                <span>الوجبات المطلوبة</span>
            </h3>
            <span class="text-xs font-bold text-stone-400">{{ $order->items->sum('quantity') }} وجبة</span>
        </div>

        <div class="divide-y divide-stone-100">
            @forelse($order->items as $item)
                <div class="py-3 flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2.5 min-w-0 flex-1">
                        <span class="w-6 h-6 rounded-lg bg-stone-100 text-stone-800 text-xs font-black flex items-center justify-center shrink-0 mt-0.5 font-mono">
                            {{ $item->quantity }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-sm text-stone-900 leading-snug">{{ $item->name }}</h4>
                            @if($item->ordered_by_name)
                                <span class="text-[11px] text-primary font-bold block mt-0.5">طلب {{ $item->ordered_by_name }}</span>
                            @endif
                            @if($item->quantity > 1)
                                <span class="text-[11px] text-stone-400 block mt-0.5">{{ number_format($item->price, 2) }} ₪ للقطعة</span>
                            @endif
                            @if($item->notes)
                                <div class="mt-1.5 inline-flex items-center gap-1 bg-amber-50 border border-amber-200/60 text-amber-900 px-2 py-0.5 rounded-lg text-[11px] font-semibold">
                                    <span class="material-symbols-outlined text-[13px] text-amber-600">edit_note</span>
                                    <span>ملاحظة: {{ $item->notes }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 text-left font-mono font-extrabold text-sm text-stone-900">
                        {{ number_format($item->line_total, 2) }} <span class="ils">₪</span>
                    </div>
                </div>
            @empty
                <p class="py-4 text-center text-xs text-stone-400">لا توجد أصناف مسجلة على هذا الطلب.</p>
            @endforelse
        </div>
    </div>

    @if($order->isGroupOrder())
        <div class="mb-4">
            @include('partials.group-order-receipts', ['order' => $order, 'tone' => 'partner'])
        </div>
    @endif

    {{-- 6. Financial Summary Card --}}
    <div class="rounded-3xl bg-white border border-stone-200/80 p-4 sm:p-5 shadow-2xs mb-4">
        <h3 class="font-extrabold text-sm text-stone-900 flex items-center gap-1.5 border-b border-stone-100 pb-3 mb-3">
            <span class="material-symbols-outlined text-primary text-[19px]">receipt</span>
            <span>ملخص الفاتورة</span>
        </h3>

        <div class="space-y-2.5 text-xs sm:text-sm">
            <div class="flex items-center justify-between text-stone-600">
                <span>مجموع الأصناف</span>
                <span class="font-mono font-bold text-stone-900">{{ number_format($order->subtotal, 2) }} <span class="ils">₪</span></span>
            </div>

            @if((float) $order->discount_amount > 0)
                <div class="flex items-center justify-between text-emerald-700">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">verified</span>
                        <span>خصم العضوية ({{ $order->discount_percent }}%)</span>
                    </span>
                    <span class="font-mono font-bold" dir="ltr">− {{ number_format($order->discount_amount, 2) }} ₪</span>
                </div>
            @endif

            @if($order->coupon_code)
                <div class="flex items-center justify-between text-emerald-700">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">confirmation_number</span>
                        <span>كوبون الخصم ({{ $order->coupon_code }})</span>
                    </span>
                    <span class="font-bold">مُطبّق</span>
                </div>
            @endif

            <div class="flex items-center justify-between text-stone-600">
                <span>رسوم التوصيل @if($order->delivery_area)({{ $order->deliveryAreaLabel() }})@endif</span>
                <span class="font-mono font-bold text-stone-900">
                    @if((float) $order->delivery_fee > 0)
                        {{ number_format($order->delivery_fee, 2) }} <span class="ils">₪</span>
                    @else
                        <span class="text-emerald-700 font-bold">مجاني</span>
                    @endif
                </span>
            </div>

            @if($order->type === 'redemption')
                <div class="flex items-center justify-between text-amber-700">
                    <span>النقاط المستبدلة</span>
                    <span class="font-mono font-bold">{{ number_format($order->points_spent) }} نقطة</span>
                </div>
            @endif

            {{-- Grand Total --}}
            <div class="pt-3 border-t border-stone-200/80 flex items-center justify-between">
                <div>
                    <span class="text-sm font-extrabold text-stone-900 block">الإجمالي المستحق</span>
                    <span class="text-[11px] text-stone-400 block mt-0.5">شامل التوصيل</span>
                </div>
                <div class="text-left font-mono text-xl font-black text-primary">
                    {{ number_format($order->total, 2) }} <span class="ils text-lg">₪</span>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-stone-100 flex items-center justify-between text-xs text-stone-500">
                <span>طريقة الدفع:</span>
                <span class="font-bold text-stone-800 bg-stone-50 border border-stone-200/60 px-2.5 py-1 rounded-lg">
                    {{ $order->paymentMethodLabel() }}
                </span>
            </div>
        </div>
    </div>

    {{-- 7. Delivery Address & Customer Details Card --}}
    <div class="rounded-3xl bg-white border border-stone-200/80 p-4 sm:p-5 shadow-2xs mb-4">
        <h3 class="font-extrabold text-sm text-stone-900 flex items-center gap-1.5 border-b border-stone-100 pb-3 mb-3">
            <span class="material-symbols-outlined text-primary text-[19px]">location_on</span>
            <span>بيانات التوصيل والعنوان</span>
        </h3>

        <div class="space-y-3 text-xs sm:text-sm text-stone-700">
            <div class="flex items-start gap-2.5">
                <span class="material-symbols-outlined text-[18px] text-stone-400 mt-0.5 shrink-0">call</span>
                <div class="min-w-0">
                    <span class="text-stone-400 block text-[11px]">رقم الهاتف</span>
                    <span class="font-mono font-bold text-stone-900 text-sm" dir="ltr">{{ $order->phone }}</span>
                </div>
            </div>

            @if($order->delivery_area)
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-[18px] text-stone-400 mt-0.5 shrink-0">explore</span>
                    <div class="min-w-0">
                        <span class="text-stone-400 block text-[11px]">منطقة التوصيل</span>
                        <strong class="font-bold text-stone-900">{{ $order->deliveryAreaLabel() }}</strong>
                    </div>
                </div>
            @endif

            <div class="flex items-start gap-2.5">
                <span class="material-symbols-outlined text-[18px] text-stone-400 mt-0.5 shrink-0">home_pin</span>
                <div class="min-w-0">
                    <span class="text-stone-400 block text-[11px]">العنوان الدقيق</span>
                    <span class="font-medium text-stone-900 leading-relaxed">{{ $order->address_details }}</span>
                </div>
            </div>

            @if($order->notes)
                <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200/60 p-2.5 rounded-xl text-xs">
                    <span class="material-symbols-outlined text-[16px] text-stone-500 mt-0.5 shrink-0">note</span>
                    <div class="min-w-0">
                        <span class="text-stone-500 font-bold block">ملاحظات الطلب:</span>
                        <span class="text-stone-700">{{ $order->notes }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Cancel Order Action --}}
        @if($order->canCancel())
            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                <span class="text-[11px] text-stone-400">يمكنك إلغاء الطلب قبل خروجه مع الكابتن</span>
                <form method="POST" action="{{ route('account.orders.cancel', $order) }}" onsubmit="return confirm('هل أنت متأكد من رغبتك في إلغاء هذا الطلب؟')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/60 px-3 py-1.5 rounded-xl transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[15px]">close</span>
                        <span>إلغاء الطلب</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- 8. Customer Review Section (When Delivered) --}}
    @if($order->status === 'delivered')
        @if($order->review)
            <div class="rounded-3xl bg-emerald-50/70 border border-emerald-200/80 p-4 sm:p-5 shadow-2xs mb-4">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[22px]">verified</span>
                        <span class="font-bold text-sm text-emerald-950">تقييمك لهذا الطلب</span>
                    </div>
                    <div class="flex items-center gap-0.5">
                        @for($s = 1; $s <= 5; $s++)
                            @if($s <= $order->review->rating)
                                @include('partials.star-icon', ['class' => 'w-4 h-4 text-[#d65e15]'])
                            @else
                                @include('partials.star-icon', ['class' => 'w-4 h-4 text-stone-200'])
                            @endif
                        @endfor
                    </div>
                </div>
                @if($order->review->comment)
                    <p class="text-xs text-slate-700 bg-white/90 rounded-2xl p-3 border border-emerald-100 mt-2">{{ $order->review->comment }}</p>
                @endif
                <div class="mt-2 text-[11px] text-emerald-800/80 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                    <span>تم توثيق تقييمك كطلب مؤكد وحقيقي لهذا المطعم. شكراً لمشاركتك!</span>
                </div>
            </div>
        @else
            <div class="rounded-3xl bg-gradient-to-br from-amber-50/70 via-white to-orange-50/30 border border-amber-200/80 p-4 sm:p-5 shadow-2xs mb-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-amber-500 text-[24px] fill-1">rate_review</span>
                    <h2 class="font-extrabold text-sm sm:text-base text-stone-900">شاركنا رأيك في وجبتك من {{ $order->restaurant->name }}</h2>
                </div>
                <p class="text-xs text-stone-500 mb-4">تقييمك يظهر للزبائن ويساعد في تطوير جودة الطعام وسرعة التوصيل.</p>

                <form method="POST" action="{{ route('restaurants.reviews.store', $order->restaurant) }}" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <div>
                        <label class="block text-xs font-bold text-stone-800 mb-1.5">التقييم بالنجوم:</label>
                        <div class="flex items-center gap-2 flex-wrap">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer group flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-stone-200 bg-white hover:border-[#fed7aa] hover:bg-[#fff7ed] transition-all">
                                    <input type="radio" name="rating" value="{{ $i }}" class="accent-[#d65e15]" required {{ $i === 5 ? 'checked' : '' }}>
                                    @include('partials.star-icon', ['class' => 'w-4 h-4 text-[#d65e15]'])
                                    <span class="text-xs font-bold text-stone-700">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-800 mb-1.5">تعليقك (اختياري):</label>
                        <textarea name="comment" rows="2" class="w-full rounded-2xl border border-stone-200 bg-white p-3 text-xs text-stone-900 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden" placeholder="اكتب ملاحظاتك عن جودة الوجبة وسرعة التوصيل..."></textarea>
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs hover:bg-primary-container shadow-2xs flex items-center justify-center gap-2 cursor-pointer active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-[17px]">send</span>
                        <span>إرسال التقييم</span>
                    </button>
                </form>
            </div>
        @endif
    @endif
</div>

<script>
window.customBackHandler = function() {
    window.location.href = "{{ route('account.orders') }}";
};
</script>
@endsection
