<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — سفرة غزة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $pendingOrdersCount = \App\Models\Order::query()->where('status', 'pending_confirmation')->count();
    $pendingCourierCount = \App\Models\User::query()->where('role', 'courier')->where('courier_status', \App\Models\User::COURIER_PENDING)->count();
    $waitingDeliveryCount = \App\Models\Order::query()->whereNull('courier_id')->whereIn('status', ['preparing', 'delivering'])->count() + $pendingCourierCount;
    $pendingSubsCount = \App\Models\MembershipSubscription::query()->where('status', 'pending')->count();
    $pendingListingCount = \App\Models\RestaurantSubscription::query()->where('status', 'pending')->count();
    $pendingRestaurantsCount = \App\Models\Restaurant::query()->pendingVerification()->count() + $pendingListingCount;
    $adminSearch = [
        'scope' => 'global',
        'action' => route('admin.dashboard'),
        'placeholder' => 'ابحث في الطلبات والمطاعم والزبائن',
        'restaurant_id' => null,
        'filter' => false,
    ];
    if (request()->routeIs('admin.orders.*')) {
        $adminSearch = [
            'scope' => 'orders',
            'action' => route('admin.orders.index'),
            'placeholder' => 'ابحث برقم الطلب أو الاسم أو الهاتف',
            'restaurant_id' => null,
            'filter' => true,
        ];
    } elseif (request()->routeIs('admin.restaurants.menu-items.*')) {
        $menuRestaurant = request()->route('restaurant');
        $adminSearch = [
            'scope' => 'menu_items',
            'action' => $menuRestaurant ? route('admin.restaurants.menu-items.index', $menuRestaurant) : route('admin.restaurants.index'),
            'placeholder' => 'ابحث في أصناف المنيو',
            'restaurant_id' => $menuRestaurant?->id ?? $menuRestaurant,
            'filter' => true,
        ];
    } elseif (request()->routeIs('admin.restaurants.*')) {
        $adminSearch = [
            'scope' => 'restaurants',
            'action' => route('admin.restaurants.index'),
            'placeholder' => 'ابحث عن مطعم أو كوفي',
            'restaurant_id' => null,
            'filter' => true,
        ];
    } elseif (request()->routeIs('admin.listings.*')) {
        $adminSearch = [
            'scope' => 'listings',
            'action' => route('admin.listings.index'),
            'placeholder' => 'ابحث في اشتراكات المطاعم',
            'restaurant_id' => null,
            'filter' => true,
        ];
    } elseif (request()->routeIs('admin.users.*')) {
        $adminSearch = [
            'scope' => 'users',
            'action' => route('admin.users.index'),
            'placeholder' => 'ابحث بالاسم أو الهاتف',
            'restaurant_id' => null,
            'filter' => true,
        ];
    } elseif (request()->routeIs('admin.memberships.*')) {
        $adminSearch = [
            'scope' => 'memberships',
            'action' => route('admin.memberships.index'),
            'placeholder' => 'ابحث عن عضوية',
            'restaurant_id' => null,
            'filter' => true,
        ];
    } elseif (request()->routeIs('admin.subscriptions.*')) {
        $adminSearch = [
            'scope' => 'subscriptions',
            'action' => route('admin.subscriptions.index'),
            'placeholder' => 'ابحث في الاشتراكات',
            'restaurant_id' => null,
            'filter' => true,
        ];
    } elseif (request()->routeIs('admin.delivery.*')) {
        $adminSearch = [
            'scope' => 'couriers',
            'action' => route('admin.delivery.index'),
            'placeholder' => 'ابحث برقم الطلب أو اسم المندوب',
            'restaurant_id' => null,
            'filter' => true,
        ];
    } elseif (request()->routeIs('admin.settings.*')) {
        $adminSearch = [
            'scope' => 'settings',
            'action' => route('admin.settings.index'),
            'placeholder' => 'ابحث في الإعدادات',
            'restaurant_id' => null,
            'filter' => false,
        ];
    }
    $navGroups = [
        [
            'label' => 'الرئيسية',
            'items' => [
                ['route' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => 'نظرة عامة', 'match' => 'admin.dashboard'],
                ['route' => 'admin.notifications.index', 'icon' => 'notifications', 'label' => 'الإشعارات', 'match' => 'admin.notifications.*', 'badge' => $unreadNotifications],
            ],
        ],
        [
            'label' => 'التشغيل',
            'items' => [
                ['route' => 'admin.orders.index', 'icon' => 'receipt_long', 'label' => 'الطلبات', 'match' => 'admin.orders.*', 'badge' => $pendingOrdersCount],
                ['route' => 'admin.delivery.index', 'icon' => 'moped', 'label' => 'التوصيل والمندوبون', 'match' => 'admin.delivery.*', 'badge' => $waitingDeliveryCount],
                ['route' => 'admin.restaurants.index', 'icon' => 'storefront', 'label' => 'المطاعم والكوفيهات', 'match' => 'admin.restaurants.*', 'badge' => $pendingRestaurantsCount],
                ['route' => 'admin.listings.index', 'icon' => 'receipt_long', 'label' => 'اشتراكات المطاعم', 'match' => 'admin.listings.*', 'badge' => $pendingListingCount],
            ],
        ],
        [
            'label' => 'الزبائن',
            'items' => [
                ['route' => 'admin.users.index', 'icon' => 'group', 'label' => 'الزبائن والنقاط', 'match' => 'admin.users.*'],
                ['route' => 'admin.memberships.index', 'icon' => 'workspace_premium', 'label' => 'العضويات', 'match' => 'admin.memberships.*'],
                ['route' => 'admin.subscriptions.index', 'icon' => 'verified', 'label' => 'الاشتراكات', 'match' => 'admin.subscriptions.*', 'badge' => $pendingSubsCount],
            ],
        ],
        [
            'label' => 'النظام',
            'items' => [
                ['route' => 'admin.settings.index', 'icon' => 'settings', 'label' => 'الإعدادات', 'match' => 'admin.settings.*'],
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
        <span class="admin-bubble" style="--s: 5rem; --x: 42%; --y: 22%; --d: 12s; --a: -3s;"></span>
        <span class="admin-bubble" style="--s: 11rem; --x: 4%; --y: 42%; --d: 20s; --a: -9s;"></span>
        <span class="admin-bubble" style="--s: 4rem; --x: 52%; --y: 82%; --d: 11s; --a: -5s;"></span>
        <span class="admin-bubble" style="--s: 16rem; --x: 70%; --y: 34%; --d: 26s; --a: -7s;"></span>
        <span class="admin-bubble admin-bubble--tiny" style="--s: 2.2rem; --x: 30%; --y: 48%; --d: 9s; --a: -1s;"></span>
        <span class="admin-bubble admin-bubble--tiny" style="--s: 1.6rem; --x: 92%; --y: 28%; --d: 8s; --a: -4s;"></span>
        <span class="admin-bubble admin-bubble--tiny" style="--s: 2.8rem; --x: 48%; --y: 6%; --d: 10s; --a: -2s;"></span>
    </div>
    <div class="admin-scrim" id="admin-scrim" hidden></div>
    <aside class="admin-sidebar admin-glass" id="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <img src="{{ $logoSrc }}" alt="سفرة غزة">
            <span>
                <small>لوحة التحكم</small>
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
            <div
                class="admin-search-wrap"
                data-suggest-url="{{ route('admin.search.suggest') }}"
                data-scope="{{ $adminSearch['scope'] }}"
                @if($adminSearch['restaurant_id']) data-restaurant-id="{{ $adminSearch['restaurant_id'] }}" @endif
                data-submit="{{ $adminSearch['filter'] ? 'filter' : 'suggest' }}"
            >
                <form class="admin-topbar__search" action="{{ $adminSearch['action'] }}" method="GET" role="search">
                    @if($adminSearch['filter'] && request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if($adminSearch['filter'] && request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                    @endif
                    <input
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="{{ $adminSearch['placeholder'] }}"
                        type="search"
                        autocomplete="off"
                        aria-label="بحث"
                        aria-autocomplete="list"
                    >
                    <button type="submit" aria-label="بحث">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </form>
                <div class="admin-suggest" hidden></div>
            </div>
            <div class="admin-topbar__actions">
                <a href="{{ route('admin.notifications.index') }}" class="admin-topbar__icon" title="الإشعارات">
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
                        <small>مدير المنصة</small>
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
                <h1>@yield('title', 'لوحة التحكم')</h1>
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
