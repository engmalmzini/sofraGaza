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
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;500;600;700;800;900&family=Amiri:wght@400;700&family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ═══════════════════════════════════════════════════════════════════
           SOFRA GAZA — SPLIT-SCREEN AUTHENTICATION DESIGN SYSTEM
           ═══════════════════════════════════════════════════════════════════ */
        :root {
            --sg-sand: #FAF6F0;
            --sg-sand-card: #FFFFFF;
            --sg-primary: #a33900;
            --sg-primary-hover: #822e00;
            --sg-primary-light: #c84500;
            --sg-gold: #c88a10;
            --sg-gold-light: #fef3c7;
            --sg-dark: #16120e;
            --sg-border: rgba(163, 57, 0, 0.12);
            --font-alex: 'Alexandria', sans-serif;
            --font-cairo: 'Cairo', sans-serif;
        }

        body.auth-body {
            background-color: var(--sg-sand) !important;
            background: var(--sg-sand) !important;
            color: #1c1917 !important;
            font-family: var(--font-cairo);
            margin: 0;
            padding: 0;
            min-height: 100dvh;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Split Screen Layout Container ── */
        .sg-split-page {
            display: flex;
            min-height: 100dvh;
            min-height: 100vh;
            width: 100%;
            background: var(--sg-sand);
            direction: rtl;
        }

        /* ── 1. Right Side: Form Panel ── */
        .sg-split-form-panel {
            flex: 1 1 52%;
            max-width: 55%;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.25rem 3.5rem 1.75rem;
            background: var(--sg-sand);
            position: relative;
            z-index: 10;
            overflow-y: auto;
        }

        @media (max-width: 1023px) {
            .sg-split-form-panel {
                flex: 1 1 100%;
                max-width: 100%;
                padding: 1.25rem 1rem 2rem;
                min-height: 100dvh;
            }
        }

        /* Top Navigation in Form Panel */
        .sg-auth-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2rem;
            width: 100%;
        }

        .sg-auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .sg-auth-brand:hover {
            opacity: 0.9;
        }

        .sg-auth-brand img {
            height: 42px;
            width: auto;
            object-fit: contain;
        }

        .sg-auth-brand__text {
            display: flex;
            flex-direction: column;
            text-align: right;
            line-height: 1.2;
        }

        .sg-auth-brand__title {
            font-family: var(--font-alex);
            font-weight: 900;
            font-size: 1.22rem;
            color: #1c1917;
            letter-spacing: -0.02em;
        }

        .sg-auth-brand__subtitle {
            font-size: 10px;
            font-weight: 800;
            color: #a8a29e;
            letter-spacing: 0.08em;
        }

        /* Quiet home link — matches the sand/terracotta auth palette */
        .sg-auth-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.42rem 0.85rem 0.42rem 0.7rem;
            border-radius: 9999px;
            background: rgba(163, 57, 0, 0.08);
            border: 0;
            color: #7a2800;
            font-family: var(--font-alex);
            font-size: 0.8rem;
            font-weight: 800;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
            white-space: nowrap;
            box-shadow: none;
        }

        .sg-auth-back-btn .material-symbols-outlined {
            font-size: 1.15rem;
            color: #a33900;
            transition: color 0.2s ease;
        }

        .sg-auth-back-btn:hover {
            background: #a33900;
            color: #ffffff !important;
        }

        .sg-auth-back-btn:hover .material-symbols-outlined {
            color: #ffffff;
        }

        /* Mobile Hero Banner Pill */
        .sg-mobile-banner-chip {
            display: none;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, rgba(163, 57, 0, 0.08), rgba(200, 69, 0, 0.04));
            border: 1px solid rgba(163, 57, 0, 0.15);
            padding: 0.6rem 0.9rem;
            border-radius: 1rem;
            margin-bottom: 1.25rem;
            color: #7a2800;
            font-size: 0.82rem;
            font-weight: 700;
            font-family: var(--font-alex);
        }

        @media (max-width: 1023px) {
            .sg-mobile-banner-chip {
                display: flex;
            }
        }

        /* Main Form Card Wrapper */
        .sg-auth-card-wrap {
            width: 100%;
            max-width: 460px;
            margin: auto;
            padding: 0.5rem 0;
        }

        .sg-auth-card {
            background: #ffffff;
            border-radius: 1.75rem;
            padding: 2.25rem 2rem;
            box-shadow:
                0 20px 45px -12px rgba(22, 18, 14, 0.08),
                0 0 0 1px rgba(163, 57, 0, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        @media (max-width: 640px) {
            .sg-auth-card {
                padding: 1.5rem 1.15rem;
                border-radius: 1.35rem;
                box-shadow: 0 10px 28px -8px rgba(22, 18, 14, 0.06);
            }
        }

        /* Tabs Capsule (دخول / إنشاء حساب) */
        .sg-auth-tabs,
        .auth-tabs {
            display: flex;
            align-items: center;
            background: #efe7dc;
            border-radius: 9999px;
            padding: 4px;
            margin-bottom: 1.75rem;
            border: 1px solid rgba(163, 57, 0, 0.08);
            width: 100%;
        }

        .sg-auth-tab,
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 0.6rem 1rem;
            border-radius: 9999px;
            font-family: var(--font-alex);
            font-weight: 800;
            font-size: 0.88rem;
            color: #78716c;
            text-decoration: none;
            transition: all 0.22s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .sg-auth-tab.is-active,
        .auth-tab.is-active {
            background: #a33900 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(163, 57, 0, 0.28) !important;
        }

        .sg-auth-tab:not(.is-active):hover,
        .auth-tab:not(.is-active):hover {
            color: #a33900;
            background: rgba(255, 255, 255, 0.65);
        }

        /* Form Typography */
        .auth-title,
        .sg-form-title {
            font-family: var(--font-alex);
            font-size: 1.55rem;
            font-weight: 800;
            color: #16120e;
            margin: 0 0 0.35rem;
            text-align: right;
            letter-spacing: -0.01em;
        }

        .auth-lead,
        .sg-form-lead {
            font-size: 0.88rem;
            color: #78716c;
            margin: 0 0 1.5rem;
            line-height: 1.6;
            text-align: right;
        }

        /* Form Inputs & Fields */
        .sg-form-group {
            margin-bottom: 1rem;
            text-align: right;
        }

        .sg-form-label {
            display: block;
            font-family: var(--font-alex);
            font-size: 0.82rem;
            font-weight: 700;
            color: #292524;
            margin-bottom: 0.45rem;
        }

        .sg-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: #faf6f0;
            border: 1.5px solid #e7ded4;
            border-radius: 0.95rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .sg-input-wrapper:focus-within {
            border-color: #a33900;
            background: #ffffff;
            box-shadow: 0 0 0 3.5px rgba(163, 57, 0, 0.12);
        }

        .sg-input-icon {
            width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a33900;
            flex-shrink: 0;
            font-size: 1.25rem;
        }

        .sg-input-field {
            flex: 1;
            border: none;
            background: transparent;
            padding: 0.85rem 0.5rem;
            font-family: var(--font-cairo);
            font-size: 0.95rem;
            font-weight: 600;
            color: #1c1917;
            outline: none;
            width: 100%;
        }

        .sg-input-field::placeholder {
            color: #a8a29e;
            font-weight: 500;
        }

        .sg-toggle-btn {
            background: none;
            border: none;
            padding: 0 0.85rem;
            color: #a8a29e;
            cursor: pointer;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sg-toggle-btn:hover {
            color: #a33900;
        }

        .sg-error-text {
            color: #dc2626;
            font-size: 0.78rem;
            font-weight: 700;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Checkbox & Options */
        .sg-form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0.75rem 0 1.35rem;
            font-size: 0.84rem;
        }

        .sg-checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: #57534e;
            font-weight: 600;
            user-select: none;
        }

        .sg-checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #a33900;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Submit Button */
        .auth-submit,
        .sg-submit-btn {
            width: 100%;
            min-height: 3.25rem;
            padding: 0.9rem 1.5rem;
            border-radius: 0.95rem;
            background: linear-gradient(135deg, #a33900 0%, #c84500 100%);
            color: #ffffff;
            font-family: var(--font-alex);
            font-weight: 800;
            font-size: 1.02rem;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            box-shadow: 0 8px 22px rgba(163, 57, 0, 0.28);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }

        .auth-submit:hover,
        .sg-submit-btn:hover {
            background: linear-gradient(135deg, #8a3000 0%, #b33d00 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(163, 57, 0, 0.38);
            color: #ffffff;
        }

        .auth-submit:active,
        .sg-submit-btn:active {
            transform: translateY(0);
        }

        /* Divider */
        .sg-auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.65rem 0 1.25rem;
            color: #a8a29e;
            font-size: 0.8rem;
            font-weight: 700;
            font-family: var(--font-alex);
        }

        .sg-auth-divider::before,
        .sg-auth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1.5px solid #e7ded4;
        }

        .sg-auth-divider span {
            padding: 0 0.85rem;
        }

        /* Partner Badges */
        .sg-partner-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .sg-partner-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.85rem 0.65rem;
            border-radius: 1rem;
            background: #faf6f0;
            border: 1.5px solid #e7ded4;
            color: #443833;
            text-decoration: none;
            transition: all 0.22s ease;
            gap: 0.35rem;
            text-align: center;
        }

        .sg-partner-card:hover {
            border-color: #a33900;
            background: #fff6f0;
            color: #a33900;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(163, 57, 0, 0.12);
        }

        .sg-partner-card .material-symbols-outlined {
            font-size: 26px;
            color: #a33900;
        }

        .sg-partner-card__title {
            font-family: var(--font-alex);
            font-size: 0.84rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .sg-partner-card__sub {
            font-size: 0.7rem;
            color: #78716c;
        }

        /* Form Footer / Security Note */
        .sg-auth-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.78rem;
            color: #a8a29e;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        /* ── 2. Left Side: Majestic Food & Brand Banner ── */
        .sg-split-banner-panel {
            flex: 1 1 48%;
            min-height: 100dvh;
            position: relative;
            background-color: #16120e;
            background-image:
                linear-gradient(180deg, rgba(22, 18, 14, 0.48) 0%, rgba(22, 18, 14, 0.78) 48%, rgba(22, 18, 14, 0.96) 100%),
                url('{{ asset('images/auth-banner.jpg') }}');
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 3.5rem;
            color: #ffffff;
            box-shadow: inset 2px 0 0 rgba(255, 255, 255, 0.08);
            position: relative;
        }

        @media (max-width: 1023px) {
            .sg-split-banner-panel {
                display: none !important;
            }
        }

        .sg-banner-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1.05rem;
            border-radius: 9999px;
            background: rgba(22, 18, 14, 0.65);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-family: var(--font-alex);
            font-size: 0.82rem;
            font-weight: 700;
            color: #fef3c7;
            width: fit-content;
        }

        .sg-banner-headline {
            font-family: var(--font-alex);
            font-size: 2.35rem;
            font-weight: 900;
            line-height: 1.35;
            color: #ffffff;
            margin: 1.5rem 0 1rem;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.6);
            letter-spacing: -0.02em;
        }

        .sg-banner-headline span {
            color: #f59e0b;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sg-banner-desc {
            font-size: 1rem;
            line-height: 1.8;
            color: #e7e5e4;
            max-width: 480px;
            margin-bottom: 2rem;
            font-weight: 500;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        /* Pillars / Feature Badges */
        .sg-banner-features {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            max-width: 490px;
        }

        .sg-banner-card {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.15rem;
            border-radius: 1.15rem;
            background: rgba(22, 18, 14, 0.58);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.25s ease;
        }

        .sg-banner-card:hover {
            background: rgba(22, 18, 14, 0.78);
            border-color: rgba(245, 158, 11, 0.4);
            transform: translateX(-4px);
        }

        .sg-banner-card__icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(163, 57, 0, 0.95), rgba(200, 69, 0, 0.95));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(163, 57, 0, 0.4);
        }

        .sg-banner-card__title {
            font-family: var(--font-alex);
            font-weight: 800;
            font-size: 0.94rem;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .sg-banner-card__sub {
            font-size: 0.82rem;
            color: #d6d3d1;
            line-height: 1.55;
            margin: 0;
        }

        /* Bottom Trust Proof Bar */
        .sg-banner-trust {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 0.82rem;
            color: #e7e5e4;
            font-weight: 700;
            font-family: var(--font-alex);
        }

        .sg-banner-trust__item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* ── 3. Centered Wizard Layout (for Partner / Courier) ── */
        .sg-wizard-page {
            min-height: 100dvh;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 0.65rem 0.65rem calc(0.85rem + env(safe-area-inset-bottom, 0px));
        }

        .sg-wizard-header {
            width: 100%;
            max-width: 72rem;
            margin: 0 auto 0.65rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .sg-wizard-container {
            width: 100%;
            max-width: 72rem;
            margin: 0 auto;
            padding: 0;
            flex: 1;
        }

        .sg-wizard-card {
            background: #ffffff;
            border-radius: 1.25rem;
            padding: 1rem 0.85rem 1.1rem;
            box-shadow:
                0 16px 36px -14px rgba(22, 18, 14, 0.1),
                0 0 0 1px rgba(163, 57, 0, 0.07);
        }

        .sg-wizard-card > .auth-card__bar {
            margin-bottom: 0.85rem;
        }

        .sg-wizard-card .auth-tabs,
        .sg-wizard-card .sg-auth-tabs {
            margin-bottom: 0;
        }

        .sg-wizard-footer {
            margin-top: 0.85rem;
        }

        @media (min-width: 640px) {
            .sg-wizard-page {
                padding: 1.15rem 1.1rem 1.25rem;
            }

            .sg-wizard-header {
                margin-bottom: 0.95rem;
            }

            .sg-wizard-card {
                border-radius: 1.65rem;
                padding: 1.7rem 1.55rem 1.55rem;
            }

            .sg-wizard-card > .auth-card__bar {
                margin-bottom: 1.25rem;
            }

            .sg-wizard-footer {
                margin-top: 1.15rem;
            }
        }

        @media (min-width: 1024px) {
            .sg-wizard-page {
                padding: 1.6rem 1.5rem 1.4rem;
            }

            .sg-wizard-card {
                border-radius: 1.85rem;
                padding: 2.1rem 2rem 1.9rem;
            }
        }

        /* Steps and helpers for registration */
        .auth-steps {
            display: flex;
            gap: 0.5rem;
            margin: 0 0 1.25rem;
            padding: 0;
            list-style: none;
        }

        .auth-steps li {
            flex: 1;
            text-align: center;
            font-size: 0.78rem;
            font-family: var(--font-alex);
            font-weight: 800;
            color: #78716c;
            padding: 0.5rem 0.3rem;
            border-radius: 9999px;
            background: #f5eee6;
            border: 1px solid #e7ded4;
            transition: all 0.2s;
        }

        .auth-steps .is-current {
            color: #ffffff;
            background: #a33900;
            border-color: #a33900;
            box-shadow: 0 3px 10px rgba(163, 57, 0, 0.25);
        }

        .auth-error {
            color: #dc2626;
            font-size: 0.78rem;
            font-weight: 700;
            margin: -0.25rem 0 0.65rem;
        }

        .auth-text-btn {
            background: none;
            border: none;
            color: #a33900;
            font-family: var(--font-alex);
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 3px;
            padding: 0.25rem 0.5rem;
            transition: color 0.2s;
        }

        .auth-text-btn:hover {
            color: #7a2800;
        }

        .sg-input-field[dir="ltr"] {
            direction: ltr;
            text-align: right;
            unicode-bidi: isolate;
        }

        /* ── Phone / small tablet: keep login compact and unclipped ── */
        @media (max-width: 1023px) {
            .sg-split-form-panel {
                justify-content: flex-start;
                gap: 0.75rem;
                padding: 1rem 1rem calc(1.35rem + env(safe-area-inset-bottom, 0px));
            }

            .sg-auth-nav {
                margin-bottom: 0.35rem;
                gap: 0.55rem;
            }

            .sg-auth-card-wrap {
                margin: 0 auto;
                padding: 0;
            }

            .sg-auth-footer {
                flex-wrap: wrap;
                margin-top: 1.15rem;
                line-height: 1.55;
            }
        }

        @media (max-width: 640px) {
            .sg-auth-brand__text {
                display: none;
            }

            .sg-auth-brand img {
                height: 36px;
            }

            .sg-auth-back-btn {
                padding: 0.38rem 0.72rem 0.38rem 0.58rem;
                font-size: 0.75rem;
                gap: 0.28rem;
            }

            .sg-auth-back-btn .material-symbols-outlined {
                font-size: 1.05rem;
            }

            .sg-mobile-banner-chip {
                padding: 0.48rem 0.7rem;
                font-size: 0.74rem;
                margin-bottom: 0.35rem;
                border-radius: 0.85rem;
            }

            .sg-auth-card {
                padding: 1.2rem 0.95rem 1.35rem;
                border-radius: 1.2rem;
            }

            .sg-auth-tabs,
            .auth-tabs {
                margin-bottom: 1.1rem;
                padding: 3px;
            }

            .sg-auth-tab,
            .auth-tab {
                min-width: 0;
                padding: 0.48rem 0.35rem;
                font-size: 0.76rem;
                gap: 0.2rem;
            }

            .sg-auth-tab .material-symbols-outlined,
            .auth-tab .material-symbols-outlined {
                display: none;
            }

            .sg-form-title,
            .auth-title {
                font-size: 1.22rem;
            }

            .sg-form-lead,
            .auth-lead {
                font-size: 0.8rem;
                margin-bottom: 1.05rem;
            }

            .sg-input-field {
                font-size: 16px;
                padding: 0.78rem 0.4rem;
            }

            .sg-input-icon {
                width: 40px;
            }

            .sg-partner-grid {
                gap: 0.55rem;
            }

            .sg-partner-card {
                padding: 0.7rem 0.4rem;
            }

            .sg-partner-card__title {
                font-size: 0.76rem;
            }

            .sg-partner-card__sub {
                font-size: 0.62rem;
            }

            .sg-auth-footer {
                font-size: 0.68rem;
            }

            .auth-steps {
                gap: 0.3rem;
            }

            .auth-steps li {
                font-size: 0.68rem;
                padding: 0.4rem 0.12rem;
            }
        }

        @media (max-width: 380px) {
            .sg-split-form-panel {
                padding-inline: 0.7rem;
            }

            .sg-auth-back-btn {
                padding: 0.36rem 0.65rem 0.36rem 0.5rem;
                font-size: 0.72rem;
            }

            .sg-auth-card {
                padding: 1.05rem 0.8rem 1.2rem;
            }

            .sg-auth-tab,
            .auth-tab {
                font-size: 0.72rem;
                padding: 0.45rem 0.25rem;
            }

            .sg-mobile-banner-chip {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $isWizard = str_contains($__env->yieldContent('shell_class'), 'auth-shell--wizard');
@endphp
<body class="auth-body">
    @if($isWizard)
        {{-- Centered Layout for Multi-Step Partner / Courier Wizards --}}
        <div class="sg-wizard-page">
            <header class="sg-wizard-header">
                <a href="{{ route('home') }}" class="sg-auth-brand">
                    <img src="{{ $logoSrc }}" alt="سفرة غزة">
                    <div class="sg-auth-brand__text">
                        <span class="sg-auth-brand__title">سُفرة <span class="text-[#c84500]">غزة</span></span>
                        <span class="sg-auth-brand__subtitle">SOFRA GAZA</span>
                    </div>
                </a>
                <a href="{{ route('home') }}" class="sg-auth-back-btn" title="العودة إلى الصفحة الرئيسية">
                    <span class="material-symbols-outlined">home</span>
                    <span>الرئيسية</span>
                </a>
            </header>

            <main class="sg-wizard-container">
                <div class="sg-wizard-card">
                    <div class="auth-card__bar">
                        <div class="auth-tabs" role="tablist">
                            @yield('tabs')
                        </div>
                    </div>
                    @yield('content')
                </div>
            </main>

            <footer class="sg-auth-footer sg-wizard-footer">
                <span class="material-symbols-outlined text-[16px] text-emerald-600">verified_user</span>
                <span>سُفرة غزة © جميع الحقوق محفوظة • بوابة انضمام الشركاء المعتمدة</span>
            </footer>
        </div>
    @else
        {{-- Full Split-Screen Layout for Login & Register --}}
        <div class="sg-split-page">
            {{-- Right Half: Form Panel --}}
            <section class="sg-split-form-panel">
                <header class="sg-auth-nav">
                    <a href="{{ route('home') }}" class="sg-auth-brand" title="الذهاب للرئيسية">
                        <img src="{{ $logoSrc }}" alt="سفرة غزة">
                        <div class="sg-auth-brand__text">
                            <span class="sg-auth-brand__title">سُفرة <span class="text-[#c84500]">غزة</span></span>
                            <span class="sg-auth-brand__subtitle">SOFRA GAZA</span>
                        </div>
                    </a>

                    <a href="{{ route('home') }}" class="sg-auth-back-btn" title="العودة إلى الصفحة الرئيسية">
                        <span class="material-symbols-outlined">home</span>
                        <span>الرئيسية</span>
                    </a>
                </header>

                {{-- Mobile Friendly Food Badge --}}
                <div class="sg-mobile-banner-chip">
                    <span class="material-symbols-outlined text-amber-600 text-sm fill-1">restaurant</span>
                    <span>أشهى سفرة تصلك أينما كنت في غزة</span>
                </div>

                {{-- The Form Card --}}
                <main class="sg-auth-card-wrap">
                    <div class="sg-auth-card">
                        <div class="auth-card__bar">
                            <div class="sg-auth-tabs" role="tablist">
                                @yield('tabs')
                            </div>
                        </div>

                        @yield('content')
                    </div>
                </main>

                {{-- Form Footer --}}
                <footer class="sg-auth-footer">
                    <span class="material-symbols-outlined text-[15px] text-emerald-600">lock</span>
                    <span>منصة سُفرة غزة © جميع الحقوق محفوظة • تسجيل دخول آمن ومشفّر</span>
                </footer>
            </section>

            {{-- Left Half: Panoramic Food Showcase & Brand Pillars --}}
            <aside class="sg-split-banner-panel" aria-label="سفرة غزة — طعم الأصالة والضيافة">
                {{-- Top Badge --}}
                <div>
                    <div class="sg-banner-badge">
                        <span class="material-symbols-outlined text-amber-400 text-sm fill-1">local_fire_department</span>
                        <span>المنصة الأولى لأشهى مطاعم ومأكولات غزة</span>
                    </div>
                </div>

                {{-- Middle Content --}}
                <div>
                    <h2 class="sg-banner-headline">
                        أشهى سُفرة تصلك<br>
                        <span>أينما كنت في غزة</span>
                    </h2>
                    <p class="sg-banner-desc">
                        من أفران الشاورما والمشويات الفحمية إلى الكنافة الغزية والحلويات الفاخرة، جمعنا لك أشهر مطاعم وكافيهات القطاع في تجربة طلب واحدة وسريعة.
                    </p>

                    {{-- 3 Key Features --}}
                    <div class="sg-banner-features">
                        {{-- Feature 1: Loyalty Points --}}
                        <div class="sg-banner-card">
                            <div class="sg-banner-card__icon">
                                <span class="material-symbols-outlined fill-1">stars</span>
                            </div>
                            <div>
                                <h3 class="sg-banner-card__title">نقاط الولاء والمكافآت الحصرية</h3>
                                <p class="sg-banner-card__sub">اجمع النقاط مع كل طلب وحوّلها لخصومات حقيقية ورصيد مشتريات في محفظتك.</p>
                            </div>
                        </div>

                        {{-- Feature 2: Saved Cart --}}
                        <div class="sg-banner-card">
                            <div class="sg-banner-card__icon">
                                <span class="material-symbols-outlined">shopping_bag</span>
                            </div>
                            <div>
                                <h3 class="sg-banner-card__title">سلتك ومطاعمك المفضلة محفوظة</h3>
                                <p class="sg-banner-card__sub">تصفح بحرية، وستجد طلباتك وعناوينك المحفوظة جاهزة متى ما عدت لحسابك.</p>
                            </div>
                        </div>

                        {{-- Feature 3: Fast Reliable Delivery --}}
                        <div class="sg-banner-card">
                            <div class="sg-banner-card__icon">
                                <span class="material-symbols-outlined">moped</span>
                            </div>
                            <div>
                                <h3 class="sg-banner-card__title">توصيل سريع وموثوق لباب بيتك</h3>
                                <p class="sg-banner-card__sub">أسطول مناديب نشط يغطي أحياء غزة، دير البلح، وخانيونس مع تتبع فوري للطلب.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom Proof --}}
                <div class="sg-banner-trust">
                    <div class="sg-banner-trust__item">
                        <span class="material-symbols-outlined text-amber-400 text-base fill-1">star</span>
                        <span>4.9 تقييم تجربة المستخدم</span>
                    </div>
                    <div class="sg-banner-trust__item">
                        <span class="material-symbols-outlined text-amber-400 text-base">storefront</span>
                        <span>+50 مطعم وكافيه معتمد</span>
                    </div>
                    <div class="sg-banner-trust__item">
                        <span class="material-symbols-outlined text-amber-400 text-base">bolt</span>
                        <span>تجهيز وتوصيل فوري</span>
                    </div>
                </div>
            </aside>
        </div>
    @endif

    {{-- Toast Notification --}}
    <div id="app-toast" class="app-toast" hidden role="status" aria-live="polite" data-success="{{ session('success') }}" data-error="{{ session('error') ?: $errors->first() }}">
        <span class="app-toast__icon material-symbols-outlined fill-1">check_circle</span>
        <p class="app-toast__text"></p>
    </div>

    <script>
        function toggleAuthPassword(button) {
            const wrapper = button.closest('.sg-input-wrapper') || button.closest('label');
            const input = wrapper ? wrapper.querySelector('input[name="password"]') : null;
            const icon = button.querySelector('.material-symbols-outlined');
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) icon.textContent = 'visibility_off';
                } else {
                    input.type = 'password';
                    if (icon) icon.textContent = 'visibility';
                }
            }
        }
    </script>
</body>
</html>
