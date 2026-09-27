@extends('layouts.public')

@section('title', 'تفاصيل الطلب')

@section('content')
<div class="mx-auto max-w-3xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-on-surface-variant mb-4">
        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        كل الطلبات
    </a>
    <h1 class="font-headline-md text-2xl font-bold text-stone-900">طلب #{{ $order->id }}</h1>
    <p class="mt-1 text-on-surface-variant">{{ $order->restaurant->name }} — {{ $order->statusLabel() }}</p>
    <div class="mt-6">
        @include('partials.order-invoice', ['order' => $order, 'tone' => 'public'])
    </div>
    <div class="mt-4 rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs text-sm leading-7 text-on-surface-variant">
        <div>الهاتف: {{ $order->phone }}</div>
        @if($order->delivery_area)
            <div>منطقة التوصيل: <strong class="text-on-surface">{{ $order->deliveryAreaLabel() }}</strong></div>
        @endif
        <div>العنوان: {{ $order->address_details }}</div>
        @if($order->rejection_reason)
            <div class="text-primary font-medium">سبب الرفض: {{ $order->rejection_reason }}</div>
        @endif
        @if($order->canCancel())
            <form method="POST" action="{{ route('account.orders.cancel', $order) }}" class="mt-6" onsubmit="return confirm('إلغاء الطلب؟')">
                @csrf
                <button class="text-sm font-bold text-primary">إلغاء الطلب</button>
            </form>
        @endif
    </div>

    @if($order->status === 'delivered')
        @if($order->review)
            <div class="mt-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[22px]">verified</span>
                        <span class="font-bold text-sm text-emerald-950">تقييمك لهذا الطلب</span>
                    </div>
                    <div class="flex items-center text-amber-500">
                        @for($s = 1; $s <= 5; $s++)
                            <span class="material-symbols-outlined text-[18px] {{ $s <= $order->review->rating ? 'fill-1' : 'text-slate-300' }}">star</span>
                        @endfor
                    </div>
                </div>
                @if($order->review->comment)
                    <p class="text-xs text-slate-700 bg-white/90 rounded-xl p-3 border border-emerald-100 mt-2">{{ $order->review->comment }}</p>
                @endif
                <div class="mt-2 text-[11px] text-emerald-800/80 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                    <span>تم توثيق تقييمك كطلب مؤكد وحقيقي لهذا المطعم. شكراً لمشاركتك!</span>
                </div>
            </div>
        @else
            <div class="mt-4 rounded-2xl bg-gradient-to-br from-amber-50/70 to-surface-container-lowest border border-amber-200/80 p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-amber-500 text-[24px] fill-1">rate_review</span>
                    <h2 class="font-bold text-base text-stone-900">شاركنا رأيك في طلبك من {{ $order->restaurant->name }}</h2>
                </div>
                <p class="text-xs text-on-surface-variant mb-4">تقييمك يظهر للزبائن ويساعد في تحسين جودة الطعام والتوصيل.</p>

                <form method="POST" action="{{ route('restaurants.reviews.store', $order->restaurant) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-1.5">التقييم بالنجوم:</label>
                        <div class="flex items-center gap-2 flex-wrap">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer group flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:border-amber-400 hover:bg-amber-50/40 transition-all">
                                    <input type="radio" name="rating" value="{{ $i }}" class="accent-amber-500" required {{ $i === 5 ? 'checked' : '' }}>
                                    <span class="material-symbols-outlined text-[18px] text-amber-500 fill-1">star</span>
                                    <span class="text-xs font-bold text-slate-700">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-1.5">تعليقك (اختياري):</label>
                        <textarea name="comment" rows="3" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs text-on-surface focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" placeholder="اكتب ملاحظاتك عن جودة الوجبة وسرعة التوصيل..."></textarea>
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs hover:bg-primary-container shadow-xs flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">send</span>
                        <span>إرسال التقييم</span>
                    </button>
                </form>
            </div>
        @endif
    @endif
</div>
@endsection
