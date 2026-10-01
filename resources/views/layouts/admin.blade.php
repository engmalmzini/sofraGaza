<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — سفرة غزة</title>
    @include('partials.icon-font')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $pendingOrdersCount = \App\Models\Order::query()->where('status', 'pending_confirmation')->count();
    $pendingCourierCount = \App\Models\User::query()->where('role', 'courier')->where('courier_status', \App\Models\User::COURIER_PENDING)->count();
    $waitingDeliveryCount = \App\Models\Order::query()->whereNull('courier_id')->whereIn('status', ['preparing', 'delivering'])->count() + $pendingCourierCount;
    $pendingSubsCount = \App\Models\MembershipSubscription::query()->where('status', 'pending')->count();
    $pendingRestaurantsCount = \App\Models\Restaurant::query()->pendingVerification()->count();
    $pendingTopupsCount = \App\Models\WalletTopup::query()->where('status', 'pending')->count();
    $pendingBoostsCount = \App\Models\RestaurantBoost::query()->pending()->count();
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
    } elseif (request()->routeIs('admin.finance.*')) {
        $adminSearch = [
            'scope' => 'finance',
            'action' => route('admin.finance.index'),
            'placeholder' => 'ابحث في المالية والتسويات',
            'restaurant_id' => null,
            'filter' => false,
        ];
    } elseif (request()->routeIs('admin.boosts.*')) {
        $adminSearch = [
            'scope' => 'finance',
            'action' => route('admin.boosts.index'),
            'placeholder' => 'ابحث في إعلانات المطاعم',
            'restaurant_id' => null,
            'filter' => false,
        ];
    } elseif (request()->routeIs('admin.homepage.*')) {
        $adminSearch = [
            'scope' => 'homepage',
            'action' => route('admin.homepage.index'),
            'placeholder' => 'ابحث في محتوى الرئيسية',
            'restaurant_id' => null,
            'filter' => false,
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
    $adminViewer = auth()->user();
    $navGroups = [
        [
            'label' => 'الرئيسية',
            'items' => [
                ['route' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => 'نظرة عامة', 'match' => 'admin.dashboard', 'module' => 'dashboard'],
                ['route' => 'admin.notifications.index', 'icon' => 'notifications', 'label' => 'الإشعارات', 'match' => 'admin.notifications.*', 'badge' => $unreadNotifications, 'module' => 'dashboard'],
            ],
        ],
        [
            'label' => 'التشغيل',
            'items' => [
                ['route' => 'admin.orders.index', 'icon' => 'receipt_long', 'label' => 'الطلبات', 'match' => 'admin.orders.*', 'badge' => $pendingOrdersCount, 'module' => 'orders'],
                ['route' => 'admin.delivery.index', 'icon' => 'moped', 'label' => 'التوصيل والمندوبون', 'match' => 'admin.delivery.*', 'badge' => $waitingDeliveryCount, 'module' => 'delivery'],
                ['route' => 'admin.restaurants.index', 'icon' => 'storefront', 'label' => 'المطاعم والكوفيهات', 'match' => 'admin.restaurants.*', 'badge' => $pendingRestaurantsCount, 'module' => 'restaurants'],
            ],
        ],
        [
            'label' => 'المالية',
            'items' => [
                ['route' => 'admin.finance.index', 'icon' => 'account_balance', 'label' => 'نظرة عامة', 'match' => 'admin.finance.index', 'module' => 'finance'],
                ['route' => 'admin.finance.orders', 'icon' => 'receipt_long', 'label' => 'مالية الطلبات', 'match' => 'admin.finance.orders', 'module' => 'finance'],
                ['route' => 'admin.finance.restaurants', 'icon' => 'payments', 'label' => 'مالية المطاعم', 'match' => 'admin.finance.restaurants*', 'module' => 'finance'],
                ['route' => 'admin.boosts.index', 'icon' => 'campaign', 'label' => 'إعلانات المطاعم', 'match' => 'admin.boosts.*', 'badge' => $pendingBoostsCount, 'module' => 'finance'],
            ],
        ],
        [
            'label' => 'الزبائن والمحفظة',
            'items' => [
                ['route' => 'admin.users.index', 'icon' => 'group', 'label' => 'الزبائن والنقاط', 'match' => 'admin.users.*', 'module' => 'customers'],
                ['route' => 'admin.wallet-topups.index', 'icon' => 'account_balance_wallet', 'label' => 'شحن الرصيد', 'match' => 'admin.wallet-topups.*', 'badge' => $pendingTopupsCount, 'module' => 'customers'],
                ['route' => 'admin.memberships.index', 'icon' => 'workspace_premium', 'label' => 'العضويات', 'match' => 'admin.memberships.*', 'module' => 'memberships'],
                ['route' => 'admin.subscriptions.index', 'icon' => 'verified', 'label' => 'الاشتراكات', 'match' => 'admin.subscriptions.*', 'badge' => $pendingSubsCount, 'module' => 'memberships'],
                ['route' => 'admin.reviews.index', 'icon' => 'star', 'label' => 'التقييمات والآراء', 'match' => 'admin.reviews.*', 'module' => 'reviews'],
                ['route' => 'admin.coupons.index', 'icon' => 'local_offer', 'label' => 'أكواد الخصم', 'match' => 'admin.coupons.*', 'module' => 'coupons'],
            ],
        ],
        [
            'label' => 'النظام',
            'items' => [
                ['route' => 'admin.team.index', 'icon' => 'manage_accounts', 'label' => 'فريق الإدارة', 'match' => 'admin.team.*', 'module' => 'team'],
                ['route' => 'admin.audit.index', 'icon' => 'history', 'label' => 'سجل التعديلات', 'match' => 'admin.audit.*', 'module' => 'audit'],
                ['route' => 'admin.homepage.index', 'icon' => 'home_app_logo', 'label' => 'محتوى الرئيسية', 'match' => 'admin.homepage.*', 'module' => 'homepage'],
                ['route' => 'admin.settings.index', 'icon' => 'settings', 'label' => 'الإعدادات', 'match' => 'admin.settings.*', 'module' => 'settings'],
            ],
        ],
    ];
    $navGroups = array_values(array_filter(array_map(function (array $group) use ($adminViewer) {
        $items = array_values(array_filter(
            $group['items'],
            fn (array $item) => $adminViewer?->canAccessAdmin($item['module'] ?? 'dashboard')
        ));

        if ($items === []) {
            return null;
        }

        $group['items'] = $items;

        return $group;
    }, $navGroups)));
@endphp
<body class="admin-app">
    <div class="admin-scrim" id="admin-scrim" hidden></div>
    <aside class="admin-sidebar" id="admin-sidebar">
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
                        class="!outline-none !ring-0 !border-0 focus:!outline-none focus:!ring-0 focus:!border-0"
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
                @if(auth()->user()->canAccessAdmin('orders'))
                    <button type="button" class="admin-topbar__icon js-order-sound-btn" title="اختبار نغمة تنبيه الطلبات (تزمير)">
                        <span class="material-symbols-outlined">volume_up</span>
                    </button>
                @endif
                <details class="admin-topbar__user">
                    <summary class="admin-topbar__icon" title="{{ auth()->user()->name }}" aria-label="الحساب">
                        <span class="material-symbols-outlined">account_circle</span>
                    </summary>
                    <div class="admin-topbar__menu">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>{{ auth()->user()->adminRoleLabel() }}</small>
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
    @include('partials.live-order-sound-and-polling')
</body>
</html>
