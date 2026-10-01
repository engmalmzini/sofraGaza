@php
    $vip = (bool) ($membership?->discount_percent);
    $itemCount = $menuSections->sum(fn ($section) => $section['items']->count());
    $areaShort = $restaurant->areaLabel();
    if (str_contains($areaShort, '•')) {
        $areaShort = trim(explode('•', $areaShort)[1]);
    }
    $deliveryFee = isset($deliveryArea['key'])
        ? \App\Models\Setting::deliveryFeeForArea($deliveryArea['key'])
        : \App\Models\Setting::deliveryFeeForArea();
    $hours = null;
    if ($restaurant->opens_at && $restaurant->closes_at) {
        $hours = substr((string) $restaurant->opens_at, 0, 5).' – '.substr((string) $restaurant->closes_at, 0, 5);
    }
@endphp

<style>
    @media (min-width: 1024px) {
        body.page-restaurant-show,
        body.page-restaurant-show .site-main {
            background: #FAF6F0 !important;
        }
    }

    .restaurant-desktop {
        color: #1a130f;
    }
    .rd-wrap {
        max-width: 80rem;
        margin: 0 auto;
        padding: 1.25rem 1.5rem 3rem;
    }
    .rd-hero {
        display: grid;
        grid-template-columns: minmax(320px, 0.95fr) minmax(0, 1.15fr);
        gap: 1.25rem;
        margin-bottom: 1.25rem;
        align-items: stretch;
    }
    .rd-hero__identity {
        background: #fff;
        border-radius: 28px;
        padding: 1.6rem 1.7rem 1.45rem;
        box-shadow: 0 8px 28px rgba(80, 40, 10, 0.06);
        display: flex;
        flex-direction: column;
        min-height: 380px;
    }
    .rd-crumb {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.76rem;
        font-weight: 700;
        color: #9a8b80;
        margin-bottom: 0.85rem;
    }
    .rd-crumb a { color: #9a8b80; text-decoration: none; }
    .rd-crumb a:hover { color: #a33900; }
    .rd-crumb .is-current { color: #1a130f; }
    .rd-hero__kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: #c84500;
        margin-bottom: 0.45rem;
    }
    .rd-hero h1 {
        margin: 0;
        font-size: 2.15rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        line-height: 1.15;
    }
    .rd-hero__status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-inline-start: 0.65rem;
        padding: 0.22rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 800;
        vertical-align: middle;
    }
    .rd-hero__status.is-open { background: #ecfdf3; color: #067647; }
    .rd-hero__status.is-closed { background: #fef2f2; color: #b42318; }
    .rd-hero__status i {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }
    .rd-hero__lead {
        margin: 0.55rem 0 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: #8a7a6e;
        line-height: 1.55;
        max-width: 36rem;
    }
    .rd-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem;
        margin-top: 1.15rem;
    }
    .rd-stat {
        background: #FAF6F0;
        border-radius: 16px;
        padding: 0.7rem 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }
    .rd-stat .material-symbols-outlined {
        font-size: 20px;
        color: #c84500;
    }
    .rd-stat b {
        display: block;
        font-size: 0.82rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .rd-stat span {
        display: block;
        font-size: 0.68rem;
        font-weight: 600;
        color: #9a8b80;
        margin-top: 0.1rem;
    }
    .rd-hero__vip {
        margin-top: auto;
        padding-top: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        background: linear-gradient(105deg, #fff7ed, #faeee4);
        border-radius: 16px;
        padding: 0.85rem 1rem;
    }
    .rd-hero__vip p {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 800;
        color: #9a3412;
    }
    .rd-hero__vip a,
    .rd-hero__vip em {
        display: inline-flex;
        align-items: center;
        background: #c84500;
        color: #fff;
        border-radius: 9999px;
        padding: 0.4rem 0.85rem;
        font-size: 0.72rem;
        font-weight: 800;
        text-decoration: none;
        font-style: normal;
        white-space: nowrap;
    }
    .rd-hero__photo {
        position: relative;
        border-radius: 28px;
        overflow: hidden;
        min-height: 380px;
        box-shadow: 0 8px 28px rgba(80, 40, 10, 0.08);
        background: #1a130f;
    }
    .rd-hero__photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        inset: 0;
    }
    .rd-hero__photo::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(26,19,15,0.08) 0%, rgba(26,19,15,0.55) 100%);
        pointer-events: none;
    }
    .rd-hero__tools {
        position: absolute;
        top: 1rem;
        inset-inline-end: 1rem;
        z-index: 2;
        display: flex;
        gap: 0.45rem;
    }
    .rd-hero__tools button {
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 50%;
        background: rgba(255,255,255,0.92);
        color: #1a130f;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    .rd-hero__tools button:hover { color: #c84500; }
    .rd-hero__tools .fav-page.is-on,
    .rd-hero__tools .fav-page.text-primary { color: #e11d48; }
    .rd-hero__caption {
        position: absolute;
        bottom: 1.15rem;
        inset-inline: 1.15rem;
        z-index: 2;
        color: #fff;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
    }
    .rd-hero__caption b {
        display: block;
        font-size: 0.95rem;
        font-weight: 800;
    }
    .rd-hero__caption span {
        font-size: 0.75rem;
        font-weight: 600;
        opacity: 0.9;
    }
    .rd-hero__back {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: #fff;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 800;
        background: rgba(255,255,255,0.16);
        backdrop-filter: blur(8px);
        border-radius: 9999px;
        padding: 0.45rem 0.9rem;
        white-space: nowrap;
    }
    .rd-hero__back:hover { background: rgba(255,255,255,0.28); }

    .rd-nav {
        position: sticky;
        top: 4.5rem;
        z-index: 40;
        background: rgba(250, 246, 240, 0.92);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid #efe6dc;
        margin-bottom: 1.35rem;
    }
    .rd-nav__inner {
        max-width: 80rem;
        margin: 0 auto;
        padding: 0.7rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    .rd-nav__cats {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        overflow-x: auto;
        min-width: 0;
        padding-bottom: 2px;
    }
    .restaurant-desktop .category-btn {
        background: #fff;
        color: #5c5148;
        border: 1px solid #eadfd3;
        border-radius: 9999px;
        padding: 0.48rem 0.95rem;
        font-size: 0.78rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
        box-shadow: none;
    }
    .restaurant-desktop .category-btn .material-symbols-outlined { font-size: 18px; }
    .restaurant-desktop .category-btn.is-active {
        background: #c84500;
        border-color: #c84500;
        color: #fff;
        box-shadow: 0 6px 14px rgba(200, 69, 0, 0.22);
    }
    .rd-search {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 9999px;
        padding: 0.2rem 0.95rem;
        min-width: 260px;
        max-width: 340px;
        flex: 1;
        box-shadow: 0 4px 14px rgba(80, 40, 10, 0.05);
    }
    .rd-search .material-symbols-outlined { color: #b0a398; font-size: 18px; }
    .rd-search input {
        border: 0;
        outline: none;
        background: transparent;
        width: 100%;
        height: 38px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1a130f;
        margin-inline-start: 0.4rem;
    }
    .rd-search input::placeholder { color: #b0a398; }

    .rd-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 1.35rem;
        align-items: start;
    }
    .rd-layout--browse { grid-template-columns: minmax(0, 1fr); }
    .rd-section { scroll-margin-top: 9rem; }
    .rd-section__head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
    }
    .rd-section__head h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 900;
        letter-spacing: -0.02em;
    }
    .rd-section__head small {
        font-size: 0.72rem;
        font-weight: 700;
        color: #9a8b80;
        background: #fff;
        border-radius: 9999px;
        padding: 0.28rem 0.7rem;
    }
    .rd-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
        margin-bottom: 1.75rem;
    }
    .rd-empty {
        background: #fff;
        border-radius: 20px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        color: #8a7a6e;
        font-weight: 700;
    }

    .rd-dish {
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 18px;
        padding: 0.7rem 0.7rem 0.85rem;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 4px 14px rgba(80, 40, 10, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        min-width: 0;
        scroll-margin-top: 9rem;
    }
    .rd-dish:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(80, 40, 10, 0.1);
    }
    .rd-dish.is-off { opacity: 0.62; }
    .rd-dish__media {
        position: relative;
        aspect-ratio: 1 / 0.82;
        border-radius: 14px;
        overflow: hidden;
        background: #f6f1ea;
        margin-bottom: 0.7rem;
    }
    .rd-dish__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .rd-dish:hover .rd-dish__media img { transform: scale(1.04); }
    .rd-dish__fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #c84500;
        font-size: 40px !important;
        background: #faeee4;
    }
    .rd-dish__badge {
        position: absolute;
        top: 8px;
        inset-inline-start: 8px;
        background: #1a130f;
        color: #fff;
        font-size: 0.58rem;
        font-weight: 800;
        padding: 0.22rem 0.5rem;
        border-radius: 8px;
        z-index: 2;
    }
    .rd-dish__badge.is-muted { background: #7a6d64; }
    .rd-dish__title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        line-height: 1.3;
    }
    .rd-dish__desc {
        margin: 0.2rem 0 0;
        font-size: 0.72rem;
        font-weight: 600;
        color: #9a8b80;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.1em;
    }
    .rd-dish__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 0.7rem;
    }
    .rd-dish__price {
        font-size: 1.05rem;
        font-weight: 900;
    }
    .rd-dish__actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .rd-dish__tune {
        border: 0;
        background: transparent;
        color: #9a8b80;
        font-size: 0.7rem;
        font-weight: 800;
        cursor: pointer;
        padding: 0;
    }
    .rd-dish__tune:hover { color: #c84500; }
    .rd-dish__plus {
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 50%;
        background: #c84500;
        color: #fff;
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 6px 12px rgba(200, 69, 0, 0.28);
    }
    .rd-dish__plus:hover { background: #b03d00; }
    .rd-dish--wide {
        grid-column: span 2;
        flex-direction: row;
        align-items: stretch;
        padding: 0.75rem;
    }
    .rd-dish--wide .rd-dish__media {
        width: 48%;
        aspect-ratio: auto;
        min-height: 210px;
        margin: 0;
        margin-inline-end: 0.9rem;
        flex-shrink: 0;
    }
    .rd-dish--wide .rd-dish__body {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
        padding: 0.45rem 0.15rem;
    }
    .rd-dish--wide .rd-dish__title { font-size: 1.25rem; }
    .rd-dish--wide .rd-dish__desc { -webkit-line-clamp: 3; font-size: 0.8rem; }

    .rd-rewards {
        background: #fff;
        border-radius: 22px;
        padding: 1.15rem 1.2rem 1.3rem;
        box-shadow: 0 4px 16px rgba(80, 40, 10, 0.05);
        margin-bottom: 1.5rem;
        scroll-margin-top: 9rem;
    }
    .rd-rewards__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }
    .rd-rewards__head h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 900;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .rd-rewards__head h2 .material-symbols-outlined { color: #c84500; }
    .rd-rewards__head small {
        font-size: 0.72rem;
        font-weight: 800;
        color: #c84500;
        background: #fff7ed;
        border-radius: 9999px;
        padding: 0.28rem 0.7rem;
    }
    .rd-rewards p.hint {
        margin: 0 0 0.85rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #9a8b80;
    }
    .rd-rewards__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.7rem;
    }
    .rd-reward {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        background: #FAF6F0;
        border-radius: 16px;
        padding: 0.65rem;
    }
    .rd-reward img {
        width: 58px;
        height: 58px;
        object-fit: cover;
        border-radius: 12px;
        flex-shrink: 0;
    }
    .rd-reward b { display: block; font-size: 0.82rem; }
    .rd-reward span { display: block; font-size: 0.7rem; font-weight: 700; color: #c84500; margin-top: 0.15rem; }
    .rd-reward a {
        margin-inline-start: auto;
        background: #c84500;
        color: #fff;
        border-radius: 9999px;
        padding: 0.35rem 0.75rem;
        font-size: 0.7rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .restaurant-desktop #reviews {
        background: #fff;
        border: 0;
        box-shadow: 0 4px 16px rgba(80, 40, 10, 0.05);
        margin-top: 0.25rem;
    }

    .rd-cart {
        position: sticky;
        top: 8.6rem;
        background: #fff;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 28px rgba(80, 40, 10, 0.08);
    }
    .rd-cart__head {
        background: #1a130f;
        color: #fff;
        padding: 1rem 1.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .rd-cart__head h2 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .rd-cart__count {
        background: #c84500;
        color: #fff;
        border-radius: 9999px;
        min-width: 1.4rem;
        height: 1.4rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 0 0.35rem;
    }
    .rd-cart__clear {
        border: 0;
        background: transparent;
        color: rgba(255,255,255,0.7);
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }
    .rd-cart__clear:hover { color: #fff; }
    .rd-cart__lines {
        padding: 0.9rem 1.05rem;
        min-height: 140px;
        max-height: 280px;
        overflow-y: auto;
    }
    .rd-cart__lines [data-cart-empty] {
        text-align: center;
        color: #9a8b80;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 2rem 0.5rem;
    }
    .rd-note {
        margin: 0 1.05rem 1rem;
        background: #FAF6F0;
        border-radius: 14px;
        padding: 0.7rem 0.8rem;
        display: flex;
        gap: 0.55rem;
        align-items: flex-start;
        font-size: 0.72rem;
        font-weight: 700;
        color: #5c5148;
    }
    .rd-note .material-symbols-outlined { color: #c84500; font-size: 18px; }
    .rd-note span { display: block; font-weight: 600; color: #9a8b80; margin-top: 0.1rem; }
    .rd-cart [data-desktop-cart-summary] a {
        border-radius: 9999px !important;
        background: #c84500 !important;
        font-weight: 800 !important;
    }
    .rd-cart [data-desktop-cart-summary] a:hover { background: #b03d00 !important; }

    @media (max-width: 1279px) {
        .rd-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rd-dish--wide { grid-column: span 2; }
        .rd-layout { grid-template-columns: minmax(0, 1fr) 280px; }
        .rd-layout--browse { grid-template-columns: minmax(0, 1fr); }
    }
</style>

<div class="restaurant-desktop hidden lg:flex flex-col w-full">
    <div class="rd-wrap">
        <section class="rd-hero">
            <div class="rd-hero__identity">
                <nav class="rd-crumb" aria-label="مسار التنقل">
                    <a href="{{ route('home') }}">الرئيسية</a>
                    <span>/</span>
                    <a href="{{ route('restaurants.index') }}">المطاعم</a>
                    <span>/</span>
                    <span class="is-current">{{ $restaurant->name }}</span>
                </nav>
                <div class="rd-hero__kicker">
                    <span class="material-symbols-outlined text-[16px]">{{ $restaurant->type === 'cafe' ? 'local_cafe' : 'restaurant' }}</span>
                    <span>{{ $restaurant->cuisineLabel() }} • {{ $areaShort }}</span>
                </div>
                <div>
                    <h1>
                        {{ $restaurant->name }}
                        @if($restaurant->isOpen())
                            <span class="rd-hero__status is-open"><i></i> مفتوح</span>
                        @else
                            <span class="rd-hero__status is-closed"><i></i> مغلق</span>
                        @endif
                    </h1>
                    @if($restaurant->description)
                        <p class="rd-hero__lead">{{ $restaurant->description }}</p>
                    @endif
                </div>
                <div class="rd-stats">
                    <div class="rd-stat">
                        @include('partials.star-icon', ['class' => 'w-4 h-4 text-[#d65e15]'])
                        <div>
                            <b>{{ $rating }}</b>
                            <span>{{ number_format($reviews) }} تقييم معتمد</span>
                        </div>
                    </div>
                    <div class="rd-stat">
                        <span class="material-symbols-outlined">schedule</span>
                        <div>
                            <b>{{ $eta[0] }}–{{ $eta[1] }} د</b>
                            <span>{{ $hours ? 'العمل '.$hours : 'وقت التوصيل المتوقع' }}</span>
                        </div>
                    </div>
                    <div class="rd-stat">
                        <span class="material-symbols-outlined">location_on</span>
                        <div>
                            <b>{{ $areaShort }}</b>
                            <span>توصيل داخل غزة</span>
                        </div>
                    </div>
                    <div class="rd-stat">
                        <span class="material-symbols-outlined">restaurant_menu</span>
                        <div>
                            <b>{{ $itemCount }} صنفاً</b>
                            <span>رسوم التوصيل {{ number_format($deliveryFee, 0) }} ₪</span>
                        </div>
                    </div>
                </div>
                @if($canShop ?? true)
                <div class="rd-hero__vip">
                    <p>{{ $vip ? 'أنت مؤهل لخصم '.$membership->discount_percent.'% فوري على هذا الطلب' : 'اشترك واحصل على خصم 10% فوري مع كل طلب' }}</p>
                    @if($vip)
                        <em>عضوية نشطة</em>
                    @else
                        <a href="{{ route('memberships.index') }}">عضوية ذهبية</a>
                    @endif
                </div>
                <div class="mt-3">
                    @include('partials.group-order-entry', [
                        'restaurant' => $restaurant,
                        'entryClass' => 'inline-flex items-center justify-center gap-1.5 rounded-xl border border-[#ead9c8] bg-white text-stone-800 font-extrabold text-sm px-4 py-2.5 hover:border-primary/50 hover:text-primary transition-colors shadow-xs',
                    ])
                </div>
                @endif
            </div>
            <div class="rd-hero__photo">
                <img src="{{ $restaurant->coverUrl() }}" alt="{{ $restaurant->name }}">
                <div class="rd-hero__tools">
                    <button type="button" class="share-page" title="مشاركة">
                        <span class="material-symbols-outlined text-[20px]">share</span>
                    </button>
                    @include('partials.favorite-button', [
                        'type' => 'restaurant',
                        'id' => $restaurant->id,
                        'class' => 'fav-page',
                    ])
                </div>
                <div class="rd-hero__caption">
                    <div>
                        <b>{{ $restaurant->cuisineLabel() }} من قلب غزة</b>
                        <span>{{ $restaurant->areaLabel() }}</span>
                    </div>
                    <a class="rd-hero__back" href="{{ route('restaurants.index') }}">
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        المطاعم
                    </a>
                </div>
            </div>
        </section>
    </div>

    <div class="rd-nav">
        <div class="rd-nav__inner">
            <div class="rd-nav__cats">
                @foreach($menuSections as $section)
                    <button type="button" class="category-btn{{ $loop->first ? ' is-active' : '' }}" data-section="{{ $section['key'] }}">
                        <span class="material-symbols-outlined">{{ $section['icon'] }}</span>
                        <span>{{ $section['name'] }}</span>
                    </button>
                @endforeach
                @if(($canShop ?? true) && count($rewards))
                    <button type="button" class="category-btn" data-section="rewards">
                        <span class="material-symbols-outlined">stars</span>
                        <span>استبدال النقاط</span>
                    </button>
                @endif
            </div>
            <div class="rd-search">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input class="menu-search" placeholder="ابحث داخل قائمة {{ $restaurant->name }}..." type="search">
            </div>
        </div>
    </div>

    <div class="rd-wrap" style="padding-top: 0;">
        <div class="rd-layout{{ ($canShop ?? true) ? '' : ' rd-layout--browse' }}">
            <div>
                @forelse($menuSections as $section)
                    <section class="menu-section rd-section" id="section-{{ $section['key'] }}" data-section="{{ $section['key'] }}">
                        <div class="rd-section__head">
                            <h2>{{ $section['name'] }}</h2>
                            <small>{{ $section['hint'] }} · {{ $section['items']->count() }} أطباق</small>
                        </div>
                        <div class="rd-grid">
                            @foreach($section['items'] as $item)
                                @include('restaurants.partials.dish-card-desktop', [
                                    'item' => $item,
                                    'sectionKey' => $section['key'],
                                    'popular' => $loop->parent->first && $loop->first,
                                    'featured' => $loop->parent->first && $loop->first,
                                ])
                            @endforeach
                        </div>
                    </section>
                @empty
                    <p class="rd-empty">لا توجد أصناف في القائمة حالياً.</p>
                @endforelse

                @if(($canShop ?? true) && count($rewards))
                <section class="menu-section rd-rewards" id="section-rewards" data-section="rewards">
                    <div class="rd-rewards__head">
                        <h2>
                            <span class="material-symbols-outlined">stars</span>
                            استبدال النقاط من قائمة {{ $restaurant->name }}
                        </h2>
                        <small>رصيدك: {{ $pointsBalance }} نقطة</small>
                    </div>
                    <p class="hint">هذه الأصناف من منيو هذا المطعم فقط. سعر الاستبدال = سعر الطبق بالنقاط.</p>
                    <div class="rd-rewards__grid">
                        @foreach($rewards as $reward)
                            <div class="rd-reward">
                                <img alt="{{ $reward['name'] }}" src="{{ $reward['image'] }}">
                                <div>
                                    <b>{{ $reward['name'] }}</b>
                                    <span>مجاناً بـ {{ $reward['points'] }} نقطة</span>
                                </div>
                                <a href="{{ $reward['url'] ?? route('redeem.create') }}">استبدال</a>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

                @include('partials.restaurant-reviews')
            </div>

            @if($canShop ?? true)
            <aside>
                <div class="rd-cart">
                    <div class="rd-cart__head">
                        <h2>
                            <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                            سلة طلبك
                            <span data-desktop-cart-count class="rd-cart__count">{{ $restaurantCart ? $cartCount : 0 }}</span>
                        </h2>
                        <form method="POST" action="{{ route('cart.clear') }}" data-desktop-cart-clear class="{{ $restaurantCart ? '' : 'hidden' }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rd-cart__clear">
                                <span class="material-symbols-outlined text-[16px]">delete_outline</span>
                                تفريغ
                            </button>
                        </form>
                    </div>

                    <div data-desktop-cart-lines class="rd-cart__lines">
                        @forelse($restaurantCart['lines'] ?? [] as $line)
                            <div class="pt-2 first:pt-0 flex flex-col gap-1.5 border-t border-slate-100 first:border-0" data-line-id="{{ $line['item']->id }}">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-[13px] font-semibold text-on-surface truncate">{{ $line['item']->name }}</span>
                                    <span class="text-[13px] font-bold text-on-surface shrink-0">{{ number_format($line['line_total'], 0) }} <span class="ils">₪</span></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2 bg-surface-container-low rounded-lg px-2 py-0.5 border border-slate-200/50">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="item_id" value="{{ $line['item']->id }}">
                                        <button type="submit" name="quantity" value="{{ $line['qty'] - 1 }}" class="w-4 h-4 flex items-center justify-center text-slate-500 text-xs font-bold">-</button>
                                        <span class="text-xs font-semibold px-1">{{ $line['qty'] }}</span>
                                        <button type="submit" name="quantity" value="{{ $line['qty'] + 1 }}" class="w-4 h-4 flex items-center justify-center text-slate-500 text-xs font-bold">+</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p data-cart-empty>سلتك فارغة. أضف طبقاً للبدء.</p>
                        @endforelse
                    </div>

                    <div class="rd-note">
                        <span class="material-symbols-outlined">local_shipping</span>
                        <div>
                            <b>ضمان وصول الوجبة ساخنة</b>
                            <span>حقائب حرارية داخل أحياء غزة</span>
                        </div>
                    </div>

                    <div data-desktop-cart-summary class="{{ $restaurantCart ? '' : 'hidden' }}">
                        <div class="p-3.5 bg-surface-container-low/40 flex flex-col gap-2 border-t border-slate-200/60 text-xs">
                            <div class="flex items-center justify-between text-slate-500">
                                <span>المجموع الفرعي</span>
                                <span data-cart-subtotal class="font-semibold text-on-surface">{{ number_format($restaurantCart['subtotal'] ?? 0, 0) }} <span class="ils">₪</span></span>
                            </div>
                            <div data-cart-discount-row class="flex items-center justify-between text-secondary {{ ($restaurantCart['discount_percent'] ?? 0) ? '' : 'hidden' }}">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">verified</span>خصم VIP (<span data-cart-discount-percent>{{ $restaurantCart['discount_percent'] ?? 0 }}</span>%)
                                </span>
                                <span data-cart-discount-amount class="font-semibold">- {{ number_format($restaurantCart['discount_amount'] ?? 0, 1) }} <span class="ils">₪</span></span>
                            </div>
                            <div class="h-px bg-slate-200/60 my-0.5"></div>
                            <div class="flex items-center justify-between pt-0.5">
                                <span class="text-sm font-bold text-on-surface">المجموع الإجمالي</span>
                                <span data-cart-grand-total class="text-lg font-bold text-primary">{{ number_format($restaurantCart['items_total'] ?? $restaurantCart['subtotal'] ?? 0, 1) }} <span class="ils">₪</span></span>
                            </div>
                        </div>
                        <div class="p-3.5 pt-1 bg-surface-container-low/40">
                            <a href="{{ $groupCheckoutUrl ?? (auth()->check() ? route('checkout.create') : route('login')) }}" class="w-full py-2.5 rounded-xl bg-primary text-white hover:bg-primary-container font-label-md text-[13px] font-semibold shadow-xs flex items-center justify-center gap-2">
                                <span>متابعة الدفع</span>
                                <span class="material-symbols-outlined text-[17px]">arrow_back</span>
                            </a>
                            <div class="flex items-center justify-center gap-1 text-slate-400 text-[11px] mt-2.5">
                                <span class="material-symbols-outlined text-[13px] text-secondary">verified_user</span>
                                <span>دفع آمن بالاستلام أو إشعار الحوالة</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
            @endif
        </div>
    </div>
</div>
