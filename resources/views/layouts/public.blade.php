<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'سفرة غزة') — سفرة غزة</title>
    @include('partials.icon-font')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800;900&family=Amiri:wght@400;700&family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-alexandria { font-family: 'Alexandria', sans-serif !important; }
        .font-tajawal { font-family: 'Tajawal', sans-serif !important; }
        .font-cairo { font-family: 'Cairo', sans-serif !important; }
        .app-toast { top: calc(4.75rem + env(safe-area-inset-top, 0px)); bottom: auto; }
        @media (min-width: 1024px) {
            .app-toast { top: 5.25rem; inset-inline-end: 1.75rem; inset-inline-start: auto; }
        }
        .ils {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 0.82rem;
            height: 0.82rem;
            font-family: 'ShekelMark', Arial, 'Segoe UI', sans-serif;
            font-size: 0.68rem !important;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0;
            vertical-align: middle;
            margin-inline: 0.18em 0.02em;
            transform: none;
        }
        /* Filled Star Glyph & Colors Site-wide (Matching Foodly reference #d65e15) */
        .fill-1,
        [data-fill="1"] {
            font-variation-settings: 'FILL' 1, 'wght' 600, 'GRAD' 0, 'opsz' 24 !important;
        }
        .fill-1.text-amber-500,
        .fill-1.text-amber-600,
        .fill-1.text-tertiary,
        .text-amber-500.fill-1,
        .text-amber-600.fill-1,
        .rating-star-icon {
            color: #d65e15 !important;
        }
        /* ═══════════════════════════════════════════════════════════════════
           STRICT SEPARATION: MOBILE HEADER vs DESKTOP HEADER
           ═══════════════════════════════════════════════════════════════════ */
        #site-header-desktop {
            display: none !important;
        }
        #site-header-mobile {
            display: block;
        }

        @media (max-width: 1023px) {
            #site-header-desktop {
                display: none !important;
            }
            #site-header-mobile {
                display: block !important;
            }
            #site-header-mobile.is-subpage-hidden {
                display: none !important;
            }
        }

        @media (min-width: 1024px) {
            #site-header-mobile {
                display: none !important;
            }
            #site-header-desktop {
                display: block !important;
            }

            body.is-home-page {
                background-color: #FAF6F0 !important;
            }
            body.is-home-page #site-header-desktop {
                position: relative;
                background: #FAF6F0;
                border-bottom: none !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                padding-top: 1rem;
                padding-bottom: 0.5rem;
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            }
            body.is-home-page .site-main {
                padding-top: 0 !important;
                background: transparent !important;
            }
            body.is-home-page #site-header-desktop .site-header__bar {
                height: auto;
                max-width: 80rem;
                margin-inline: auto;
                padding-inline: 1.5rem;
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            }
            body.is-home-page #site-header-desktop .site-header__nav {
                display: flex;
                gap: 1.75rem;
            }
            body.is-home-page #site-header-desktop .site-header__nav a {
                font-size: 15px;
                font-weight: 700;
                color: #443833;
            }
            body.is-home-page #site-header-desktop .site-header__nav a:hover,
            body.is-home-page #site-header-desktop .site-header__nav a.is-active {
                color: #c84500;
            }
            body.is-home-page #site-header-desktop .site-header__nav a::after {
                display: none !important;
            }

            /* ═══════════════════════════════════════════════════════════════════
               CRAVK STYLE FLOATING BLACK PILL CAPSULE HEADER (ON SCROLL DOWN - DESKTOP ONLY)
               ═══════════════════════════════════════════════════════════════════ */
            #site-header-desktop.is-scrolled {
                position: fixed !important;
                top: 14px !important;
                inset-inline: 0 !important;
                width: 100% !important;
                z-index: 100 !important;
                background: transparent !important;
                border-bottom: none !important;
                box-shadow: none !important;
                padding: 0 1rem !important;
                pointer-events: none !important;
                display: flex !important;
                justify-content: center !important;
                animation: sgPillDrop 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
            }

            @keyframes sgPillDrop {
                0% {
                    opacity: 0;
                    transform: translateY(-20px) scale(0.97);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            #site-header-desktop.is-scrolled .site-header__bar {
                pointer-events: auto !important;
                background: #0d0d0f !important;
                background: rgba(13, 13, 15, 0.94) !important;
                backdrop-filter: blur(20px) !important;
                -webkit-backdrop-filter: blur(20px) !important;
                border-radius: 9999px !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.06) !important;
                padding: 0.25rem 0.4rem 0.25rem 1rem !important;
                max-width: 1040px !important;
                width: 100% !important;
                height: 48px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 0.75rem !important;
                transition: all 0.3s ease !important;
            }

            /* Brand Logo in Black Capsule */
            #site-header-desktop.is-scrolled .site-header__brand {
                gap: 0.4rem !important;
            }
            #site-header-desktop.is-scrolled .site-header__brand img {
                height: 28px !important;
                width: auto !important;
            }
            #site-header-desktop.is-scrolled .site-header__brand span.text-stone-900 {
                color: #ffffff !important;
                font-size: 13.5px !important;
                white-space: nowrap !important;
            }
            #site-header-desktop.is-scrolled .site-header__brand span.text-stone-400 {
                color: #9ca3af !important;
                font-size: 7.5px !important;
                white-space: nowrap !important;
            }
            #site-header-desktop.is-scrolled .site-header__location {
                display: none !important;
            }

            /* Center Nav Links inside Floating Pill */
            #site-header-desktop.is-scrolled .site-header__nav {
                display: flex !important;
                align-items: center !important;
                gap: 0.2rem !important;
                margin: 0 !important;
                flex-wrap: nowrap !important;
            }
            #site-header-desktop.is-scrolled .site-header__nav a {
                color: #d1d5db !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                padding: 0.25rem 0.55rem !important;
                border-radius: 9999px !important;
                transition: all 0.2s ease !important;
                text-decoration: none !important;
                display: inline-flex !important;
                align-items: center !important;
                white-space: nowrap !important;
                line-height: 1 !important;
            }
            #site-header-desktop.is-scrolled .site-header__nav a:hover {
                color: #ffffff !important;
                background: rgba(255, 255, 255, 0.08) !important;
            }

            /* Active Link: Distinct White Pill with Black Dot & Bold Text */
            #site-header-desktop.is-scrolled .site-header__nav a.is-active {
                background: #ffffff !important;
                color: #0d0d0f !important;
                font-weight: 800 !important;
                font-size: 11.5px !important;
                padding: 0.25rem 0.75rem !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25) !important;
                gap: 0.35rem !important;
                white-space: nowrap !important;
            }
            #site-header-desktop.is-scrolled .site-header__nav a.is-active::before {
                content: '' !important;
                display: inline-block !important;
                width: 5px !important;
                height: 5px !important;
                border-radius: 9999px !important;
                background: #0d0d0f !important;
            }

            /* Action Buttons inside Black Capsule */
            #site-header-desktop.is-scrolled .site-header__actions {
                display: flex !important;
                align-items: center !important;
                gap: 0.4rem !important;
                flex-wrap: nowrap !important;
            }
            #site-header-desktop.is-scrolled .site-header__actions button,
            #site-header-desktop.is-scrolled .site-header__actions a:not(.site-header__cta) {
                background: rgba(255, 255, 255, 0.08) !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                color: #f3f4f6 !important;
                width: 32px !important;
                height: 32px !important;
                border-radius: 9999px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                transition: all 0.2s ease !important;
            }
            #site-header-desktop.is-scrolled .site-header__actions button .material-symbols-outlined,
            #site-header-desktop.is-scrolled .site-header__actions a:not(.site-header__cta) .material-symbols-outlined {
                font-size: 17px !important;
            }
            #site-header-desktop.is-scrolled .site-header__actions button:hover,
            #site-header-desktop.is-scrolled .site-header__actions a:not(.site-header__cta):hover {
                background: rgba(255, 255, 255, 0.18) !important;
                color: #ff9800 !important;
            }

            /* Orange Pill CTA Button (Matches Cravk orange pill button) */
            #site-header-desktop.is-scrolled .site-header__actions a.site-header__cta {
                background: #ea580c !important;
                background: linear-gradient(135deg, #ea580c 0%, #c84500 100%) !important;
                color: #ffffff !important;
                border-radius: 9999px !important;
                padding: 0.35rem 0.95rem !important;
                font-weight: 800 !important;
                font-size: 11.5px !important;
                box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35) !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 0.35rem !important;
                border: none !important;
                white-space: nowrap !important;
                transition: all 0.2s ease !important;
            }
            #site-header-desktop.is-scrolled .site-header__actions a.site-header__cta:hover {
                filter: brightness(1.08) !important;
                transform: scale(1.03) !important;
            }
        }

        /* Desktop footer — Grapeslab layout, Sofra Gaza colors */
        .sg-footer {
            background: #FAF6F0;
            padding: 2.75rem 0 0;
            margin: 0;
        }
        .sg-footer__shell {
            width: 100%;
            margin: 0;
            border-radius: 48px 48px 0 0;
            overflow: hidden;
            background: linear-gradient(165deg, #F7EDE3 0%, #EEDCC8 52%, #E8D4BC 100%);
            box-shadow: 0 -14px 36px rgba(80, 40, 10, 0.08);
        }
        .sg-footer__panel {
            --sg-footer-cols: 1.45fr 0.9fr 0.9fr 1fr 1.05fr;
            background: linear-gradient(165deg, #F7EDE3 0%, #EEDCC8 52%, #E8D4BC 100%);
            padding: 52px 56px 28px;
        }
        .sg-footer__grid {
            display: grid;
            grid-template-columns: var(--sg-footer-cols);
            gap: 2.5rem 1.75rem;
            align-items: start;
        }
        .sg-footer__brand-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        .sg-footer__brand-row img {
            height: 42px;
            width: auto;
            object-fit: contain;
        }
        .sg-footer__blurb {
            margin: 0 0 22px;
            max-width: 240px;
            font-size: 0.78rem;
            line-height: 1.75;
            color: #6b5a4e;
            font-weight: 600;
        }
        .sg-footer__socials {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sg-footer__social {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            color: #3d342c;
            box-shadow: 0 2px 8px rgba(80, 40, 10, 0.08);
            transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
        }
        .sg-footer__social svg {
            width: 15px;
            height: 15px;
        }
        .sg-footer__social:hover {
            transform: translateY(-2px);
            color: #3d342c;
        }
        .sg-footer__col-title {
            margin: 4px 0 18px;
            font-size: 0.95rem;
            font-weight: 800;
            color: #1a130f;
        }
        .sg-footer__col-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 11px;
        }
        .sg-footer__col-list a,
        .sg-footer__col-list span {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b5a4e;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .sg-footer__col-list a:hover {
            color: #a33900;
        }
        .sg-footer__bottom {
            position: relative;
            display: grid;
            grid-template-columns: var(--sg-footer-cols);
            gap: 2.5rem 1.75rem;
            align-items: center;
            margin-top: 2.35rem;
            min-height: 40px;
        }
        .sg-footer__copy {
            position: absolute;
            inset-inline: 0;
            text-align: center;
            font-size: 0.78rem;
            font-weight: 600;
            color: #6b5a4e;
            pointer-events: none;
        }
        .sg-footer__pays {
            grid-column: 5;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: flex-start;
            position: relative;
            z-index: 1;
        }
        .sg-footer__pays img {
            height: 28px;
            width: auto;
            max-width: 52px;
            object-fit: contain;
            border-radius: 6px;
        }
        @media (max-width: 1100px) {
            .sg-footer__panel { padding: 40px 32px 28px; }
            .sg-footer__grid,
            .sg-footer__bottom { grid-template-columns: 1.2fr 1fr 1fr; }
            .sg-footer__pays { grid-column: 3; }
        }
    </style>
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $pointsBalance = auth()->user()->points_balance ?? 0;
    $walletBalance = auth()->user()->wallet_balance ?? 0;
    $canShop = $canShop ?? \App\Models\User::currentCanShop();
    $accountHomeUrl = $accountHomeUrl ?? (auth()->user()?->publicAccountUrl() ?? route('login'));
@endphp
<body class="bg-surface font-body-md text-body-md text-on-surface antialiased {{ request()->routeIs('home') ? 'is-home-page' : 'is-sub-page' }} @yield('body_class')" data-auth="{{ auth()->check() && auth()->user()->canShopAsCustomer() ? '1' : '0' }}">

    {{-- ═══════════════════════════════════════════════════════════════════
         1. NATIVE MOBILE HEADER (MOBILE ONLY: < 1024px)
         ═══════════════════════════════════════════════════════════════════ --}}
    <header id="site-header-mobile" class="site-header site-header--mobile {{ request()->routeIs('home') ? '' : 'is-subpage-hidden' }}">
        <div class="site-header__bar">
            <div class="site-header__start">
                <a href="{{ route('home') }}" class="site-header__brand">
                    <img alt="شعار سفرة غزة" src="{{ $logoSrc }}">
                </a>

                <div class="site-header__location">
                    @include('partials.area-picker', ['variant' => 'stacked'])
                </div>
            </div>

            <div class="site-header__actions">
                @if($canShop)
                    @auth
                        <a href="{{ route('account.wallet') }}" class="flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/60 transition-colors shrink-0" title="رصيد المحفظة">
                            <span class="material-symbols-outlined text-[16px] text-emerald-600">account_balance_wallet</span>
                            <span class="font-mono text-xs">{{ number_format($walletBalance, 0) }}</span>
                            <span class="ils text-[10px]">₪</span>
                        </a>
                        <a href="{{ route('account.points') }}" class="header-points shrink-0" title="نقاط الولاء">
                            @include('partials.gold-coin-icon', ['class' => 'w-4 h-4'])
                            <span>{{ $pointsBalance }}</span>
                        </a>
                    @endauth
                @endif

                <div class="header-session">
                    @if($canShop)
                        <a href="{{ route('cart.index') }}" class="header-icon-btn" aria-label="السلة" data-header-cart>
                            <span class="material-symbols-outlined">shopping_bag</span>
                            <span class="header-badge" data-header-cart-badge @if(! $cartCount) hidden @endif>{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                        </a>
                    @endif

                    @auth
                        @php
                            $headerTier = auth()->user()->tier();
                        @endphp
                        <a href="{{ $accountHomeUrl }}" class="header-account" title="حسابي • مستوى الولاء: {{ $headerTier['name'] }}">
                            <div class="relative inline-flex items-center justify-center shrink-0">
                                @if(auth()->user()->photo_path)
                                    <img src="{{ auth()->user()->photoUrl() }}" alt="{{ auth()->user()->name }}" class="header-avatar object-cover">
                                @else
                                    <span class="header-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                                @endif
                                <span class="absolute -bottom-1 -left-1 w-4 h-4 rounded-full bg-white ring-2 ring-white shadow-xs flex items-center justify-center pointer-events-none z-10" title="مستوى الولاء: {{ $headerTier['name'] }}">
                                    @include('partials.tier-icon', ['tier' => $headerTier['key'], 'class' => 'w-full h-full'])
                                </span>
                            </div>
                            <span class="header-account__name">{{ explode(' ', auth()->user()->name)[0] }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="header-cta">
                            <span>دخول</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        @unless(request()->routeIs('account.*') || request()->routeIs('cart.*') || request()->routeIs('checkout.*') || View::hasSection('hideMobileSearch'))
            <div class="site-header__mobile-search">
                @include('partials.header-search')
            </div>
        @endunless
    </header>

    {{-- ═══════════════════════════════════════════════════════════════════
         2. DESKTOP HEADER (DESKTOP ONLY: >= 1024px)
         ═══════════════════════════════════════════════════════════════════ --}}
    <header id="site-header-desktop" class="site-header site-header--desktop">
        <div class="site-header__bar">
            {{-- Brand Logo --}}
            <div class="site-header__start">
                <a href="{{ route('home') }}" class="site-header__brand flex items-center gap-2.5">
                    <img alt="شعار سفرة غزة" src="{{ $logoSrc }}" class="h-9 sm:h-11 w-auto object-contain">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="font-black text-stone-900 text-lg leading-tight tracking-tight">سُفرة <span class="text-[#c84500]">غزة</span></span>
                        <span class="text-[9px] font-bold text-stone-400 tracking-wider">SOFRA GAZA</span>
                    </div>
                </a>

                <div class="site-header__location hidden xl:block">
                    @include('partials.area-picker', ['variant' => 'stacked'])
                </div>
            </div>

            {{-- Center Navigation Links (Foodly Style) --}}
            <nav class="site-header__nav" aria-label="التنقل الرئيسي">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">الرئيسية</a>
                <a href="{{ route('restaurants.index') }}" class="{{ request()->routeIs('restaurants.*') ? 'is-active' : '' }}">المطاعم</a>
                <a href="{{ route('restaurants.index') }}">التصنيفات</a>
                @if($canShop)
                    <a href="{{ route('memberships.index') }}" class="{{ request()->routeIs('memberships.*') ? 'is-active' : '' }}">المكافآت</a>
                @endif
                <a href="{{ route('home') }}#why-us">عن سفرة غزة</a>
                <a href="#site-footer">تواصل معنا</a>
            </nav>

            {{-- Right/End Action Icons & CTA --}}
            <div class="site-header__actions">
                @if($canShop)
                    @auth
                        <a href="{{ route('account.wallet') }}" class="hidden md:flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/60 transition-colors shrink-0" title="رصيد المحفظة">
                            <span class="material-symbols-outlined text-[16px] text-emerald-600">account_balance_wallet</span>
                            <span class="font-mono text-xs">{{ number_format($walletBalance, 0) }}</span>
                            <span class="ils text-[10px]">₪</span>
                        </a>
                        <a href="{{ route('account.points') }}" class="header-points shrink-0" title="نقاط الولاء">
                            @include('partials.gold-coin-icon', ['class' => 'w-4 h-4'])
                            <span>{{ $pointsBalance }}</span>
                        </a>
                    @endauth
                @endif

                {{-- 1. Search Icon Button (Circular) --}}
                <button type="button" 
                        onclick="document.getElementById('header-search-modal')?.classList.toggle('hidden'); document.getElementById('header-search-input')?.focus();" 
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-xs border border-stone-200/80 flex items-center justify-center text-stone-700 hover:text-[#c84500] hover:border-[#c84500]/30 transition-all shrink-0 cursor-pointer" 
                        aria-label="بحث">
                    <span class="material-symbols-outlined text-[20px]">search</span>
                </button>

                {{-- 2. User / Account Icon Button (Circular) --}}
                @auth
                    <a href="{{ $accountHomeUrl }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-xs border border-stone-200/80 flex items-center justify-center text-stone-700 hover:text-[#c84500] hover:border-[#c84500]/30 transition-all shrink-0 overflow-hidden" title="حسابي">
                        @if(auth()->user()->photo_path)
                            <img src="{{ auth()->user()->photoUrl() }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-xs border border-stone-200/80 flex items-center justify-center text-stone-700 hover:text-[#c84500] hover:border-[#c84500]/30 transition-all shrink-0" title="تسجيل الدخول">
                        <span class="material-symbols-outlined text-[20px]">person</span>
                    </a>
                @endauth

                @if($canShop)
                    {{-- 3. Cart Icon Button (Circular with Badge Counter) --}}
                    <a href="{{ route('cart.index') }}" class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-xs border border-stone-200/80 flex items-center justify-center text-stone-700 hover:text-[#c84500] hover:border-[#c84500]/30 transition-all shrink-0" aria-label="السلة" data-header-cart>
                        <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                        <span class="header-badge absolute -top-1 -right-1 bg-[#c84500] text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow-xs" data-header-cart-badge @if(! $cartCount) hidden @endif>{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                    </a>

                    {{-- 4. Order Now CTA Pill Button --}}
                    <a href="{{ route('restaurants.index') }}" class="site-header__cta px-4 py-2 sm:px-6 sm:py-2.5 rounded-full bg-[#c84500] hover:bg-[#b03d00] text-white font-extrabold text-xs sm:text-sm shadow-sm hover:shadow hover:scale-[1.02] active:scale-95 transition-all whitespace-nowrap inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[17px]">shopping_bag</span>
                        <span>اطلب الآن</span>
                    </a>
                @else
                    <a href="{{ route('restaurants.index') }}" class="site-header__cta px-4 py-2 sm:px-6 sm:py-2.5 rounded-full bg-[#c84500] hover:bg-[#b03d00] text-white font-extrabold text-xs sm:text-sm shadow-sm hover:shadow hover:scale-[1.02] active:scale-95 transition-all whitespace-nowrap inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[17px]">storefront</span>
                        <span>استعرض المطاعم</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Quick Search Modal (Desktop) --}}
        <div id="header-search-modal" class="hidden fixed inset-0 z-50 bg-stone-900/40 backdrop-blur-xs flex items-start justify-center pt-20 px-4" onclick="if(event.target === this) this.classList.add('hidden')">
            <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-2xl w-full max-w-lg border border-stone-200" onclick="event.stopPropagation()">
                <form action="{{ route('restaurants.index') }}" method="GET" class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-amber-600 text-[22px]">search</span>
                    <input id="header-search-input" type="text" name="q" placeholder="ابحث عن مطعم، كافيه، أو وجبتك المفضلة..." class="w-full text-sm sm:text-base font-bold text-stone-800 outline-none bg-transparent">
                    <button type="submit" class="px-4 py-2 rounded-full bg-[#c84500] text-white font-bold text-xs shrink-0 hover:bg-[#b03d00] transition-colors">بحث</button>
                    <button type="button" onclick="document.getElementById('header-search-modal').classList.add('hidden')" class="p-1 text-stone-400 hover:text-stone-700">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Native Mobile Subpage Header (Shown on all pages except Home and Restaurant Details on mobile) --}}
    @unless(request()->routeIs('home') || request()->routeIs('restaurants.show') || View::hasSection('hideMobileSubpageBar'))
        <div class="mobile-subpage-bar lg:hidden sticky top-0 inset-x-0 z-40 bg-white/95 backdrop-blur-xl border-b border-stone-200/70 shadow-[0_1px_6px_rgba(0,0,0,0.03)] pt-safe">
            <div class="h-12 px-3.5 flex items-center justify-between">
                {{-- 1. زر الرجوع بدون خلفية (Clean Back Arrow without background) --}}
                <button type="button" 
                        onclick="if(window.customBackHandler) { window.customBackHandler(); } else if(window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('home') }}'; }" 
                        aria-label="رجوع" 
                        class="w-10 h-10 -mr-2 flex items-center justify-center text-stone-800 hover:text-primary active:scale-90 transition-transform bg-transparent border-0 p-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[24px]">arrow_forward</span>
                </button>

                {{-- 2. عنوان الصفحة الحالية --}}
                <h1 id="mobile-subpage-title" class="text-sm font-extrabold text-stone-900 truncate text-center flex-1 px-2 select-none">
                    @yield('title', 'سفرة غزة')
                </h1>

                {{-- 3. أيقونة السلة أو زر إفراغ السلة في صفحة السلة --}}
                @if($canShop && request()->routeIs('cart.index'))
                    <form id="header-clear-cart-form" method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('هل تريد إفراغ السلة بالكامل؟');" class="{{ $cartCount ? '' : 'hidden' }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="إفراغ السلة" title="إفراغ السلة" class="w-10 h-10 -ml-2 flex items-center justify-center text-rose-500 hover:text-rose-600 active:scale-90 transition-transform bg-transparent cursor-pointer border-0 p-0">
                            <span class="material-symbols-outlined text-[23px]">delete_outline</span>
                        </button>
                    </form>
                    <div id="header-cart-empty-spacer" class="w-10 h-10 -ml-2 {{ $cartCount ? 'hidden' : '' }}"></div>
                @elseif($canShop)
                    <a href="{{ route('cart.index') }}" 
                       aria-label="السلة" 
                       class="relative w-10 h-10 -ml-2 flex items-center justify-center text-stone-800 hover:text-primary active:scale-90 transition-transform bg-transparent cursor-pointer" 
                       data-header-cart>
                        <span class="material-symbols-outlined text-[23px]">shopping_bag</span>
                        <span class="header-badge absolute top-1.5 left-1.5 bg-primary text-white text-[9px] font-extrabold w-4 h-4 rounded-full flex items-center justify-center shadow-xs" 
                              data-header-cart-badge 
                              @if(! $cartCount) hidden @endif>{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                    </a>
                @else
                    <div class="w-10 h-10 -ml-2"></div>
                @endif
            </div>
        </div>
    @endunless

    <main class="w-full bg-surface pb-28 lg:pb-0 site-main">
        @include('partials.group-order-banner')
        @yield('content')
    </main>

    <div id="app-toast" class="app-toast" hidden role="status" aria-live="polite" data-success="{{ session('success') }}" data-error="{{ session('error') ?: $errors->first() }}">
        <span class="app-toast__icon material-symbols-outlined fill-1">check_circle</span>
        <p class="app-toast__text"></p>
    </div>

    @unless(! $canShop || request()->routeIs('checkout.*') || request()->routeIs('cart.*') || View::hasSection('hideFloatingCart'))
        <aside data-floating-cart class="lg:hidden fixed bottom-20 inset-x-0 px-margin z-40 pointer-events-none {{ $cartCount && $cartPreview ? '' : 'hidden' }}">
            <div class="pointer-events-auto bg-on-surface text-surface rounded-full shadow-[0_8px_24px_rgba(0,0,0,0.2)] p-2 pr-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="relative w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-secondary text-on-secondary rounded-full text-[10px] font-bold flex items-center justify-center" data-cart-count>{{ $cartCount ?: 0 }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-label-sm font-label-sm text-surface font-bold">سلّة الطلب الحالية</span>
                        <span class="text-[11px] text-surface-container-highest" data-floating-cart-meta>{{ $cartPreview['restaurant']->name ?? 'سلتك' }} • {{ number_format($cartPreview['total'] ?? 0, 0) }} شيكل</span>
                    </div>
                </div>
                <a href="{{ route('cart.index') }}" class="bg-primary hover:bg-primary-container text-on-primary px-4 py-2 rounded-full text-label-sm font-label-sm font-bold flex items-center gap-1 shadow-md">
                    <span>إتمام الطلب</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                </a>
            </div>
        </aside>
    @endunless

    <footer id="site-footer" class="sg-footer hidden lg:block w-full">
        <div class="sg-footer__shell">
            <div class="sg-footer__panel">
                <div class="sg-footer__grid">
                    <div>
                        <a href="{{ route('home') }}" class="sg-footer__brand-row">
                            <img alt="شعار سفرة غزة" src="{{ $logoSrc }}">
                        </a>
                        <p class="sg-footer__blurb">
                            منصة الضيافة وتوصيل الطعام الأولى في قطاع غزة. كل احتياجاتك في مكان واحد، بجودة وسرعة ومكافآت مع كل طلب.
                        </p>
                        <div class="sg-footer__socials">
                            <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="sg-footer__social" aria-label="فيسبوك" title="فيسبوك">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3V6h-3c-1.7 0-3 1.3-3 3v2H8v3h3v7h3v-7h2.6l.4-3H14V9z"/></svg>
                            </a>
                            <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="sg-footer__social" aria-label="إنستغرام" title="إنستغرام">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.8" fill="currentColor" stroke="none"/></svg>
                            </a>
                            <a href="https://wa.me/972590000000" target="_blank" rel="noopener noreferrer" class="sg-footer__social" aria-label="تواصل عبر واتساب" title="تواصل عبر واتساب">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.48 14.19c-.23.64-.94 1.17-1.54 1.24-.4.05-.91.08-1.48-.09-.35-.1-.79-.24-1.36-.47-2.39-1.03-3.94-3.44-4.06-3.6-.13-.16-.99-1.31-.99-2.5 0-1.18.61-1.76.83-2 .22-.24.48-.3.64-.3h.46c.15 0 .35-.06.54.41.2.49.68 1.66.74 1.78.06.12.1.26.02.42-.08.16-.12.26-.24.4-.12.13-.25.3-.36.4-.12.12-.24.25-.1.49.14.24.62 1.02 1.33 1.65.91.81 1.68 1.06 1.92 1.18.24.12.38.1.52-.06.14-.16.59-.69.75-.92.16-.24.31-.2.52-.12.21.08 1.34.63 1.57.75.23.12.38.17.44.27.05.1.05.58-.18 1.22z"/></svg>
                            </a>
                            <a href="tel:0599000000" class="sg-footer__social" aria-label="اتصال هاتفي" title="اتصال هاتفي">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13.5l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>
                            </a>
                        </div>
                    </div>

                    <div>
                        <h4 class="sg-footer__col-title">الدعم</h4>
                        <ul class="sg-footer__col-list">
                            <li><a href="{{ $accountHomeUrl }}">{{ auth()->check() ? 'حسابي' : 'تسجيل الدخول' }}</a></li>
                            @if($canShop)
                                <li><a href="{{ route('account.orders') }}">طلباتي</a></li>
                                <li><a href="{{ route('redeem.create') }}">استبدال النقاط</a></li>
                                <li><a href="{{ route('account.wallet') }}">المحفظة</a></li>
                            @endif
                        </ul>
                    </div>

                    <div>
                        <h4 class="sg-footer__col-title">قائمتنا</h4>
                        <ul class="sg-footer__col-list">
                            @if($canShop)
                                <li><a href="{{ route('memberships.index') }}">العروض</a></li>
                            @endif
                            <li><a href="{{ route('restaurants.index') }}">الأشهر</a></li>
                            <li><a href="{{ route('restaurants.index') }}">التصنيفات</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="sg-footer__col-title">روابط مفيدة</h4>
                        <ul class="sg-footer__col-list">
                            <li><a href="{{ route('partner.register') }}">انضم كشريك مطعم</a></li>
                            <li><a href="{{ route('courier.register') }}">انضم كمندوب توصيل</a></li>
                            <li><a href="{{ route('home') }}">الشروط والأحكام</a></li>
                            <li><a href="{{ route('home') }}#why-us">عن سفرة غزة</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="sg-footer__col-title">تواصل معنا</h4>
                        <ul class="sg-footer__col-list">
                            <li><a href="mailto:support@sofragaza.com">support@sofragaza.com</a></li>
                            <li><span>غزة، فلسطين</span></li>
                        </ul>
                    </div>
                </div>

                <div class="sg-footer__bottom">
                    <span class="sg-footer__copy">حقوق النشر © {{ date('Y') }} سفرة غزة</span>
                    <div class="sg-footer__pays" aria-label="طرق الدفع المعتمدة">
                        <img src="{{ asset('images/payments/palpay.png') }}" alt="محفظتي PalPay" title="محفظتي PalPay">
                        <img src="{{ asset('images/payments/jawwalpay.png') }}" alt="جوال باي" title="جوال باي">
                        <img src="{{ asset('images/payments/bop.png') }}" alt="بنك فلسطين" title="بنك فلسطين">
                        <img src="{{ asset('images/payments/iburaq.png') }}" alt="بُراق" title="بُراق">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- Floating WhatsApp button (Mobile only) --}}
    @unless(request()->routeIs('checkout.*') || request()->routeIs('cart.*') || View::hasSection('hideWhatsApp'))
    @php
        $hasActiveCart = ($cartCount ?? 0) > 0;
    @endphp
    <a id="floating-whatsapp-btn" href="https://wa.me/972590000000" target="_blank" rel="noopener noreferrer" aria-label="تواصل عبر واتساب" class="lg:hidden fixed {{ $hasActiveCart ? 'bottom-[152px]' : 'bottom-[74px]' }} left-3.5 z-40 w-12 h-12 bg-[#25D366] text-white rounded-full flex items-center justify-center shadow-[0_4px_16px_rgba(37,211,102,0.4)] hover:scale-105 active:scale-95 transition-all duration-300" title="تواصل عبر واتساب">
        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm.01 18.09c-1.49 0-2.95-.4-4.23-1.16l-.3-.18-3.12.82.83-3.04-.2-.31a8.136 8.136 0 0 1-1.26-4.31c0-4.5 3.66-8.16 8.16-8.16 2.18 0 4.23.85 5.77 2.39 1.54 1.54 2.39 3.59 2.39 5.77 0 4.5-3.66 8.17-8.17 8.17zm4.47-6.1c-.25-.13-1.47-.72-1.7-.81-.23-.09-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.53.07-.25-.13-1.04-.38-1.98-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.13-.14.17-.23.25-.39.09-.16.04-.3-.02-.43-.07-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.16 0-.43.06-.66.3-.23.25-.87.85-.87 2.08 0 1.22.89 2.41 1.02 2.57.13.17 1.76 2.68 4.26 3.76.6.26 1.06.41 1.42.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.14-1.18-.06-.11-.23-.17-.48-.29z"/>
        </svg>
    </a>
    @endunless

    {{-- Native App Style Bottom Navigation Bar (Mobile only) --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 w-full z-50 pb-safe bg-white/95 backdrop-blur-xl border-t border-stone-200/80 shadow-[0_-2px_12px_rgba(0,0,0,0.06)]">
        <div class="grid {{ $canShop ? 'grid-cols-4' : 'grid-cols-3' }} h-16 items-center px-1">
            <a class="flex flex-col items-center justify-center min-w-0 h-full gap-0.5 transition-colors {{ request()->routeIs('home') ? 'text-amber-500 font-bold' : 'text-stone-400 hover:text-stone-600' }}" href="{{ route('home') }}">
                <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('home') ? 'fill-1' : '' }}">home</span>
                <span class="text-[11px] font-bold">الرئيسية</span>
            </a>
            @if($canShop)
                <a class="flex flex-col items-center justify-center min-w-0 h-full gap-0.5 transition-colors relative {{ request()->routeIs('cart.*') ? 'text-amber-500 font-bold' : 'text-stone-400 hover:text-stone-600' }}" href="{{ route('cart.index') }}">
                    <span class="relative">
                        <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('cart.*') ? 'fill-1' : '' }}">shopping_bag</span>
                        <span class="header-badge absolute -top-1.5 -right-2 bg-primary text-white text-[10px] font-extrabold w-4 h-4 rounded-full flex items-center justify-center" data-header-cart-badge @if(! $cartCount) hidden @endif>{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                    </span>
                    <span class="text-[11px] font-bold">السلة</span>
                </a>
                <a class="flex flex-col items-center justify-center min-w-0 h-full gap-0.5 transition-colors {{ request()->routeIs('account.orders*') ? 'text-amber-500 font-bold' : 'text-stone-400 hover:text-stone-600' }}" href="{{ auth()->check() ? route('account.orders') : route('login') }}">
                    <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('account.orders*') ? 'fill-1' : '' }}">receipt_long</span>
                    <span class="text-[11px] font-bold">طلباتي</span>
                </a>
                <a class="flex flex-col items-center justify-center min-w-0 h-full gap-0.5 transition-colors {{ request()->routeIs('account.*') && !request()->routeIs('account.orders*') ? 'text-amber-500 font-bold' : 'text-stone-400 hover:text-stone-600' }}" href="{{ $accountHomeUrl }}">
                    <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('account.*') && !request()->routeIs('account.orders*') ? 'fill-1' : '' }}">person</span>
                    <span class="text-[11px] font-bold">حسابي</span>
                </a>
            @else
                <a class="flex flex-col items-center justify-center min-w-0 h-full gap-0.5 transition-colors {{ request()->routeIs('restaurants.*') ? 'text-amber-500 font-bold' : 'text-stone-400 hover:text-stone-600' }}" href="{{ route('restaurants.index') }}">
                    <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('restaurants.*') ? 'fill-1' : '' }}">storefront</span>
                    <span class="text-[11px] font-bold">المطاعم</span>
                </a>
                <a class="flex flex-col items-center justify-center min-w-0 h-full gap-0.5 transition-colors text-stone-400 hover:text-stone-600" href="{{ $accountHomeUrl }}">
                    <span class="material-symbols-outlined text-[24px]">dashboard</span>
                    <span class="text-[11px] font-bold">{{ auth()->user()?->isAdmin() ? 'لوحة التحكم' : 'لوحتي' }}</span>
                </a>
            @endif
        </div>
    </nav>
    <script>
    (function() {
        const header = document.getElementById('site-header-desktop');
        if (!header) return;
        const onScroll = () => {
            if (window.innerWidth >= 1024 && window.scrollY > 60) {
                header.classList.add('is-scrolled');
            } else {
                header.classList.remove('is-scrolled');
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });
        onScroll();
    })();
    </script>
    @yield('scripts')
</body>
</html>
