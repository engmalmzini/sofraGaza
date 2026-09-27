<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'سفرة غزة') — سفرة غزة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
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
    </style>
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $pointsBalance = auth()->user()->points_balance ?? 0;
    $walletBalance = auth()->user()->wallet_balance ?? 0;
@endphp
<body class="bg-surface font-body-md text-body-md text-on-surface antialiased @yield('body_class')">
    <header id="site-header" class="site-header">
        <div class="site-header__bar">
            <div class="site-header__start">
                <a href="{{ route('home') }}" class="site-header__brand">
                    <img alt="شعار سفرة غزة" src="{{ $logoSrc }}">
                </a>

                <div class="site-header__location">
                    @include('partials.area-picker', ['variant' => 'stacked'])
                </div>
            </div>

            <nav class="site-header__nav" aria-label="التنقل الرئيسي">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">الرئيسية</a>
                <a href="{{ route('restaurants.index') }}" class="{{ request()->routeIs('restaurants.*') ? 'is-active' : '' }}">المطاعم</a>
                <a href="{{ route('memberships.index') }}" class="{{ request()->routeIs('memberships.*') ? 'is-active' : '' }}">المكافآت</a>
            </nav>

            <div class="site-header__actions">
                @auth
                    <a href="{{ route('account.wallet') }}" class="flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/60 transition-colors shrink-0" title="رصيد المحفظة">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600">account_balance_wallet</span>
                        <span class="font-mono text-xs">{{ number_format($walletBalance, 0) }}</span>
                        <span class="ils text-[10px]">₪</span>
                    </a>
                    <a href="{{ route('account.points') }}" class="header-points shrink-0" title="نقاط الولاء">
                        <span class="material-symbols-outlined fill-1">stars</span>
                        <span>{{ $pointsBalance }}</span>
                    </a>
                @endauth

                @include('partials.header-search', ['class' => 'site-header__search'])

                <div class="header-session">
                    <a href="{{ route('cart.index') }}" class="header-icon-btn" aria-label="السلة" data-header-cart>
                        <span class="material-symbols-outlined">shopping_bag</span>
                        <span class="header-badge" data-header-cart-badge @if(! $cartCount) hidden @endif>{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                    </a>

                    @auth
                        @php
                            $headerTier = auth()->user()->tier();
                        @endphp
                        <a href="{{ route('account.show') }}" class="header-account" title="حسابي • مستوى الولاء: {{ $headerTier['name'] }}">
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
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="header-icon-btn header-icon-btn--desktop" title="لوحة التحكم">
                                <span class="material-symbols-outlined">admin_panel_settings</span>
                            </a>
                        @elseif(auth()->user()->isRestaurantOwner())
                            <a href="{{ auth()->user()->partnerPanelRoute() }}" class="header-icon-btn header-icon-btn--desktop" title="لوحة المطعم">
                                <span class="material-symbols-outlined">storefront</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="header-cta">
                            <span class="lg:hidden">دخول</span>
                            <span class="hidden lg:inline">تسجيل الدخول</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <div class="site-header__mobile-search">
            @include('partials.header-search')
        </div>
    </header>

    <main class="w-full bg-surface pb-28 lg:pb-0 site-main">
        @yield('content')
    </main>

    <div id="app-toast" class="app-toast" hidden role="status" aria-live="polite" data-success="{{ session('success') }}" data-error="{{ session('error') ?: $errors->first() }}">
        <span class="app-toast__icon material-symbols-outlined fill-1">check_circle</span>
        <p class="app-toast__text"></p>
    </div>

    @unless(View::hasSection('hideFloatingCart'))
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

    <footer class="w-full bg-surface-container-lowest border-t border-surface-container-high pt-12 pb-24 lg:pb-12">
        <div class="max-w-7xl mx-auto px-margin lg:px-margin-desktop">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 pb-10">
                {{-- Column 1: Brand & Contact --}}
                <div class="space-y-3">
                    <img alt="شعار سفرة غزة" class="h-9 w-auto object-contain" src="{{ $logoSrc }}">
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        منصة الضيافة وتوصيل الطعام الأولى في قطاع غزة. كل احتياجاتك في مكان واحد، بجودة وسرعة ومكافآت مع كل طلب.
                    </p>
                    <div class="flex items-center gap-4 pt-2 text-on-surface-variant">
                        <a href="tel:0599000000" class="hover:text-primary transition-colors inline-flex items-center justify-center" title="اتصال هاتفي">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"/>
                            </svg>
                        </a>
                        <a href="https://wa.me/972590000000" target="_blank" class="hover:text-[#25D366] transition-colors inline-flex items-center justify-center" title="تواصل عبر واتساب">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                            </svg>
                        </a>
                        <a href="mailto:support@sofragaza.com" class="hover:text-primary transition-colors inline-flex items-center justify-center" title="البريد الإلكتروني">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect width="18" height="14" x="3" y="5" rx="2" />
                                <path d="m3 7 9 6 9-6" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Column 2: Delivery Areas (Clean list, NO cards) --}}
                <div class="space-y-3">
                    <h4 class="text-sm font-extrabold text-on-surface">مناطق التوصيل الفعّالة</h4>
                    <ul class="space-y-2 text-xs text-on-surface-variant leading-relaxed">
                        <li class="hover:text-on-surface transition-colors">حي الرمال الجنوبي والشمالي</li>
                        <li class="hover:text-on-surface transition-colors">منطقة النصر وتل الهوى</li>
                        <li class="hover:text-on-surface transition-colors">المحافظة الوسطى (النصيرات ودير البلح)</li>
                        <li class="hover:text-on-surface transition-colors">خانيونس والمواصي</li>
                        <li class="hover:text-on-surface transition-colors">مخيم جباليا والشيخ رضوان</li>
                    </ul>
                </div>

                {{-- Column 3: Payment Methods & Icons --}}
                <div class="space-y-3">
                    <h4 class="text-sm font-extrabold text-on-surface">طرق الدفع المعتمدة</h4>
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        وسائل دفع ومحافظ إلكترونية موثوقة في قطاع غزة:
                    </p>

                    <div class="flex items-center gap-2.5 flex-wrap pt-1">
                        <img src="{{ asset('images/payments/palpay.png') }}" alt="PalPay" title="محفظتي PalPay" class="w-9 h-9 rounded-xl object-contain shadow-2xs hover:scale-110 transition-transform">
                        <img src="{{ asset('images/payments/jawwalpay.png') }}" alt="Jawwal Pay" title="جوال باي Jawwal Pay" class="w-9 h-9 rounded-xl object-contain shadow-2xs hover:scale-110 transition-transform">
                        <img src="{{ asset('images/payments/bop.png') }}" alt="Bank of Palestine" title="بنك فلسطين Bank of Palestine" class="w-9 h-9 rounded-xl object-contain shadow-2xs hover:scale-110 transition-transform">
                        <img src="{{ asset('images/payments/iburaq.png') }}" alt="iBuraq" title="بُراق iBURAQ" class="w-9 h-9 rounded-xl object-contain shadow-2xs hover:scale-110 transition-transform">
                    </div>

                    <p class="text-[11px] text-on-surface-variant pt-0.5">
                        إلى جانب الدفع نقداً (كاش) عند الاستلام ومحفظة سفرة غزة.
                    </p>
                </div>

                {{-- Column 4: Quick Links (Clean text links) --}}
                <div class="space-y-3">
                    <h4 class="text-sm font-extrabold text-on-surface">مركز المساعدة والدعم</h4>
                    <ul class="space-y-2 text-xs text-on-surface-variant">
                        <li>
                            <a class="hover:text-primary transition-colors inline-block" href="{{ route('memberships.index') }}">العضويات والمكافآت</a>
                        </li>
                        <li>
                            <a class="hover:text-primary transition-colors inline-block" href="{{ route('partner.register') }}">انضم كشريك مطعم</a>
                        </li>
                        <li>
                            <a class="hover:text-primary transition-colors inline-block" href="{{ route('courier.register') }}">انضم كمندوب توصيل</a>
                        </li>
                        <li>
                            <a class="hover:text-primary transition-colors inline-block" href="{{ route('redeem.create') }}">استبدال نقاط المكافآت</a>
                        </li>
                        <li>
                            <a class="hover:text-primary transition-colors inline-block" href="{{ auth()->check() ? route('account.show') : route('login') }}">
                                {{ auth()->check() ? 'لوحة حسابي ومحفظتي' : 'تسجيل الدخول / إنشاء حساب' }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Bottom Footer Bar --}}
            <div class="pt-6 border-t border-surface-container-high flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-on-surface-variant">
                <div class="flex items-center gap-2 flex-wrap text-center md:text-right">
                    <span>© {{ date('Y') }} سفرة غزة | Sofra Gaza. جميع الحقوق محفوظة لقطاع الضيافة في غزة.</span>
                    <span>•</span>
                    <span>صُنع بكل فخر لأهلنا في غزة 🇵🇸</span>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <a class="hover:text-primary transition-colors" href="{{ route('home') }}">سياسة الاسترجاع والضمان</a>
                    <span>•</span>
                    <a class="hover:text-primary transition-colors" href="{{ route('home') }}">الشروط والأحكام</a>
                    <span>•</span>
                    <a class="hover:text-primary transition-colors" href="{{ route('account.show') }}">أمان الحساب والمحفظة</a>
                </div>
            </div>
        </div>
    </footer>

    <nav class="lg:hidden fixed bottom-0 w-full z-50 pb-safe bg-surface/90 backdrop-blur-xl shadow-[0_-2px_12px_rgba(0,0,0,0.05)]">
        <div class="flex items-center justify-around h-16 px-space-xs">
            <a class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] gap-0.5 transition-colors {{ request()->routeIs('home') ? 'text-primary font-bold' : 'text-on-surface-variant' }}" href="{{ route('home') }}">
                <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('home') ? 'fill-1' : '' }}">home</span>
                <span class="text-label-sm font-label-sm">الرئيسية</span>
            </a>
            <a class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] gap-0.5 transition-colors {{ request()->routeIs('restaurants.*') ? 'text-primary font-bold' : 'text-on-surface-variant' }}" href="{{ route('restaurants.index') }}">
                <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('restaurants.*') ? 'fill-1' : '' }}">restaurant</span>
                <span class="text-label-sm font-label-sm">المطاعم</span>
            </a>
            <a class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] gap-0.5 transition-colors {{ request()->routeIs('memberships.*', 'redeem.*') ? 'text-primary font-bold' : 'text-on-surface-variant' }}" href="{{ route('memberships.index') }}">
                <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('memberships.*', 'redeem.*') ? 'fill-1' : '' }}">military_tech</span>
                <span class="text-label-sm font-label-sm">المكافآت</span>
            </a>
            <a class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] gap-0.5 transition-colors {{ request()->routeIs('account.orders*') ? 'text-primary font-bold' : 'text-on-surface-variant' }}" href="{{ auth()->check() ? route('account.orders') : route('login') }}">
                <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('account.orders*') ? 'fill-1' : '' }}">receipt_long</span>
                <span class="text-label-sm font-label-sm">طلباتي</span>
            </a>
            <a class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] gap-0.5 transition-colors {{ request()->routeIs('account.show', 'account.points', 'account.addresses', 'account.notifications') ? 'text-primary font-bold' : 'text-on-surface-variant' }}" href="{{ auth()->check() ? route('account.show') : route('login') }}">
                <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('account.show', 'account.points', 'account.addresses', 'account.notifications') ? 'fill-1' : '' }}">account_circle</span>
                <span class="text-label-sm font-label-sm">حسابي</span>
            </a>
        </div>
    </nav>
    @yield('scripts')
</body>
</html>
