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
                    <a href="{{ route('account.points') }}" class="header-points" title="نقاط الولاء">
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
                        <a href="{{ route('account.show') }}" class="header-account">
                            <span class="header-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                            <span class="header-account__name">{{ explode(' ', auth()->user()->name)[0] }}</span>
                        </a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="header-icon-btn header-icon-btn--desktop" title="لوحة التحكم">
                                <span class="material-symbols-outlined">admin_panel_settings</span>
                            </a>
                        @elseif(auth()->user()->isRestaurantOwner())
                            <a href="{{ route('partner.dashboard') }}" class="header-icon-btn header-icon-btn--desktop" title="لوحة المطعم">
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

    <footer class="hidden lg:block w-full bg-surface-container-lowest shadow-[0_1px_8px_rgba(0,0,0,0.04)] pt-space-xl pb-space-lg">
        <div class="max-w-7xl mx-auto px-margin-desktop">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-lg pb-space-xl">
                <div class="flex flex-col gap-space-sm">
                    <img alt="شعار سفرة غزة" class="sg-brand-footer" src="{{ $logoSrc }}">
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">كل احتياجاتك في غزة .. في مكان واحد. منصة الضيافة والمطاعم في قطاع غزة.</p>
                    <div class="flex items-center gap-space-sm pt-space-xs text-primary">
                        <span class="material-symbols-outlined">forum</span>
                        <span class="material-symbols-outlined">share</span>
                        <span class="material-symbols-outlined">mail</span>
                        <span class="material-symbols-outlined">call</span>
                    </div>
                </div>
                <div class="flex flex-col gap-space-sm">
                    <span class="font-headline-sm text-headline-sm text-on-surface">مناطق التوصيل الفعّالة</span>
                    <ul class="flex flex-col gap-space-xs font-body-sm text-body-sm text-on-surface-variant">
                        <li>حي الرمال الجنوبي والشمالي</li>
                        <li>منطقة النصر وتل الهوى</li>
                        <li>المحافظة الوسطى (النصيرات ودير البلح)</li>
                        <li>خانيونس والمواصي</li>
                        <li>مخيم جباليا والشيخ رضوان</li>
                    </ul>
                </div>
                <div class="flex flex-col gap-space-sm">
                    <span class="font-headline-sm text-headline-sm text-on-surface">المدفوعات والمحفظة</span>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">ندعم الدفع نقداً عند الاستلام ومحافظ الدفع الإلكتروني المعتمدة بنظام إشعارات تحويل فوري وموثوق.</p>
                    <div class="flex flex-wrap items-center gap-space-xs pt-space-xs">
                        <div class="px-space-sm py-space-xs rounded-lg bg-surface-container font-label-sm text-label-sm text-on-surface font-semibold">كاش عند الاستلام</div>
                        <div class="px-space-sm py-space-xs rounded-lg bg-surface-container font-label-sm text-label-sm text-on-surface font-semibold">حوالة بنكية</div>
                        <div class="px-space-sm py-space-xs rounded-lg bg-surface-container font-label-sm text-label-sm text-on-surface font-semibold">PalPay</div>
                    </div>
                </div>
                <div class="flex flex-col gap-space-sm">
                    <span class="font-headline-sm text-headline-sm text-on-surface">مركز المساعدة والدعم</span>
                    <ul class="flex flex-col gap-space-xs font-body-sm text-body-sm text-on-surface-variant">
                        <li><a class="hover:text-primary transition-colors" href="{{ route('memberships.index') }}">العضويات والمكافآت</a></li>
                        <li><a class="hover:text-primary transition-colors" href="{{ route('partner.register') }}">انضم كشريك مطعم</a></li>
                        <li><a class="hover:text-primary transition-colors" href="{{ route('courier.register') }}">انضم كمندوب توصيل</a></li>
                        <li><a class="hover:text-primary transition-colors" href="{{ route('register') }}">إنشاء حساب جديد</a></li>
                        <li><a class="hover:text-primary transition-colors" href="{{ route('login') }}">تسجيل الدخول</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-space-lg flex flex-col md:flex-row items-center justify-between gap-space-md text-on-surface-variant font-label-sm text-label-sm">
                <p>© {{ date('Y') }} سفرة غزة | Sofra Gaza. جميع الحقوق محفوظة لقطاع الضيافة في غزة.</p>
                <div class="flex items-center gap-space-md">
                    <a class="hover:text-primary transition-colors" href="{{ route('home') }}">سياسة الاسترجاع</a>
                    <a class="hover:text-primary transition-colors" href="{{ route('home') }}">شروط الخدمة</a>
                    <a class="hover:text-primary transition-colors" href="{{ route('account.show') }}">أمان الحساب</a>
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
