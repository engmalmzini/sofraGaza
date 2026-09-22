@extends('layouts.public')

@section('title', 'السلة')

@section('content')
<div class="mx-auto max-w-4xl px-margin lg:px-margin-desktop py-5 lg:py-10" data-cart-page>
    <h1 class="font-headline-md text-2xl font-bold text-stone-900">سلّة الطلب</h1>
    @if(empty($quote['lines']))
        <div class="mt-6 rounded-2xl bg-surface-container-lowest border border-slate-100 p-8 text-center">
            <span class="material-symbols-outlined text-primary text-[40px]">shopping_bag</span>
            <p class="mt-3 text-on-surface-variant">السلة فارغة حالياً.</p>
            <a class="mt-4 inline-flex items-center gap-1 rounded-full bg-primary text-on-primary px-5 py-2.5 text-sm font-semibold" href="{{ route('restaurants.index') }}">تصفح المطاعم</a>
        </div>
    @else
        <p class="mt-2 text-sm text-on-surface-variant">من {{ $quote['restaurant']->name }}</p>
        <div class="mt-5 space-y-3">
            @foreach($quote['lines'] as $line)
                <div class="flex items-center justify-between gap-3 rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs" data-cart-line="{{ $line['item']->id }}">
                    <div class="min-w-0">
                        <div class="font-bold text-on-surface truncate">{{ $line['item']->name }}</div>
                        <div class="text-sm text-on-surface-variant">{{ number_format($line['item']->price, 2) }} <span class="ils">₪</span></div>
                    </div>
                    <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="item_id" value="{{ $line['item']->id }}">
                        <input type="number" name="quantity" min="0" max="20" value="{{ $line['qty'] }}" class="w-16 h-10 rounded-xl bg-surface-container-low border-none px-2 text-center">
                        <button class="text-sm font-bold text-primary">تحديث</button>
                    </form>
                    <div class="font-bold text-on-surface shrink-0" data-line-total>{{ number_format($line['line_total'], 2) }} <span class="ils">₪</span></div>
                </div>
            @endforeach
        </div>
        <aside class="mt-6 rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs">
            <div class="flex justify-between text-sm text-on-surface-variant"><span>المجموع</span><span data-cart-subtotal>{{ number_format($quote['subtotal'], 2) }} <span class="ils">₪</span></span></div>
            @if($quote['discount_percent'])
                <div class="mt-2 flex justify-between text-sm text-secondary font-semibold"><span>خصم العضوية {{ $quote['discount_percent'] }}%</span><span data-cart-discount-amount>- {{ number_format($quote['discount_amount'], 2) }} <span class="ils">₪</span></span></div>
            @endif
            <div class="mt-2 flex justify-between text-sm text-on-surface-variant"><span>التوصيل</span><span>@if($quote['delivery_fee']){{ number_format($quote['delivery_fee'], 2) }} <span class="ils">₪</span>@else مجاني @endif</span></div>
            <div class="mt-4 flex justify-between text-lg font-bold text-on-surface"><span>النهائي</span><span data-cart-grand-total>{{ number_format($quote['total'], 2) }} <span class="ils">₪</span></span></div>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('checkout.create') }}" class="inline-flex items-center gap-1 rounded-full bg-primary hover:bg-primary-container text-on-primary px-5 py-3 font-semibold">
                    إتمام الطلب
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <form method="POST" action="{{ route('cart.clear') }}">@csrf @method('DELETE')<button class="py-3 text-sm text-on-surface-variant">إفراغ السلة</button></form>
            </div>
        </aside>
    @endif
</div>
@endsection
