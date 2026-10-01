@extends('layouts.public')

@section('title', 'طلب جماعي')
@section('hideFloatingCart', true)

@section('content')
<div class="mx-auto max-w-lg px-3 sm:px-4 py-5 sm:py-8 pb-28">
    <div class="mb-5">
        <p class="text-xs font-extrabold text-primary mb-1">طلب من نفس المطعم</p>
        <h1 class="text-2xl font-black text-stone-900 tracking-tight">طلب جماعي</h1>
        <p class="mt-2 text-sm text-stone-500 leading-relaxed">
            اطلبوا من {{ $restaurant->name }} بفاتورة واحدة وتوصيل واحد، وكل واحد يدفع نصيبه لحاله.
        </p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs mb-4 flex items-center gap-3">
        @if($restaurant->logo_url ?? false)
            <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}" class="w-12 h-12 rounded-2xl object-cover">
        @else
            <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary font-black flex items-center justify-center">
                {{ mb_substr($restaurant->name, 0, 1) }}
            </div>
        @endif
        <div>
            <p class="text-[11px] text-stone-400 font-bold">المطعم</p>
            <h2 class="font-extrabold text-stone-900">{{ $restaurant->name }}</h2>
        </div>
    </div>

    <form method="POST" action="{{ route('group-orders.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">

        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs">
            <div class="flex items-center justify-between gap-2 mb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-stone-900">مين بدو يطلب معك؟</h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">اكتب أرقام جوالهم واحد واحد. لازم يكون عندهم حساب زبون.</p>
                </div>
            </div>

            <div id="group-phone-list" class="space-y-2">
                @php $oldPhones = old('phones', ['']); @endphp
                @foreach($oldPhones as $index => $phone)
                    <div class="flex items-center gap-2" data-phone-row>
                        <input type="tel"
                               name="phones[]"
                               value="{{ $phone }}"
                               inputmode="numeric"
                               dir="ltr"
                               placeholder="05xxxxxxxx"
                               pattern="{{ \App\Support\PalestinianPhone::CUSTOMER_HTML_PATTERN }}"
                               class="flex-1 h-12 rounded-xl bg-stone-50 border border-slate-200 px-3 text-sm font-mono outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <button type="button" data-remove-phone class="w-10 h-12 rounded-xl bg-stone-100 text-stone-500 hover:bg-rose-50 hover:text-rose-600 {{ $index === 0 ? 'invisible' : '' }}">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-group-phone"
                    class="mt-3 w-full h-11 rounded-xl border border-dashed border-slate-300 text-stone-600 text-xs font-extrabold hover:border-primary hover:text-primary flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>إضافة رقم ثاني</span>
            </button>
            @error('phones')
                <p class="mt-2 text-xs text-rose-600 font-bold">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full h-14 rounded-2xl bg-primary text-white font-extrabold text-base shadow-[0_10px_22px_rgba(163,57,0,0.28)]">
            تم — أرسل الدعوة
        </button>
        <p class="text-[11px] text-stone-400 text-center leading-relaxed">
            رح يوصل إشعار لكل واحد تدعوه عشان يطلب اللي بدو إياه ويدفع نصيبه.
        </p>
    </form>
</div>

<script>
(function () {
    const list = document.getElementById('group-phone-list');
    const addBtn = document.getElementById('add-group-phone');
    if (!list || !addBtn) return;

    addBtn.addEventListener('click', function () {
        if (list.querySelectorAll('[data-phone-row]').length >= 8) return;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.setAttribute('data-phone-row', '');
        row.innerHTML = `
            <input type="tel" name="phones[]" inputmode="numeric" dir="ltr" placeholder="05xxxxxxxx" pattern="{{ \App\Support\PalestinianPhone::CUSTOMER_HTML_PATTERN }}" class="flex-1 h-12 rounded-xl bg-stone-50 border border-slate-200 px-3 text-sm font-mono outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
            <button type="button" data-remove-phone class="w-10 h-12 rounded-xl bg-stone-100 text-stone-500 hover:bg-rose-50 hover:text-rose-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>`;
        list.appendChild(row);
    });

    list.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-remove-phone]');
        if (!btn) return;
        const row = btn.closest('[data-phone-row]');
        if (row && list.querySelectorAll('[data-phone-row]').length > 1) {
            row.remove();
        }
    });
})();
</script>
@endsection
