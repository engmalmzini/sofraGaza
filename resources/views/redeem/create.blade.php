@extends('layouts.public')

@section('title', 'استبدال النقاط')

@section('content')
@php
    $selectedRestaurant = $restaurants->firstWhere('id', $selected);
    $selectedItem = $items->firstWhere('id', $selectedItemId);
    $selectedCost = $selectedItem->redeem_cost ?? 0;
    $canSubmit = $selectedItem && $balance >= $selectedCost;
@endphp
<div class="sg-redeem">
    <header class="sg-redeem__hero">
        <div>
            <p class="sg-redeem__kicker">
                <span class="material-symbols-outlined">stars</span>
                برنامج الولاء
            </p>
            <h1>استبدال النقاط</h1>
            <p>اختر مطعماً ثم طبقاً، وأكمل عنوان التوصيل. الاستبدال مجاني بالكامل ويُخصم من رصيدك بعد إرسال الطلب.</p>
        </div>
        <div class="sg-redeem__balance">
            <span>رصيدك الحالي</span>
            <strong>{{ number_format($balance) }}</strong>
            <em>نقطة</em>
            <a href="{{ route('account.points') }}">سجل النقاط</a>
        </div>
    </header>

    <div class="sg-redeem__rates">
        <article>
            <span class="material-symbols-outlined">stars</span>
            <div>
                <strong>تجميع النقاط</strong>
                <em>كل {{ rtrim(rtrim(number_format($earnRate, 1), '0'), '.') }} شيكل = نقطة</em>
            </div>
        </article>
        <article>
            <span class="material-symbols-outlined">restaurant</span>
            <div>
                <strong>تكلفة الاستبدال</strong>
                <em>سعر الطبق نفسه بالنقاط</em>
            </div>
        </article>
        <article>
            <span class="material-symbols-outlined">delivery_dining</span>
            <div>
                <strong>التوصيل</strong>
                <em>مجاني مع الاستبدال</em>
            </div>
        </article>
    </div>

    @if($restaurants->isEmpty())
        <div class="sg-redeem__empty">
            <span class="material-symbols-outlined">storefront</span>
            <strong>لا مطاعم متاحة للاستبدال حالياً</strong>
            <p>سيظهر الاستبدال هنا بعد اعتماد المطاعم من الإدارة.</p>
        </div>
    @else
        <form method="GET" action="{{ route('redeem.create') }}" class="sg-redeem__venue">
            <label>
                <span>المطعم أو الكافي</span>
                <select name="restaurant_id" onchange="this.form.submit()">
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" @selected($selected == $restaurant->id)>{{ $restaurant->name }}</option>
                    @endforeach
                </select>
            </label>
            @if($selectedRestaurant)
                <p>{{ $selectedRestaurant->areaLabel() }}@if($selectedRestaurant->cuisine) · {{ $selectedRestaurant->cuisineLabel() }}@endif</p>
            @endif
        </form>

        <form method="POST" action="{{ route('redeem.store') }}" class="sg-redeem__layout" data-redeem-form data-balance="{{ $balance }}">
            @csrf
            <section class="sg-redeem__main">
                <div class="sg-redeem__panel">
                    <div class="sg-redeem__panel-head">
                        <h2>اختر الصنف</h2>
                        <span>{{ $items->count() }} طبق متاح</span>
                    </div>
                    @forelse($items as $item)
                        @php $affordable = $balance >= $item->redeem_cost; @endphp
                        <label class="sg-redeem__item {{ $affordable ? '' : 'is-locked' }} {{ (int) $selectedItemId === (int) $item->id ? 'is-selected' : '' }}">
                            <input
                                type="radio"
                                name="menu_item_id"
                                value="{{ $item->id }}"
                                data-name="{{ $item->name }}"
                                data-cost="{{ $item->redeem_cost }}"
                                data-category="{{ $item->category }}"
                                @checked((int) $selectedItemId === (int) $item->id)
                                @disabled(! $affordable)
                            >
                            <span class="sg-redeem__thumb">
                                @if($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="">
                                @else
                                    <span class="material-symbols-outlined">{{ $item->category === 'مشروبات' ? 'local_cafe' : 'ramen_dining' }}</span>
                                @endif
                            </span>
                            <span class="sg-redeem__copy">
                                <strong>{{ $item->name }}</strong>
                                <em>{{ $item->category }}{{ $item->description ? ' · '.$item->description : '' }}</em>
                            </span>
                            <span class="sg-redeem__cost">
                                <b>{{ $item->redeem_cost }}</b>
                                <small>نقطة</small>
                                @unless($affordable)
                                    <i>رصيدك لا يكفي</i>
                                @endunless
                            </span>
                        </label>
                    @empty
                        <div class="sg-redeem__empty sg-redeem__empty--soft">
                            <span class="material-symbols-outlined">restaurant_menu</span>
                            <strong>لا أصناف متاحة في هذا المطعم</strong>
                            <p>جرّب مطعماً آخر من القائمة أعلاه.</p>
                        </div>
                    @endforelse
                </div>

                <div class="sg-redeem__panel">
                    <div class="sg-redeem__panel-head">
                        <h2>التوصيل</h2>
                    </div>
                    @if($addresses->isNotEmpty())
                        <div class="sg-redeem__addresses">
                            <p>عناوين محفوظة</p>
                            @foreach($addresses as $address)
                                <button type="button" class="sg-redeem__address" data-redeem-address data-details="{{ $address->details }}" data-phone="{{ $address->phone }}">
                                    <strong>{{ $address->label }}</strong>
                                    <span>{{ $address->details }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <label class="sg-redeem__field">
                        <span>عنوان التوصيل</span>
                        <textarea id="redeem-address" name="address_details" rows="3" required minlength="10" placeholder="الحي، الشارع، أقرب معلم، رقم البناية">{{ old('address_details') }}</textarea>
                    </label>
                    <label class="sg-redeem__field">
                        <span>هاتف التواصل</span>
                        <input id="redeem-phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required inputmode="numeric" placeholder="059XXXXXXXX">
                    </label>
                    <label class="sg-redeem__field">
                        <span>ملاحظة للمطعم (اختياري)</span>
                        <input name="notes" value="{{ old('notes') }}" maxlength="500" placeholder="مثال: بدون بصل، اترك الطلب عند الباب">
                    </label>
                </div>
            </section>

            <aside class="sg-redeem__summary">
                <h2>ملخص الاستبدال</h2>
                <p data-redeem-restaurant>{{ $selectedRestaurant->name ?? 'اختر مطعماً' }}</p>
                <div class="sg-redeem__summary-item">
                    <span>الصنف</span>
                    <strong data-redeem-name>{{ $selectedItem->name ?? 'لم يُحدد بعد' }}</strong>
                </div>
                <div class="sg-redeem__summary-item">
                    <span>التكلفة</span>
                    <strong><b data-redeem-cost>{{ $selectedCost }}</b> نقطة</strong>
                </div>
                <div class="sg-redeem__summary-item">
                    <span>المتبقي بعد الاستبدال</span>
                    <strong><b data-redeem-left>{{ max(0, $balance - $selectedCost) }}</b> نقطة</strong>
                </div>
                <p class="sg-redeem__hint">الطلب يصل للمطعم بانتظار التأكيد، بدون أي تحويل مالي.</p>
                <button type="submit" class="sg-redeem__submit" data-redeem-submit @disabled(! $canSubmit)>
                    تأكيد الاستبدال
                </button>
                <small data-redeem-warning @if($canSubmit) hidden @endif>أضف نقاطاً أو اختر صنفاً يناسب رصيدك.</small>
            </aside>
        </form>
    @endif
</div>
@endsection
