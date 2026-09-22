@extends('layouts.public')

@section('title', $restaurant->name)
@section('body_class', 'page-restaurant-show')
@section('hideFloatingCart')
@endsection

@section('content')
    @include('restaurants.partials.show-mobile')
    @include('restaurants.partials.show-desktop')

    <div class="lg:hidden fixed inset-0 z-[60] bg-on-surface/50 backdrop-blur-sm flex-col justify-end" id="cartDrawer" hidden>
        <div class="bg-surface-container-lowest rounded-t-3xl p-6 flex flex-col max-h-[80vh] overflow-y-auto shadow-2xl">
            <div class="flex items-center justify-between pb-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="material-symbols-outlined text-primary text-[24px]">shopping_cart</span>
                    <h3 class="font-headline-sm text-[20px] text-on-surface font-bold truncate">سلة طلباتك ({{ $restaurant->name }})</h3>
                </div>
                <button type="button" class="js-close-cart w-8 h-8 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant" aria-label="إغلاق">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <div data-drawer-lines class="flex flex-col gap-2 mt-3">
                @forelse($restaurantCart['lines'] ?? [] as $line)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-surface-container-low" data-line-id="{{ $line['item']->id }}">
                        <div class="flex-1 min-w-0 pl-2">
                            <h4 class="font-label-md text-[13px] text-on-surface font-bold truncate">{{ $line['item']->name }}</h4>
                            <span class="font-label-md text-[13px] text-primary font-bold">{{ number_format($line['line_total'], 0) }} <span class="ils">₪</span></span>
                        </div>
                        <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2 bg-surface-container-lowest rounded-full p-1 shadow-xs">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="item_id" value="{{ $line['item']->id }}">
                            <button type="submit" name="quantity" value="{{ $line['qty'] - 1 }}" class="w-6 h-6 rounded-full flex items-center justify-center text-on-surface" aria-label="إنقاص">
                                <span class="material-symbols-outlined text-[16px]">remove</span>
                            </button>
                            <span class="font-label-md text-[13px] font-bold px-1">{{ $line['qty'] }}</span>
                            <button type="submit" name="quantity" value="{{ $line['qty'] + 1 }}" class="w-6 h-6 rounded-full flex items-center justify-center text-primary" aria-label="زيادة">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <p data-cart-empty class="text-sm text-on-surface-variant text-center py-6">سلتك فارغة. أضف طبقاً للبدء.</p>
                @endforelse
            </div>
            <div data-drawer-summary class="{{ $restaurantCart ? '' : 'hidden' }}">
                <div class="bg-surface-container-low rounded-xl p-3 mt-4 flex flex-col gap-1.5 font-label-md text-[13px]">
                    <div class="flex justify-between text-on-surface-variant">
                        <span>المجموع الفرعي</span>
                        <span data-cart-subtotal>{{ number_format($restaurantCart['subtotal'] ?? 0, 1) }} <span class="ils">₪</span></span>
                    </div>
                    <div data-cart-discount-row class="flex justify-between text-secondary font-medium {{ ($restaurantCart['discount_percent'] ?? 0) ? '' : 'hidden' }}">
                        <span>خصم خاص <span data-cart-discount-percent>{{ $restaurantCart['discount_percent'] ?? 0 }}</span>% (VIP)</span>
                        <span data-cart-discount-amount>- {{ number_format($restaurantCart['discount_amount'] ?? 0, 1) }} <span class="ils">₪</span></span>
                    </div>
                    <div class="h-px bg-outline/30 my-1"></div>
                    <div class="flex justify-between text-on-surface font-headline-sm text-[20px] font-bold">
                        <span>الإجمالي النهائي</span>
                        <span data-cart-grand-total class="text-primary">{{ number_format($restaurantCart['items_total'] ?? $restaurantCart['subtotal'] ?? 0, 1) }} <span class="ils">₪</span></span>
                    </div>
                </div>
                <a href="{{ auth()->check() ? route('checkout.create') : route('login') }}" class="mt-4 w-full bg-primary text-on-primary py-3.5 rounded-full font-label-lg text-[15px] font-bold flex items-center justify-center gap-2 shadow-lg">
                    <span>تأكيد الطلب والدفع الفوري</span>
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </a>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-[70] bg-on-surface/40 backdrop-blur-sm items-end lg:items-center justify-center p-0 lg:p-4" id="customizeModal" hidden>
        <div class="bg-surface-container-lowest rounded-t-3xl lg:rounded-2xl w-full max-w-lg shadow-xl overflow-hidden">
            <div class="p-4 bg-surface-container-low flex items-start justify-between">
                <div class="flex flex-col min-w-0 pl-3">
                    <h3 class="font-headline-sm text-[20px] font-bold text-on-surface" id="modalDishTitle">تخصيص الوجبة</h3>
                    <p class="font-body-sm text-[14px] text-on-surface-variant pt-1" id="modalDishDesc">اختر الكمية والإضافات المفضلة لوجبتك</p>
                </div>
                <button type="button" class="js-close-customizer text-on-surface-variant hover:text-on-surface p-1" aria-label="إغلاق">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="customize-form" method="POST" action="#" class="flex flex-col" data-ajax-cart>
                @csrf
                <input type="hidden" name="quantity" id="customize-qty" value="1">
                <div class="p-4 flex flex-col gap-4 max-h-[70vh] overflow-y-auto no-scrollbar">
                    <div class="flex items-center justify-between bg-surface-container rounded-xl p-3">
                        <span class="font-label-lg text-[15px] text-on-surface font-semibold">الكمية المطلوبة</span>
                        <div class="flex items-center gap-3">
                            <button type="button" class="js-qty-minus w-8 h-8 rounded-full bg-surface-container-lowest text-on-surface flex items-center justify-center font-bold text-lg">-</button>
                            <span class="font-headline-sm text-[20px] font-bold text-primary px-2" id="modalQuantity">1</span>
                            <button type="button" class="js-qty-plus w-8 h-8 rounded-full bg-surface-container-lowest text-on-surface flex items-center justify-center font-bold text-lg">+</button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="font-label-lg text-[15px] text-on-surface font-semibold">إضافات محببة (اختياري)</span>
                        <label class="flex items-center justify-between p-2 rounded-lg hover:bg-surface-container-low cursor-pointer">
                            <div class="flex items-center gap-2">
                                <input class="addon-check w-4 h-4 rounded accent-primary" data-price="0" type="checkbox">
                                <span class="font-body-sm text-[14px] text-on-surface">خبز صاج وطابون طازج إضافي</span>
                            </div>
                            <span class="font-label-sm text-[11px] text-secondary font-bold">مجاناً</span>
                        </label>
                        <label class="flex items-center justify-between p-2 rounded-lg hover:bg-surface-container-low cursor-pointer">
                            <div class="flex items-center gap-2">
                                <input class="addon-check w-4 h-4 rounded accent-primary" data-price="0" type="checkbox">
                                <span class="font-body-sm text-[14px] text-on-surface">صلصة مثومة غنية وزيت زيتون</span>
                            </div>
                            <span class="font-label-sm text-[11px] text-secondary font-bold">مجاناً</span>
                        </label>
                        <label class="flex items-center justify-between p-2 rounded-lg hover:bg-surface-container-low cursor-pointer">
                            <div class="flex items-center gap-2">
                                <input class="addon-check w-4 h-4 rounded accent-primary" data-price="0" type="checkbox">
                                <span class="font-body-sm text-[14px] text-on-surface">سلطة دقّة غزاوية حارة بالفلفل الأخضر</span>
                            </div>
                            <span class="font-label-sm text-[11px] text-secondary font-bold">مجاناً</span>
                        </label>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-label-lg text-[15px] text-on-surface font-semibold" for="orderNotes">ملاحظات التحضير الخاصة</label>
                        <textarea class="w-full rounded-xl bg-surface-container-low p-3 border-none outline-none font-body-sm text-[14px] text-on-surface placeholder:text-on-surface-variant" id="orderNotes" name="notes" placeholder="مثال: بدون بصل، خبز محمص زيادة، فصل الصلصة عن اللحم..." rows="2"></textarea>
                    </div>
                </div>
                <div class="p-4 bg-surface-container-low flex items-center justify-between gap-4">
                    <div class="flex flex-col">
                        <span class="font-label-sm text-[11px] text-on-surface-variant">السعر المحسوب</span>
                        <span class="font-headline-sm text-[20px] font-bold text-primary" id="modalTotalCalculated">0 <span class="ils">₪</span></span>
                    </div>
                    <button type="submit" class="px-6 py-2 rounded-full bg-primary text-on-primary font-label-lg text-[15px] font-bold shadow-md flex items-center gap-1">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        <span>إضافة للسلة الآن</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
