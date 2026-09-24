<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(($courierRefresh ?? false))
        <meta http-equiv="refresh" content="20">
    @endif
    <title>@yield('title', 'توصيل') — سفرة غزة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $courierUser = auth()->user();
    $navMine = $courierUser->deliveries()->whereIn('status', ['preparing', 'delivering'])->count();
    $navDone = $courierUser->deliveries()->where('status', 'delivered')->whereDate('delivered_at', today())->count();
    $onDash = request()->routeIs('courier.dashboard');
    $tab = request('tab', 'mine');
    $approved = $courierUser->isCourierApproved();
@endphp
<body class="courier-app">
    <div class="courier-shell">
    <header class="courier-top">
        <div class="courier-top__brand">
            @hasSection('back')
                <a class="courier-icon" href="@yield('back')" aria-label="رجوع">
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            @endif
            <div>
                <small>سفرة غزة</small>
                <strong>@yield('title', 'لوحة التوصيل')</strong>
            </div>
        </div>
        <div class="courier-top__actions">
            <a href="{{ url()->full() }}" class="courier-icon" aria-label="تحديث">
                <span class="material-symbols-outlined">refresh</span>
            </a>
            <a href="{{ route('courier.notifications.index') }}" class="courier-icon {{ request()->routeIs('courier.notifications.*') ? 'is-active' : '' }}" aria-label="الإشعارات">
                <span class="material-symbols-outlined">{{ $unreadNotifications ? 'notifications' : 'notifications' }}</span>
                @if($unreadNotifications)
                    <em>{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</em>
                @endif
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="courier-icon" aria-label="خروج">
                    <span class="material-symbols-outlined">logout</span>
                </button>
            </form>
        </div>
    </header>

    <main class="courier-main">
        @if(session('success'))
            <div class="courier-flash courier-flash--ok">{{ session('success') }}</div>
        @endif
        @if(session('error') || $errors->any())
            <div class="courier-flash courier-flash--err">{{ session('error') ?: $errors->first() }}</div>
        @endif
        @yield('content')
    </main>

    @if($approved)
    <nav class="courier-nav" aria-label="تنقل التوصيل">
        <a href="{{ route('courier.dashboard', ['tab' => 'mine']) }}" class="{{ $onDash && $tab === 'mine' ? 'is-active' : '' }}">
            <span class="material-symbols-outlined">delivery_dining</span>
            طلباتي
            @if($navMine)<i>{{ $navMine }}</i>@endif
        </a>
        <a href="{{ route('courier.dashboard', ['tab' => 'done']) }}" class="{{ $onDash && $tab === 'done' ? 'is-active' : '' }}">
            <span class="material-symbols-outlined">task_alt</span>
            اليوم
            @if($navDone)<i>{{ $navDone }}</i>@endif
        </a>
        <a href="{{ route('courier.notifications.index') }}" class="{{ request()->routeIs('courier.notifications.*') ? 'is-active' : '' }}">
            <span class="material-symbols-outlined">notifications</span>
            تنبيهات
            @if($unreadNotifications)<i>{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</i>@endif
        </a>
    </nav>
    @endif
    </div>
</body>
</html>
