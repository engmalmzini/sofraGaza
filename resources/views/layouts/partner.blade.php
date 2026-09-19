<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة المطعم') — سفرة غزة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $partnerRestaurant = auth()->user()->ownedRestaurant;
    $pendingOrdersCount = $partnerRestaurant?->orders()->where('status', 'pending_confirmation')->count() ?? 0;
    $navGroups = [
        [
            'label' => 'المطعم',
            'items' => [
                ['route' => 'partner.dashboard', 'icon' => 'dashboard', 'label' => 'نظرة عامة', 'match' => 'partner.dashboard'],
                ['route' => 'partner.restaurant.edit', 'icon' => 'storefront', 'label' => 'بيانات المطعم', 'match' => 'partner.restaurant.*'],
                ['route' => 'partner.menu-items.index', 'icon' => 'restaurant_menu', 'label' => 'المنيو والأطباق', 'match' => 'partner.menu-items.*'],
            ],
        ],
        [
            'label' => 'التشغيل',
            'items' => [
                ['route' => 'partner.orders.index', 'icon' => 'receipt_long', 'label' => 'الطلبات', 'match' => 'partner.orders.*', 'badge' => $pendingOrdersCount],
                ['route' => 'partner.notifications.index', 'icon' => 'notifications', 'label' => 'الإشعارات', 'match' => 'partner.notifications.*', 'badge' => $unreadNotifications],
            ],
        ],
    ];
@endphp
<body class="admin-app">
    <div class="admin-bubbles" aria-hidden="true">
        <span class="admin-bubble" style="--s: 22rem; --x: 8%; --y: 12%; --d: 22s; --a: 0s;"></span>
        <span class="admin-bubble" style="--s: 14rem; --x: 78%; --y: 8%; --d: 18s; --a: -4s;"></span>
        <span class="admin-bubble" style="--s: 9rem; --x: 62%; --y: 58%; --d: 16s; --a: -8s;"></span>
        <span class="admin-bubble" style="--s: 18rem; --x: 88%; --y: 72%; --d: 24s; --a: -2s;"></span>
        <span class="admin-bubble" style="--s: 7rem; --x: 18%; --y: 68%; --d: 14s; --a: -6s;"></span>
    </div>
    <div class="admin-scrim" id="admin-scrim" hidden></div>
    <aside class="admin-sidebar admin-glass" id="admin-sidebar">
        <a href="{{ route('partner.dashboard') }}" class="admin-brand">
            <img src="{{ $logoSrc }}" alt="سفرة غزة">
            <span>
                <small>لوحة المطعم</small>
            </span>
        </a>
        <nav class="admin-nav">
            @foreach($navGroups as $group)
                <p class="admin-nav__label">{{ $group['label'] }}</p>
                @foreach($group['items'] as $item)
                    <a href="{{ route($item['route']) }}" class="admin-nav__item {{ request()->routeIs($item['match']) ? 'is-active' : '' }}">
                        <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                        @if(!empty($item['badge']))
                            <em>{{ $item['badge'] > 9 ? '9+' : $item['badge'] }}</em>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>
        <a href="{{ route('home') }}" class="admin-nav__item admin-nav__site">
            <span class="material-symbols-outlined">storefront</span>
            <span>عودة للموقع</span>
        </a>
    </aside>
    <div class="admin-shell">
        <header class="admin-topbar">
            <button type="button" class="admin-icon-btn admin-menu-btn" id="admin-menu-btn" aria-label="القائمة">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <div class="admin-topbar__tools">
            <form class="admin-topbar__search" action="{{ route('partner.menu-items.index') }}" method="GET" role="search">
                <input name="q" value="{{ request()->routeIs('partner.menu-items.*') ? request('q') : '' }}" placeholder="ابحث في أصناف المنيو" type="search" autocomplete="off" aria-label="بحث">
                <button type="submit" aria-label="بحث">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </form>
            <div class="admin-topbar__actions">
                <a href="{{ route('partner.notifications.index') }}" class="admin-topbar__icon" title="الإشعارات">
                    <span class="material-symbols-outlined">notifications</span>
                    @if($unreadNotifications)
                        <em>{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</em>
                    @endif
                </a>
                <details class="admin-topbar__user">
                    <summary class="admin-topbar__icon" title="{{ auth()->user()->name }}" aria-label="الحساب">
                        <span class="material-symbols-outlined">account_circle</span>
                    </summary>
                    <div class="admin-topbar__menu">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>{{ $partnerRestaurant?->name ?? 'صاحب مطعم' }}</small>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">خروج</button>
                        </form>
                    </div>
                </details>
            </div>
            </div>
        </header>
        <main class="admin-main">
            <div class="admin-pagehead">
                <h1>@yield('title', 'لوحة المطعم')</h1>
                <div class="admin-pagehead__actions">@yield('actions')</div>
            </div>
            @if(session('success'))
                <div class="admin-alert admin-alert--ok">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="admin-alert admin-alert--err">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="admin-alert admin-alert--err">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
