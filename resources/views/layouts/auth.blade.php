<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'سفرة غزة') — سفرة غزة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
@endphp
<body class="auth-body">
    <div class="auth-scene">
        <div class="auth-fx" aria-hidden="true">
            <span class="auth-fx__arc"></span>
            <span class="auth-fx__glow auth-fx__glow--tr"></span>
            <span class="auth-fx__glow auth-fx__glow--bl"></span>
            <span class="auth-fx__glow auth-fx__glow--c"></span>
        </div>

        <div class="auth-shell @yield('shell_class')">
            @include('partials.auth-cast')
            <a href="{{ route('home') }}" class="auth-brand">
                <img src="{{ $logoSrc }}" alt="سفرة غزة">
            </a>

            <section class="auth-card @yield('card_class')">
                <div class="auth-card__bar">
                    <div class="auth-tabs" role="tablist">
                        @yield('tabs')
                    </div>
                    <a href="{{ route('home') }}" class="auth-close" aria-label="إغلاق والعودة للرئيسية">
                        <span class="material-symbols-outlined">close</span>
                    </a>
                </div>
                @yield('content')
            </section>
        </div>
    </div>

    <div id="app-toast" class="app-toast" hidden role="status" aria-live="polite" data-success="{{ session('success') }}" data-error="{{ session('error') ?: $errors->first() }}">
        <span class="app-toast__icon material-symbols-outlined fill-1">check_circle</span>
        <p class="app-toast__text"></p>
    </div>
</body>
</html>
