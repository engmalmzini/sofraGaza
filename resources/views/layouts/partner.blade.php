<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $partnerRestaurant = auth()->user()->ownedRestaurant;
    $panelTitle = $partnerRestaurant?->panelTitle() ?? 'لوحة الشريك';
    $pendingPartnerOrdersCount = $partnerRestaurant ? $partnerRestaurant->orders()->whereIn('status', ['pending_confirmation', 'confirmed', 'preparing'])->count() : 0;
    $navGroups = [
        [
            'label' => $partnerRestaurant?->typeLabel() ?? 'المكان',
            'items' => [
                ['route' => 'partner.dashboard', 'icon' => 'dashboard', 'label' => 'نظرة عامة', 'match' => 'partner.dashboard'],
                ['route' => 'partner.orders.index', 'icon' => 'receipt_long', 'label' => 'الطلبات الواردة', 'match' => 'partner.orders.*', 'badge' => $pendingPartnerOrdersCount],
                ['route' => 'partner.restaurant.edit', 'icon' => 'storefront', 'label' => 'بيانات '.($partnerRestaurant?->venueNoun() ?? 'المكان'), 'match' => 'partner.restaurant.*'],
                ['route' => 'partner.boosts.index', 'icon' => 'campaign', 'label' => 'الإعلان والظهور الأول', 'match' => 'partner.boosts.*'],
                ['route' => 'partner.menu-items.index', 'icon' => 'restaurant_menu', 'label' => 'المنيو والتصنيفات', 'match' => 'partner.menu-items.*'],
            ],
        ],
        [
            'label' => 'الحساب',
            'items' => [
                ['route' => 'partner.cards.show', 'icon' => 'credit_card', 'label' => 'تحقق البطاقة', 'match' => 'partner.cards.*'],
                ['route' => 'partner.notifications.index', 'icon' => 'notifications', 'label' => 'الإشعارات', 'match' => 'partner.notifications.*', 'badge' => $unreadNotifications],
            ],
        ],
    ];
@endphp
    <title>@yield('title', $panelTitle) — سفرة غزة</title>
    @include('partials.icon-font')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app">
    <div class="admin-scrim" id="admin-scrim" hidden></div>
    <aside class="admin-sidebar" id="admin-sidebar">
        <a href="{{ route('partner.dashboard') }}" class="admin-brand">
            <img src="{{ $logoSrc }}" alt="سفرة غزة">
            <span>
                <small>{{ $panelTitle }}</small>
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
                <input name="q" value="{{ request()->routeIs('partner.menu-items.*') ? request('q') : '' }}" placeholder="ابحث في أصناف المنيو" type="search" autocomplete="off" aria-label="بحث" class="!outline-none !ring-0 !border-0 focus:!outline-none focus:!ring-0 focus:!border-0">
                <button type="submit" aria-label="بحث">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </form>
            <div class="admin-topbar__actions">
                <a href="{{ route('partner.orders.index') }}" class="admin-topbar__icon" title="الطلبات الواردة">
                    <span class="material-symbols-outlined">receipt_long</span>
                    @if($pendingPartnerOrdersCount)
                        <em>{{ $pendingPartnerOrdersCount > 9 ? '9+' : $pendingPartnerOrdersCount }}</em>
                    @endif
                </a>
                <button type="button" class="admin-topbar__icon js-order-sound-btn" title="اختبار نغمة تنبيه الطلبات (تزمير)">
                    <span class="material-symbols-outlined">volume_up</span>
                </button>
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
                        <small>{{ $partnerRestaurant?->name ?? 'صاحب مكان' }}</small>
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
                <h1>@yield('title', $panelTitle)</h1>
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

    @if($partnerRestaurant?->isPending())
        <div class="partner-visit-overlay" id="partner-visit-dialog" role="dialog" aria-modal="true" aria-labelledby="partner-visit-title">
            <div class="partner-visit-card">
                <div class="partner-visit-card__icon" aria-hidden="true">
                    <span class="material-symbols-outlined">storefront</span>
                </div>
                <p class="partner-visit-card__kicker">حساب قيد المراجعة</p>
                <h2 id="partner-visit-title">سيتم زيارة {{ $partnerRestaurant->venueNounYours() }} قريباً</h2>
                <p>
                    حساب <strong>{{ $partnerRestaurant->name }}</strong> لا يزال قيد مراجعة الإدارة.
                    سيتم زيارة {{ $partnerRestaurant->venueNounYours() }} قريباً من قبل فريق سفرة غزة للتحقق من البيانات، وبعد الموافقة يظهر للزبائن.
                </p>
                <button type="button" class="admin-btn admin-btn--primary partner-visit-card__ok" id="partner-visit-dismiss">
                    حسناً، فهمت
                </button>
            </div>
        </div>
        <script>
            (function () {
                var root = document.getElementById('partner-visit-dialog');
                if (!root) return;
                var key = 'partner-visit-dialog-{{ auth()->id() }}-{{ csrf_token() }}';
                try {
                    if (sessionStorage.getItem(key) === '1') {
                        root.hidden = true;
                        return;
                    }
                } catch (e) {}
                document.body.classList.add('partner-visit-lock');
                var btn = document.getElementById('partner-visit-dismiss');
                if (!btn) return;
                btn.addEventListener('click', function () {
                    root.hidden = true;
                    document.body.classList.remove('partner-visit-lock');
                    try { sessionStorage.setItem(key, '1'); } catch (e) {}
                });
            })();
        </script>
    @endif
</body>
</html>
