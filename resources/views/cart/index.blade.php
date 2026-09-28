@extends('layouts.public')

@section('title', 'السلة')
@section('hideFloatingCart', true)
@section('hideWhatsApp', true)

@section('content')
<div data-cart-page class="mx-auto max-w-2xl px-3 sm:px-4 py-3 sm:py-6 {{ empty($quote['lines']) ? 'min-h-[calc(100dvh-140px)] lg:min-h-[60vh] flex flex-col justify-center pb-6' : 'pb-36' }}">

    {{-- Empty Cart State (Centered in page) --}}
    <div id="cart-empty-view" class="{{ empty($quote['lines']) ? '' : 'hidden' }} w-full max-w-md mx-auto rounded-3xl bg-white border border-slate-100/90 p-8 text-center shadow-xs space-y-4">
        <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center mx-auto">
            <span class="material-symbols-outlined text-[36px]">shopping_bag</span>
        </div>
        <h2 class="text-base font-extrabold text-stone-900">سلتك فارغة حالياً</h2>
        <p class="text-xs text-stone-500 leading-relaxed">تصفح وجبات ومطاعم غزة اللذيذة وأضف ما تشتهي إلى سلتك.</p>
        <div class="pt-1">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-primary hover:bg-primary-container text-white font-extrabold text-xs px-6 py-3 shadow-xs transition-all active:scale-95">
                <span>استكشف المطاعم</span>
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            </a>
        </div>
    </div>

    @if(!empty($quote['restaurant']))
    <div id="cart-items-view" class="{{ empty($quote['lines']) ? 'hidden' : '' }} space-y-3.5">
        {{-- Card 1: Restaurant and Items Card --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-100/80 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-4">
            {{-- Restaurant Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5 min-w-0">
                    @if($quote['restaurant']->logo_url ?? false)
                        <img src="{{ $quote['restaurant']->logo_url }}" alt="{{ $quote['restaurant']->name }}" class="w-11 h-11 rounded-full object-cover border border-primary/20 shrink-0">
                    @else
                        <div class="w-11 h-11 rounded-full bg-primary/10 text-primary font-extrabold text-sm flex items-center justify-center shrink-0 border border-primary/20">
                            {{ mb_substr($quote['restaurant']->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <span class="text-[11px] text-stone-400 block font-medium">من</span>
                        <h3 class="font-extrabold text-sm text-stone-900 leading-tight truncate">{{ $quote['restaurant']->name }}</h3>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('restaurants.show', $quote['restaurant']->slug ?? $quote['restaurant']->id) }}" class="text-xs font-extrabold text-primary hover:text-primary-container flex items-center gap-0.5">
                        <span>+ إضافة</span>
                    </a>
                </div>
            </div>

            {{-- Items List --}}
            <div class="divide-y divide-slate-100 space-y-3" id="cart-items-list">
                @foreach($quote['lines'] as $line)
                    <div class="pt-3 first:pt-0 space-y-2" data-cart-line="{{ $line['item']->id }}">
                        <div class="flex items-center justify-between gap-3">
                            {{-- Item Details --}}
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                @php
                                    $dishImg = $line['item']->image_path 
                                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($line['item']->image_path)
                                        : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=120&h=120&q=80';
                                @endphp
                                <img src="{{ $dishImg }}" alt="{{ $line['item']->name }}" class="w-14 h-14 rounded-xl object-cover border border-slate-100 shrink-0">
                                
                                <div class="min-w-0">
                                    <h4 class="font-extrabold text-sm text-stone-900 leading-tight truncate">{{ $line['item']->name }}</h4>
                                    <p class="font-mono text-xs font-bold text-primary mt-1" data-line-total>
                                        {{ number_format($line['line_total'], 2) }} ₪
                                    </p>
                                </div>
                            </div>

                            {{-- Quantity Stepper --}}
                            <div class="flex items-center gap-1.5 shrink-0 bg-stone-50 border border-slate-200/80 rounded-xl p-1">
                                {{-- Increase Quantity Form --}}
                                <form method="POST" action="{{ route('cart.update') }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="item_id" value="{{ $line['item']->id }}">
                                    <input type="hidden" name="quantity" value="{{ $line['qty'] + 1 }}" data-cart-qty-plus>
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-primary hover:bg-primary-container text-white flex items-center justify-center font-bold text-base transition-colors cursor-pointer" title="زيادة">
                                        +
                                    </button>
                                </form>

                                <span class="w-6 text-center font-mono font-extrabold text-xs text-stone-900" data-cart-qty>{{ $line['qty'] }}</span>

                                {{-- Decrease or Remove Quantity Form --}}
                                <form method="POST" action="{{ route('cart.update') }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="item_id" value="{{ $line['item']->id }}">
                                    <input type="hidden" name="quantity" value="{{ max(0, $line['qty'] - 1) }}" data-cart-qty-minus>
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-stone-200/80 hover:bg-stone-300 text-stone-700 flex items-center justify-center transition-colors cursor-pointer" title="إنقاص">
                                        <span class="text-base font-bold leading-none">−</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if(!empty($line['notes']))
                            <p class="text-[11px] text-stone-700 bg-stone-100 px-2 py-0.5 rounded-lg inline-block font-medium">
                                ملاحظة: {{ $line['notes'] }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Restaurant Notes Field --}}
            <div class="pt-2 border-t border-slate-100">
                <label class="block text-xs font-bold text-stone-800 mb-1.5">
                    ملاحظة لهذا المطعم (اختياري)
                </label>
                <form method="POST" action="{{ route('cart.update') }}" onsubmit="event.preventDefault(); submitRestaurantNote(this);">
                    <input type="text" 
                           id="cart-restaurant-notes"
                           name="notes" 
                           value="{{ $quote['lines'][0]['notes'] ?? '' }}" 
                           onblur="saveCartNotes(this.value)"
                           placeholder="مثلاً: بدون بصل، الصلصة على جنب" 
                           class="w-full h-12 rounded-xl bg-stone-50 border border-slate-200/80 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 px-3.5 text-xs text-stone-900 placeholder:text-stone-400 outline-none transition-all">
                </form>
            </div>

            {{-- Subtotal for this restaurant --}}
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-stone-500">
                <span>مجموع هذا المطعم:</span>
                <span class="font-mono text-stone-800 text-sm font-extrabold" data-cart-subtotal>{{ number_format($quote['subtotal'], 2) }} ₪</span>
            </div>
        </div>

        {{-- Card 2: Total Orders Summary Card (مجموع الطلبات) --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-100/80 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-1.5">
            <div class="flex items-center justify-between">
                <h3 class="font-extrabold text-sm text-stone-900">مجموع الطلبات</h3>
                <span class="font-mono text-base font-black text-primary" data-cart-grand-total>{{ number_format($quote['subtotal'], 2) }} ₪</span>
            </div>
            <p class="text-[11px] text-stone-400 leading-relaxed">
                رسوم التوصيل - إن وجدت - تُحدد في الخطوة التالية حسب منطقة التوصيل.
            </p>
        </div>
    </div>

    {{-- Floating Bottom Action Bar fixed above bottom nav bar (No arrow, dark orange) --}}
    <div id="cart-floating-checkout" class="{{ empty($quote['lines']) ? 'hidden' : '' }} fixed bottom-[74px] inset-x-0 px-3 sm:px-4 z-40 max-w-2xl mx-auto pointer-events-none">
        <a href="{{ route('checkout.create') }}" 
           class="pointer-events-auto w-full h-14 rounded-2xl bg-primary hover:bg-primary-container active:scale-[0.99] text-white font-extrabold text-base flex items-center justify-between px-5 shadow-[0_8px_24px_rgba(163,57,0,0.35)] transition-all cursor-pointer">
            <span>متابعة للدفع</span>
            <span class="font-mono text-sm bg-black/20 px-3 py-1 rounded-xl" data-cart-grand-total>{{ number_format($quote['subtotal'], 2) }} ₪</span>
        </a>
    </div>
    @endif

</div>

<script>
function saveCartNotes(notes) {
    const firstLine = document.querySelector('[data-cart-line]');
    if (firstLine) {
        const itemId = firstLine.dataset.cartLine;
        const qtySpan = firstLine.querySelector('[data-cart-qty]');
        const qty = qtySpan ? parseInt(qtySpan.textContent) || 1 : 1;
        fetch('{{ route("cart.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                _method: 'PATCH',
                item_id: itemId,
                quantity: qty,
                notes: notes
            })
        });
    }
}

function submitRestaurantNote(form) {
    const input = form.querySelector('input[name="notes"]');
    if (input) {
        saveCartNotes(input.value);
    }
}
</script>
@endsection
