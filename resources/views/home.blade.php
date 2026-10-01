@extends('layouts.public')

@section('title', 'الرئيسية — سفرة غزة لأشهى المطاعم والكافيهات')

@section('content')
<style>
/* ═════════════════════════════════════════════════════════════════════
   SOFRA GAZA — MASTERPIECE DESIGN SYSTEM & LIVE NEWS TICKER
   ═════════════════════════════════════════════════════════════════════ */
:root {
    --sg-primary: #a33900;
    --sg-primary-dark: #7a2800;
    --sg-primary-light: #cc4900;
    --sg-gold: #c88a10;
    --sg-gold-light: #fef3c7;
    --sg-green: #007a37;
    --sg-dark: #16120e;
    --sg-sand: #faf6f0;
}

.sg-canvas {
    background: #FAF6F0;
    color: #1e1915;
    overflow-x: hidden;
}

/* ── 1. LIVE GAZA GASTRONOMY & NEWS TICKER ── */
.sg-news-ticker {
    background: #140f0c;
    color: #ffffff;
    border-bottom: 1.5px solid rgba(255, 255, 255, 0.08);
    position: relative;
    z-index: 30;
    overflow: hidden;
    height: 48px;
    display: flex;
    align-items: center;
}
.sg-news-badge {
    background: linear-gradient(135deg, #a33900 0%, #d64700 100%);
    color: #ffffff;
    font-weight: 900;
    font-size: 0.78rem;
    padding: 0 1rem;
    height: 100%;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-shrink: 0;
    z-index: 10;
    box-shadow: 4px 0 16px rgba(0, 0, 0, 0.35);
}
.sg-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: sgPulse 2s infinite;
}
@keyframes sgPulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.sg-ticker-track-wrap {
    flex-grow: 1;
    overflow: hidden;
    position: relative;
    height: 100%;
    display: flex;
    align-items: center;
    mask-image: linear-gradient(to right, transparent 0%, black 3%, black 97%, transparent 100%);
    -webkit-mask-image: linear-gradient(to right, transparent 0%, black 3%, black 97%, transparent 100%);
}

.sg-ticker-track {
    display: flex;
    align-items: center;
    white-space: nowrap;
    gap: 2.5rem;
    animation: sgMarquee 38s linear infinite;
}
.sg-ticker-track:hover {
    animation-play-state: paused;
}
@keyframes sgMarquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(50%); }
}

.sg-news-item {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    color: #e5ded7;
    text-decoration: none;
    transition: all 0.2s ease;
    padding: 0.2rem 0.6rem;
    border-radius: 8px;
}
.sg-news-item:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
}
.sg-news-tag {
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.15rem 0.55rem;
    border-radius: 9999px;
}
.sg-news-tag--dish {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.4);
}
.sg-news-tag--place {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.4);
}
.sg-news-tag--promo {
    background: rgba(225, 29, 72, 0.2);
    color: #fda4af;
    border: 1px solid rgba(225, 29, 72, 0.4);
}

.sg-news-action {
    display: none;
}
@media (min-width: 768px) {
    .sg-news-action {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-left: 1.25rem;
        padding-right: 1.25rem;
        flex-shrink: 0;
        z-index: 10;
        font-size: 0.78rem;
        color: #a8988b;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }
}

/* ── 2. HERO SECTION ── */
.sg-hero {
    position: relative;
    padding-top: 2.5rem;
    padding-bottom: 4rem;
}
@media (min-width: 1024px) {
    .sg-hero {
        padding-top: 3.75rem;
        padding-bottom: 6rem;
    }
}
.sg-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 1rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(163, 57, 0, 0.2);
    box-shadow: 0 4px 15px rgba(163, 57, 0, 0.08);
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--sg-primary);
}
.sg-hero-title {
    font-weight: 900;
    line-height: 1.18;
    letter-spacing: -0.02em;
}
.sg-text-gradient {
    background: linear-gradient(135deg, #a33900 0%, #e65100 50%, #d48b00 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Compact Modern Search Capsule */
.sg-search-compact {
    background: #ffffff;
    border-radius: 9999px;
    padding: 0.35rem 0.5rem;
    box-shadow: 0 10px 30px -6px rgba(163, 57, 0, 0.09), 0 2px 8px rgba(0, 0, 0, 0.03);
    border: 1.5px solid rgba(163, 57, 0, 0.12);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.sg-search-compact:focus-within {
    border-color: var(--sg-primary);
    box-shadow: 0 12px 32px -4px rgba(163, 57, 0, 0.18), 0 0 0 3px rgba(163, 57, 0, 0.08);
}
@media (max-width: 639px) {
    .sg-search-compact {
        border-radius: 20px;
        padding: 0.5rem;
    }
}
.sg-search-compact-btn {
    background: linear-gradient(135deg, #a33900 0%, #d64700 100%);
    color: #ffffff;
    border-radius: 9999px;
    padding: 0.55rem 1.4rem;
    font-weight: 800;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(163, 57, 0, 0.28);
}
.sg-search-compact-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(163, 57, 0, 0.38);
    background: linear-gradient(135deg, #b84000 0%, #e65100 100%);
}
.sg-search-compact-btn:active {
    transform: translateY(0);
}

/* Micro Proof Stats Chips */
.sg-stat-chip {
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(214, 203, 194, 0.7);
    border-radius: 9999px;
    padding: 0.35rem 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
}
.sg-stat-chip:hover {
    background: #ffffff;
    border-color: rgba(163, 57, 0, 0.3);
    transform: translateY(-1.5px);
    box-shadow: 0 6px 14px rgba(163, 57, 0, 0.08);
}

/* Mood Pills */
.sg-mood-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.9rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(140, 100, 80, 0.15);
    font-size: 0.82rem;
    font-weight: 700;
    color: #52433d;
    transition: all 0.2s ease;
    text-decoration: none;
}
.sg-mood-pill:hover {
    background: #ffffff;
    border-color: var(--sg-primary);
    color: var(--sg-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(163, 57, 0, 0.12);
}

/* Bento Hero */
.sg-bento-hero {
    position: relative;
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
}
.sg-bento-card-main {
    border-radius: 30px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 25px 60px -15px rgba(163, 57, 0, 0.25), 0 10px 25px rgba(0, 0, 0, 0.08);
    border: 4px solid #ffffff;
    background: #ffffff;
}
.sg-bento-card-main img {
    width: 100%;
    height: 380px;
    object-fit: cover;
    transition: transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}
.sg-bento-card-main:hover img {
    transform: scale(1.05);
}
.sg-bento-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.75) 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.5rem;
    color: #ffffff;
}

.sg-glass-widget {
    position: absolute;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(16px);
    border-radius: 20px;
    padding: 0.85rem 1.15rem;
    box-shadow: 0 16px 36px -6px rgba(0, 0, 0, 0.14), 0 4px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.9);
    z-index: 10;
    animation: sgFloat 6s ease-in-out infinite;
}
.sg-glass-widget--top {
    top: -24px;
    right: -16px;
    animation-delay: 0s;
}
.sg-glass-widget--bottom {
    bottom: -28px;
    left: -16px;
    animation-delay: 3s;
}
@keyframes sgFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

/* ── 3. FOODLY CATEGORIES CLONE (DESKTOP ONLY) ── */
.sg-cat-band {
    background: #ffffff;
    width: 100%;
}
.sg-cat-section {
    padding-top: 2.75rem;
    padding-bottom: 2.75rem;
    position: relative;
    width: 100%;
}
.sg-cat-header-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.25rem;
}
.sg-cat-header-title {
    font-family: 'Tajawal', sans-serif;
    font-size: 3.15rem;
    font-weight: 900;
    color: #1c1917;
    line-height: 1.25;
    letter-spacing: -0.025em;
}
.sg-cat-view-all {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-family: 'Cairo', 'Alexandria', sans-serif;
    font-size: 0.92rem;
    font-weight: 700;
    color: #1a120e;
    background: #ffffff;
    border: 1px solid #e8e0d6;
    border-radius: 999px;
    padding: 0.52rem 1.15rem 0.52rem 0.95rem;
    text-decoration: none;
    box-shadow: 0 1px 2px rgba(26, 18, 14, 0.04);
    transition: color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
}
.sg-cat-view-all .material-symbols-outlined {
    font-size: 18px;
    font-variation-settings: 'wght' 500;
    transition: transform 0.25s ease;
}
.sg-cat-view-all:hover {
    color: #e85d04;
    border-color: rgba(232, 93, 4, 0.45);
    box-shadow: 0 6px 16px rgba(232, 93, 4, 0.1);
    transform: translateY(-1px);
}
.sg-cat-view-all:hover .material-symbols-outlined {
    transform: translateX(-3px);
}

.sg-cat-track {
    position: relative;
    width: 100%;
}
.sg-cat-viewport {
    container-type: inline-size;
    overflow: hidden;
    width: 100%;
    padding: 2rem 0 1.75rem;
}
.sg-cat-slider {
    display: flex;
    align-items: flex-start;
    width: max-content;
    gap: 0;
    cursor: grab;
    user-select: none;
    will-change: transform;
}
.sg-cat-slider:active { cursor: grabbing; }
.sg-cat-nav {
    position: absolute;
    top: calc(2rem + 134px);
    transform: translateY(-50%);
    z-index: 20;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #e8e0d6;
    box-shadow: 0 8px 22px rgba(26, 18, 14, 0.1);
    color: #3f342c;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
}
.sg-cat-nav:hover {
    color: #e85d04;
    border-color: rgba(232, 93, 4, 0.45);
    box-shadow: 0 10px 24px rgba(232, 93, 4, 0.14);
}
.sg-cat-nav .material-symbols-outlined {
    font-size: 22px;
    font-variation-settings: 'wght' 500;
}
.sg-cat-nav--prev {
    inset-inline-start: -0.35rem;
}
.sg-cat-nav--next {
    inset-inline-end: -0.35rem;
}

.sg-cat-slide {
    flex: 0 0 calc(100cqw / 3);
    width: calc(100cqw / 3);
    min-width: 0;
    scroll-snap-align: start;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    text-decoration: none;
    padding: 0 0.5rem;
    box-sizing: border-box;
    overflow: visible;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.sg-cat-slide:hover { transform: translateY(-6px); }

.sg-cat-badge-wrap {
    position: relative;
    width: 268px;
    height: 268px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: visible;
}
.sg-cat-orbit-svg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    transform-origin: center center;
    transition: transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
}
.sg-cat-slide:hover .sg-cat-orbit-svg {
    transform: rotate(18deg) scale(1.04);
}

.sg-cat-food-wrap {
    position: absolute;
    inset: 14%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    pointer-events: none;
    overflow: visible;
}
.sg-cat-food-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 12px 18px rgba(0, 0, 0, 0.16));
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.sg-cat-food--grill img { transform: scale(0.76) translateY(-6px); }
.sg-cat-slide:hover .sg-cat-food--grill img { transform: scale(0.82) translateY(-12px); }

.sg-cat-food--cafe img { transform: scale(1.22) translateY(-10px); }
.sg-cat-slide:hover .sg-cat-food--cafe img { transform: scale(1.28) translateY(-16px); }

.sg-cat-food--fries img { transform: scale(1.28) translateY(-14px); }
.sg-cat-slide:hover .sg-cat-food--fries img { transform: scale(1.34) translateY(-20px); }

.sg-cat-food--burger img { transform: scale(0.82); }
.sg-cat-slide:hover .sg-cat-food--burger img { transform: scale(0.88) translateY(-6px); }

.sg-cat-food--shawarma img { transform: scale(1.08); }
.sg-cat-slide:hover .sg-cat-food--shawarma img { transform: scale(1.14) translateY(-6px); }

.sg-cat-food--pizza img { transform: scale(0.78); }
.sg-cat-slide:hover .sg-cat-food--pizza img { transform: scale(0.84) translateY(-6px); }

.sg-cat-food--sweets img { transform: scale(0.8) translateY(-2px); }
.sg-cat-slide:hover .sg-cat-food--sweets img { transform: scale(0.86) translateY(-8px); }

.sg-cat-food--seafood img { transform: scale(0.84); }
.sg-cat-slide:hover .sg-cat-food--seafood img { transform: scale(0.9) translateY(-6px); }

.sg-cat-title-en {
    font-family: 'DM Sans', 'Plus Jakarta Sans', sans-serif;
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a120e;
    margin-top: 1.05rem;
    letter-spacing: -0.01em;
    line-height: 1.2;
}
.sg-cat-title-ar { display: none; }

.sg-cat-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    font-family: 'Cairo', 'Alexandria', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: #e85d04;
    margin-top: 0.35rem;
}
.sg-cat-cta-btn .material-symbols-outlined {
    font-size: 16px;
    font-variation-settings: 'wght' 600;
}

/* Restaurant Cards */
.sg-place-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 640px) {
    .sg-place-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .sg-place-grid { grid-template-columns: repeat(3, 1fr); gap: 1.75rem; }
}
@media (min-width: 1400px) {
    .sg-place-grid { grid-template-columns: repeat(4, 1fr); }
}

.sg-card {
    background: #ffffff;
    border-radius: 24px;
    overflow: hidden;
    border: 1.5px solid rgba(140, 100, 80, 0.1);
    box-shadow: 0 6px 20px -2px rgba(22, 18, 14, 0.05);
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    position: relative;
    text-decoration: none;
}
.sg-card:hover {
    transform: translateY(-7px);
    border-color: rgba(163, 57, 0, 0.3);
    box-shadow: 0 20px 45px -8px rgba(163, 57, 0, 0.18), 0 4px 12px rgba(0, 0, 0, 0.04);
}
.sg-card__media {
    position: relative;
    width: 100%;
    height: 195px;
    overflow: hidden;
    background: #eae2d8;
}
.sg-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.sg-card:hover .sg-card__img {
    transform: scale(1.08);
}
.sg-card__badge-time {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(22, 18, 14, 0.85);
    backdrop-filter: blur(8px);
    color: #ffffff;
    padding: 0.3rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.sg-card__badge-promo {
    position: absolute;
    top: 12px;
    right: 12px;
    background: linear-gradient(135deg, #a33900 0%, #e65100 100%);
    color: #ffffff;
    padding: 0.3rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.72rem;
    font-weight: 800;
    box-shadow: 0 4px 12px rgba(163, 57, 0, 0.3);
}
.sg-card__fav-btn {
    position: absolute;
    top: 12px;
    left: 12px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #716259;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}
.sg-card__fav-btn:hover {
    color: #e11d48;
    transform: scale(1.12);
}
.sg-card__fav-btn.is-on,
.sg-card__fav-btn.is-on .material-symbols-outlined {
    color: #e11d48;
}
.sg-card__body {
    padding: 1.25rem 1.15rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

/* Filters */
.sg-filter-chip {
    padding: 0.5rem 1.15rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 700;
    background: #ffffff;
    border: 1.5px solid rgba(140, 100, 80, 0.15);
    color: #55443b;
    cursor: pointer;
    transition: all 0.25s ease;
}
.sg-filter-chip:hover {
    border-color: var(--sg-primary);
    color: var(--sg-primary);
}
.sg-filter-chip.is-active {
    background: var(--sg-primary);
    color: #ffffff;
    border-color: var(--sg-primary);
    box-shadow: 0 6px 18px rgba(163, 57, 0, 0.3);
}

/* Gold Section */
.sg-gold-wrap {
    padding: 2.5rem 1rem 3rem;
}
@media (min-width: 1024px) {
    .sg-gold-wrap { padding: 2.75rem 2rem 3.25rem; }
}
.sg-gold-club {
    position: relative;
    overflow: hidden;
    border-radius: 32px;
    color: #ffffff;
    padding: 2.25rem 1.5rem 2.4rem;
    background:
        radial-gradient(1200px 420px at 100% 0%, rgba(201, 162, 39, 0.18), transparent 55%),
        radial-gradient(900px 380px at 0% 100%, rgba(163, 57, 0, 0.22), transparent 50%),
        linear-gradient(165deg, #1c1410 0%, #0f0c0a 48%, #1a120c 100%);
    border: 1px solid rgba(212, 175, 55, 0.28);
    box-shadow:
        0 28px 64px -18px rgba(12, 8, 6, 0.55),
        inset 0 1px 0 rgba(255, 214, 120, 0.12);
}
@media (min-width: 1024px) {
    .sg-gold-club { padding: 2.75rem 2.75rem 2.85rem; }
}
.sg-gold-club::before {
    content: '';
    position: absolute;
    inset: 10px;
    border-radius: 24px;
    border: 1px dashed rgba(212, 175, 55, 0.18);
    pointer-events: none;
}
.sg-gold-grid {
    position: relative;
    z-index: 1;
    display: grid;
    gap: 2rem;
}
@media (min-width: 1024px) {
    .sg-gold-grid {
        grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr);
        gap: 2.5rem;
        align-items: stretch;
    }
}
.sg-gold-copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: right;
}
.sg-gold-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    align-self: flex-start;
    padding: 0.38rem 0.9rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #e2b53a, #c4921c);
    color: #1a120e;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.01em;
    margin-bottom: 1rem;
    box-shadow: 0 8px 18px rgba(196, 146, 28, 0.28);
}
.sg-gold-kicker .material-symbols-outlined {
    font-size: 16px;
    font-variation-settings: 'FILL' 1, 'wght' 600;
}
.sg-gold-title {
    font-family: 'Cairo', 'Alexandria', sans-serif;
    font-size: clamp(1.7rem, 2.4vw, 2.55rem);
    font-weight: 800;
    line-height: 1.28;
    color: #fffaf0;
    margin: 0 0 0.9rem;
    letter-spacing: -0.02em;
}
.sg-gold-text {
    font-size: 0.92rem;
    line-height: 1.85;
    color: rgba(255, 244, 220, 0.72);
    font-weight: 500;
    margin: 0 0 1.5rem;
    max-width: 36rem;
}
.sg-gold-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: 0.85rem;
}
.sg-gold-balance {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    min-width: 9.5rem;
    padding: 0.7rem 1rem;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(212, 175, 55, 0.22);
}
.sg-gold-balance__coin {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: radial-gradient(circle at 35% 30%, #ffe08a, #c4921c 62%, #8a6410);
    color: #3b2a08;
    box-shadow: 0 8px 16px rgba(196, 146, 28, 0.35);
    flex-shrink: 0;
}
.sg-gold-balance__coin .material-symbols-outlined {
    font-size: 22px;
    font-variation-settings: 'FILL' 1, 'wght' 600;
}
.sg-gold-balance small {
    display: block;
    font-size: 0.68rem;
    font-weight: 700;
    color: rgba(255, 244, 220, 0.55);
}
.sg-gold-balance strong {
    display: block;
    font-size: 1.2rem;
    font-weight: 800;
    color: #f3c453;
    line-height: 1.2;
}
.sg-gold-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.9rem 1.35rem;
    border-radius: 18px;
    background: linear-gradient(135deg, #f0c14b, #d39a1f);
    color: #1a120e;
    font-size: 0.9rem;
    font-weight: 800;
    text-decoration: none;
    box-shadow: 0 12px 24px rgba(211, 154, 31, 0.28);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.sg-gold-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 28px rgba(211, 154, 31, 0.38);
    color: #1a120e;
}
.sg-gold-rewards {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 0.9rem;
    min-height: 340px;
}
.sg-gold-ticket {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    overflow: hidden;
    border-radius: 22px;
    min-height: 164px;
    text-decoration: none;
    color: #fff;
    isolation: isolate;
    border: 1px solid rgba(255, 214, 120, 0.12);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
}
.sg-gold-ticket:first-child {
    grid-row: 1 / span 2;
    min-height: 340px;
}
.sg-gold-ticket:hover {
    transform: translateY(-5px);
    border-color: rgba(243, 196, 83, 0.45);
}
.sg-gold-ticket img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
    transition: transform 0.5s ease;
}
.sg-gold-ticket:hover img { transform: scale(1.06); }
.sg-gold-ticket::after {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 1;
    background: linear-gradient(180deg, rgba(12, 8, 6, 0.05) 20%, rgba(12, 8, 6, 0.82) 100%);
}
.sg-gold-ticket__meta {
    position: relative;
    z-index: 2;
    padding: 0.95rem 1rem 1.05rem;
}
.sg-gold-ticket__pts {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 0.45rem;
    padding: 0.22rem 0.6rem;
    border-radius: 999px;
    background: #f3c453;
    color: #1a120e;
    font-size: 0.68rem;
    font-weight: 800;
}
.sg-gold-ticket__pts .material-symbols-outlined {
    font-size: 13px;
    font-variation-settings: 'FILL' 1, 'wght' 700;
}
.sg-gold-ticket h4 {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.35;
}
.sg-gold-ticket p {
    margin: 0.15rem 0 0.55rem;
    font-size: 0.72rem;
    color: rgba(255, 244, 220, 0.7);
    font-weight: 600;
}
.sg-gold-ticket span.sg-gold-ticket__go {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    font-size: 0.72rem;
    font-weight: 800;
    color: #f3c453;
}
.sg-gold-ticket span.sg-gold-ticket__go .material-symbols-outlined { font-size: 14px; }
.sg-gold-empty {
    grid-column: 1 / -1;
    display: grid;
    place-items: center;
    text-align: center;
    padding: 2rem 1rem;
    border-radius: 22px;
    border: 1px dashed rgba(212, 175, 55, 0.28);
    background: rgba(255, 255, 255, 0.03);
}

/* Why Sofra — 3D trust cards */
.sg-why {
    padding: 3rem 1rem 3.5rem;
    perspective: 1600px;
}
.sg-why-head {
    text-align: center;
    max-width: 40rem;
    margin: 0 auto 2.4rem;
}
.sg-why-head h2 {
    font-family: 'Cairo', 'Alexandria', sans-serif;
    font-size: clamp(1.7rem, 2.6vw, 2.55rem);
    font-weight: 800;
    color: #1a120e;
    letter-spacing: -0.03em;
    margin: 0 0 0.4rem;
    line-height: 1.25;
}
.sg-why-head em {
    display: block;
    font-style: normal;
    font-size: 0.86rem;
    font-weight: 800;
    color: var(--sg-gold);
    margin-bottom: 0.45rem;
}
.sg-why-head p {
    margin: 0;
    font-size: 0.86rem;
    color: #716259;
    font-weight: 500;
    line-height: 1.7;
}
.sg-why-stage {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1.35rem;
    transform-style: preserve-3d;
    padding: 0.75rem 0.25rem 1.75rem;
}
.sg-why-card {
    position: relative;
    transform-style: preserve-3d;
    outline: none;
}
.sg-why-card__3d {
    position: relative;
    height: 100%;
    transform-style: preserve-3d;
    transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
}
.sg-why-card:nth-child(1) .sg-why-card__3d { transform: rotateY(10deg) rotateX(5deg) translateZ(8px); }
.sg-why-card:nth-child(2) .sg-why-card__3d { transform: rotateY(3deg) rotateX(3deg) translateZ(22px); }
.sg-why-card:nth-child(3) .sg-why-card__3d { transform: rotateY(-3deg) rotateX(3deg) translateZ(16px); }
.sg-why-card:nth-child(4) .sg-why-card__3d { transform: rotateY(-10deg) rotateX(5deg) translateZ(8px); }
.sg-why-card.is-featured .sg-why-card__3d {
    transform: rotateY(2deg) rotateX(2deg) translateZ(42px) scale(1.04);
}
.sg-why-card__slab {
    position: absolute;
    inset: 10px -10px -14px 10px;
    border-radius: 28px;
    background: linear-gradient(160deg, #3d2418 0%, #1a120e 100%);
    transform: translateZ(-1px);
    box-shadow: 0 28px 40px -16px rgba(22, 18, 14, 0.5);
}
.sg-why-card.is-featured .sg-why-card__slab {
    background: linear-gradient(160deg, #e07020 0%, var(--sg-primary) 55%, var(--sg-primary-dark) 100%);
}
.sg-why-card__face {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    height: 100%;
    min-height: 320px;
    padding: 1.85rem 1.2rem 1.45rem;
    border-radius: 26px;
    background:
        linear-gradient(180deg, #ffffff 0%, var(--sg-sand) 100%);
    transform: translateZ(22px);
    box-shadow:
        0 12px 28px rgba(22, 18, 14, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
}
.sg-why-card.is-featured .sg-why-card__face {
    background: linear-gradient(180deg, #fffaf7 0%, #ffece3 100%);
    box-shadow:
        0 18px 36px rgba(163, 57, 0, 0.16),
        inset 0 0 0 1.5px rgba(200, 69, 0, 0.45),
        inset 0 1px 0 #fff;
}
.sg-why-icon {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    color: #fff;
    margin-bottom: 1.15rem;
    background: radial-gradient(circle at 32% 26%, #2ea35c 0%, var(--sg-green) 55%, #004d22 100%);
    box-shadow:
        0 16px 22px rgba(0, 122, 55, 0.28),
        inset 6px 8px 12px rgba(255, 255, 255, 0.22),
        inset -8px -10px 14px rgba(0, 40, 18, 0.28);
    transform: translateZ(40px);
}
.sg-why-card.is-featured .sg-why-icon {
    background: radial-gradient(circle at 32% 26%, #ff9a4a 0%, #c84500 52%, var(--sg-primary-dark) 100%);
    color: #fff;
    box-shadow:
        0 16px 22px rgba(163, 57, 0, 0.35),
        inset 6px 8px 12px rgba(255, 255, 255, 0.32),
        inset -8px -10px 14px rgba(80, 25, 0, 0.32);
}
.sg-why-icon .material-symbols-outlined {
    font-size: 32px;
    font-variation-settings: 'FILL' 1, 'wght' 500;
    filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.2));
}
.sg-why-card h3 {
    margin: 0 0 0.45rem;
    font-size: 1.05rem;
    font-weight: 800;
    color: #1a120e;
    line-height: 1.35;
}
.sg-why-card p {
    margin: 0 0 1.25rem;
    font-size: 0.8rem;
    line-height: 1.7;
    color: #716259;
    font-weight: 500;
    flex-grow: 1;
}
.sg-why-card a {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: auto;
    padding: 0.52rem 1.15rem;
    border-radius: 999px;
    background: linear-gradient(180deg, #d64700, var(--sg-primary));
    color: #fff !important;
    text-decoration: none;
    font-size: 0.78rem;
    font-weight: 800;
    box-shadow: 0 8px 14px rgba(163, 57, 0, 0.28), inset 0 1px 0 rgba(255,255,255,0.22);
    transform: translateZ(28px);
}
.sg-why-card.is-featured a {
    background: linear-gradient(180deg, #e85d04, var(--sg-primary));
    color: #fff !important;
    box-shadow: 0 8px 14px rgba(163, 57, 0, 0.32), inset 0 1px 0 rgba(255,255,255,0.28);
}
.sg-why-card a .material-symbols-outlined { font-size: 16px; }
@media (prefers-reduced-motion: reduce) {
    .sg-why-card__3d,
    .sg-why-card:nth-child(n) .sg-why-card__3d {
        transform: none !important;
        transition: none;
    }
}


/* Join as restaurant / delivery — photo banner */
.sg-join-banner {
    position: relative;
    overflow: hidden;
    border-radius: 32px;
    min-height: 230px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 18px 40px -12px rgba(20, 12, 8, 0.35);
}
.sg-join-banner__bg {
    position: absolute;
    inset: 0;
    background: #1a120e;
}
.sg-join-banner__bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: 82% 40%;
    display: block;
    filter: brightness(0.62) saturate(0.95);
}
.sg-join-banner__overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(90deg,
            rgba(16, 10, 8, 0.82) 0%,
            rgba(16, 10, 8, 0.58) 34%,
            rgba(16, 10, 8, 0.22) 64%,
            rgba(16, 10, 8, 0.06) 100%);
}
.sg-join-banner__content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 2.4rem 1.5rem;
    max-width: 640px;
}
.sg-join-banner__title {
    margin: 0 0 1.35rem;
    color: #ffffff;
    font-family: 'Cairo', 'Alexandria', sans-serif;
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1.35;
    letter-spacing: 0;
    text-shadow: 0 2px 14px rgba(0, 0, 0, 0.35);
}
.sg-join-cta {
    display: flex;
    align-items: center;
    background: #ffffff;
    border-radius: 9999px;
    padding: 5px;
    gap: 4px;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
}
.sg-join-cta__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    padding: 0.72rem 1.55rem;
    font-size: 0.88rem;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}
.sg-join-cta__btn--rest {
    color: #4b4b4b;
    min-width: 168px;
}
.sg-join-cta__btn--rest:hover {
    background: #f3f3f3;
    color: #1f1f1f;
}
.sg-join-cta__btn--del {
    background: #6b8a72;
    color: #ffffff !important;
    min-width: 168px;
}
.sg-join-cta__btn--del:hover {
    background: #5c7862;
    transform: translateY(-1px);
}

/* ── Fullscreen Hero Viewport (Header + Hero + Ticker = 100vh) ── */
.sg-hero-fullscreen {
    min-height: calc(100vh - 74px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    background: #FAF6F0;
    overflow: hidden;
}
@supports (min-height: 100dvh) {
    .sg-hero-fullscreen {
        min-height: calc(100dvh - 74px);
    }
}
@media (max-width: 1023px) {
    .sg-hero-fullscreen {
        min-height: auto;
    }
}

/* ── Mobile App Download Banner (Foodly-style Exact Replica) ── */
.sg-app-section {
    width: 100%;
    display: flex;
    justify-content: center;
    position: relative;
    padding: 48px 0 36px;
    direction: ltr !important;
    overflow: visible;
}
.sg-app-stage-wrap {
    container-type: inline-size;
    width: 100%;
    max-width: 1240px;
    aspect-ratio: 780 / 348;
    position: relative;
    margin: 0 auto;
}
.sg-app-stage {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 780px;
    height: 220px;
    transform: translate(-50%, -50%) scale(1.52);
    transform-origin: center center;
    direction: ltr !important;
}
@supports (width: 1cqi) {
    .sg-app-stage {
        transform: translate(-50%, -50%) scale(calc(100cqi / 780px));
    }
}

/* Angled Orange Wedge SVG Banner */
.sg-app-wedge-svg {
    position: absolute;
    left: 0;
    top: 0;
    width: 780px;
    height: 220px;
    filter: drop-shadow(0 20px 35px rgba(234, 76, 37, 0.38));
    z-index: 1;
}

/* Banner Content Layer */
.sg-app-banner-content {
    position: absolute;
    left: 0;
    top: 0;
    width: 780px;
    height: 220px;
    z-index: 10;
    display: flex;
    align-items: flex-end;
    pointer-events: none;
    direction: ltr;
}

/* Headline & Avatars */
.sg-app-text-block {
    position: absolute;
    left: 236px;
    bottom: 44px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    pointer-events: auto;
    z-index: 12;
    text-align: left;
    direction: ltr;
}
.sg-app-heading {
    font-family: 'Cairo', 'Alexandria', sans-serif;
    font-size: 28px;
    line-height: 1.22;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: 0;
    text-shadow: 0 2px 8px rgba(160, 40, 10, 0.18);
    margin: 0;
}
.sg-app-avatars-group {
    display: flex;
    align-items: center;
    direction: ltr;
}
.sg-app-avatar-img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 2.5px solid #ffffff;
    object-fit: cover;
    margin-left: -8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    background: #ffe4d6;
}
.sg-app-avatar-img:first-child {
    margin-left: 0;
}
.sg-app-arrow-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #ffffff;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    color: #111111;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
}
.sg-app-arrow-btn:hover {
    transform: scale(1.08) translate(1px, -1px);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.22);
}
.sg-app-arrow-btn svg {
    width: 14px;
    height: 14px;
    stroke-width: 2.8;
}

/* White Store Arches */
.sg-app-arches-wrap {
    position: absolute;
    left: 500px;
    bottom: 0px;
    display: flex;
    gap: 16px;
    pointer-events: auto;
    z-index: 10;
    direction: ltr;
}
.sg-app-arch-card {
    width: 86px;
    height: 124px;
    background: #ffffff;
    border-radius: 43px 43px 0 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 18px;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 -2px 14px rgba(0, 0, 0, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.sg-app-arch-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 -6px 20px rgba(0, 0, 0, 0.08);
}
.sg-app-arch-icon-wrap {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sg-app-play-icon {
    width: 28px;
    height: 28px;
}
.sg-app-ios-squircle {
    width: 30px;
    height: 30px;
    background: linear-gradient(145deg, #1fa2ff 0%, #007aff 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 8px rgba(0, 122, 255, 0.35);
}
.sg-app-arch-rating-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.sg-app-arch-stars {
    display: flex;
    gap: 1.5px;
    color: #f5b301;
}
.sg-app-arch-stars svg {
    width: 8.5px;
    height: 8.5px;
    fill: currentColor;
}
.sg-app-arch-number {
    font-size: 11.5px;
    font-weight: 800;
    color: #222222;
    letter-spacing: -0.01em;
}

/* iPhone Mockup (Left) */
.sg-app-phone-container {
    position: absolute;
    left: 45px;
    top: -46px;
    width: 154px;
    height: 320px;
    background: #ffffff;
    border-radius: 26px;
    border: 3.5px solid #1a1a1a;
    box-shadow:
        0 25px 50px -12px rgba(0, 0, 0, 0.35),
        0 10px 20px -6px rgba(0, 0, 0, 0.15),
        inset 0 0 0 1px rgba(255, 255, 255, 0.8);
    z-index: 8;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    direction: ltr;
}
.sg-app-radiance {
    position: absolute;
    left: 160px;
    top: -64px;
    width: 26px;
    height: 18px;
    z-index: 7;
    pointer-events: none;
}
.sg-app-radiance svg {
    width: 100%;
    height: 100%;
}

/* Phone Screen */
.sg-app-screen {
    padding: 9px 8px 0;
    display: flex;
    flex-direction: column;
    height: 100%;
    background: #ffffff;
    direction: rtl;
    text-align: right;
    font-family: 'Cairo', 'Alexandria', sans-serif;
}
.sg-app-screen-navtop {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 7px;
}
.sg-app-screen-bars {
    display: flex;
    flex-direction: column;
    gap: 2px;
    width: 12px;
}
.sg-app-screen-bars span {
    display: block;
    height: 1.6px;
    background: #111;
    border-radius: 2px;
}
.sg-app-screen-bars span:nth-child(2) {
    width: 8px;
}
.sg-app-screen-userpic {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #f59e0b;
    overflow: hidden;
}
.sg-app-screen-userpic img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.sg-app-screen-greeting {
    font-size: 11.5px;
    font-weight: 800;
    line-height: 1.2;
    color: #111111;
    margin-bottom: 6px;
    letter-spacing: -0.02em;
}
.sg-app-screen-searchbox {
    display: flex;
    align-items: center;
    background: #f6f6f7;
    border-radius: 6px;
    padding: 3px 5px;
    gap: 4px;
    margin-bottom: 6px;
}
.sg-app-screen-searchbox svg {
    width: 8px;
    height: 8px;
    color: #999;
}
.sg-app-screen-searchbox span {
    font-size: 7px;
    color: #aaa;
    font-weight: 600;
    flex: 1;
}
.sg-app-screen-searchbtn {
    width: 13px;
    height: 13px;
    background: #ee4f27;
    border-radius: 3.5px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sg-app-screen-searchbtn svg {
    width: 7.5px;
    height: 7.5px;
    color: #fff;
}
.sg-app-screen-categories {
    display: flex;
    gap: 3px;
    margin-bottom: 6px;
}
.sg-app-screen-pill {
    padding: 2px 4.5px;
    border-radius: 8px;
    font-size: 5.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 2px;
    white-space: nowrap;
}
.sg-app-screen-pill--on {
    background: #fff1e5;
    color: #ea580c;
}
.sg-app-screen-pill--off {
    background: #f4f4f5;
    color: #71717a;
}
.sg-app-screen-products {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4.5px;
    margin-bottom: auto;
}
.sg-app-item-card {
    background: #ffffff;
    border-radius: 7px;
    padding: 3px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f1f3;
    display: flex;
    flex-direction: column;
}
.sg-app-item-card img {
    width: 100%;
    height: 40px;
    object-fit: cover;
    border-radius: 5px;
    margin-bottom: 2.5px;
}
.sg-app-item-title {
    font-size: 6.5px;
    font-weight: 800;
    color: #111;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sg-app-item-sub {
    font-size: 5px;
    color: #9ca3af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sg-app-item-cals {
    font-size: 5px;
    color: #f97316;
    font-weight: 700;
    margin: 1px 0;
}
.sg-app-item-price {
    font-size: 7px;
    font-weight: 800;
    color: #111;
}
.sg-app-screen-bottomnav {
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 4px 0 5px;
    border-top: 1px solid #f4f4f5;
    position: relative;
}
.sg-app-screen-bottomnav svg {
    width: 10px;
    height: 10px;
    color: #a1a1aa;
}
.sg-app-screen-cartcircle {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #facc15;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(250, 204, 21, 0.45);
}
.sg-app-screen-cartcircle svg {
    width: 8px;
    height: 8px;
    color: #854d0e;
}

/* Floating Elements */
.sg-app-floating-cup {
    position: absolute;
    left: 406px;
    top: -34px;
    width: 44px;
    height: 58px;
    z-index: 15;
    filter: drop-shadow(0 10px 12px rgba(0, 0, 0, 0.16));
    transform: rotate(14deg);
    pointer-events: none;
}
.sg-app-floating-cup img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.sg-app-floating-pizza {
    position: absolute;
    left: 602px;
    top: -50px;
    width: 124px;
    height: 124px;
    z-index: 15;
    filter: drop-shadow(0 16px 20px rgba(0, 0, 0, 0.24));
    transform: rotate(6deg);
    pointer-events: none;
}
.sg-app-floating-pizza img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* Decorative Dots */
.sg-app-dot-g {
    position: absolute;
    top: -46px;
    left: 772px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #166534;
    z-index: 1;
}
.sg-app-dot-y {
    position: absolute;
    top: -30px;
    left: 780px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #f59e0b;
    z-index: 1;
}

.sg-partners-band {
    background: #ffffff;
    width: 100%;
    padding: 3.25rem 0 3.5rem;
}
.sg-partners-title {
    font-family: 'Cairo', 'Alexandria', sans-serif;
    font-size: 1.45rem;
    font-weight: 800;
    color: #1a120e;
    text-align: center;
    margin: 0 0 2.1rem;
    letter-spacing: -0.02em;
}
.sg-partners-viewport {
    overflow: hidden;
    width: 100%;
    mask-image: linear-gradient(to left, transparent 0%, #000 8%, #000 92%, transparent 100%);
    -webkit-mask-image: linear-gradient(to left, transparent 0%, #000 8%, #000 92%, transparent 100%);
}
.sg-partners-track {
    display: flex;
    align-items: center;
    gap: 1.65rem;
    width: max-content;
    animation: sgPartnersMarquee 32s linear infinite;
}
.sg-partners-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 52px;
    flex-shrink: 0;
    text-decoration: none;
}
.sg-partners-logo img {
    height: 46px;
    width: auto;
    max-width: 128px;
    object-fit: contain;
    opacity: 0.88;
    transition: opacity 0.25s ease, transform 0.25s ease;
}
.sg-partners-logo:hover img {
    opacity: 1;
    transform: scale(1.04);
}
@keyframes sgPartnersMarquee {
    from { transform: translateX(0); }
    to { transform: translateX(50%); }
}
@media (prefers-reduced-motion: reduce) {
    .sg-partners-track { animation: none; }
}

</style>

{{-- 100% UNTOUCHED NATIVE MOBILE EXPERIENCE --}}
<div class="lg:hidden">
    @include('partials.home-mobile')
</div>

{{-- DESKTOP FULLSCREEN EXPERIENCE --}}
@php
    $home = $home ?? \App\Support\HomeContent::values();
    $partners = $partners ?? collect();
    $heroImage = $heroImage ?? \App\Support\HomeContent::imageUrl('hero_image');
    $joinImage = $joinImage ?? \App\Support\HomeContent::imageUrl('join_image');
@endphp
<div class="hidden lg:flex flex-col w-full">
    <div class="sg-canvas flex flex-col w-full">

    {{-- ═══════════════════════════════════════════════════════════════════
         1. FOODLY / SOFRA GAZA SIGNATURE HERO (FULLSCREEN VIEWPORT WITH TICKER)
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="sg-hero-fullscreen">

        <section class="relative px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full flex-1 flex flex-col justify-center pt-2 pb-0 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 w-full flex-1 items-stretch">
                
                {{-- Right Column (Text Content in RTL - Tajawal Font) --}}
                <div class="lg:col-span-6 flex flex-col justify-center items-start text-right z-10 font-tajawal py-6 lg:py-10">
                    
                    {{-- Status Kicker Pill --}}
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-[#fdf2db] border border-[#f5dfad] text-[#8a5d00] text-xs sm:text-sm font-bold mb-6 sm:mb-7 shadow-2xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b] animate-pulse"></span>
                        <span>{{ $home['hero_kicker'] }}</span>
                    </div>

                    {{-- Headline --}}
                    <h1 class="text-4xl sm:text-5xl lg:text-[50px] xl:text-[58px] font-black text-stone-900 leading-[1.25] mb-6 sm:mb-7 tracking-tight">
                        <span class="block">{{ $home['hero_title_line1'] }}</span>
                        <span class="block text-[#c84500] mt-1 sm:mt-2">{{ $home['hero_title_line2'] }}</span>
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-base sm:text-lg lg:text-xl text-stone-600 font-medium max-w-lg leading-relaxed mb-8 sm:mb-9">
                        {{ $home['hero_subtitle'] }}
                    </p>

                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap items-center gap-4 sm:gap-5 mb-8 sm:mb-10 w-full sm:w-auto">
                        <a href="{{ route('restaurants.index') }}" class="px-8 py-3.5 rounded-full bg-[#c84500] hover:bg-[#b03d00] text-white font-extrabold text-base shadow-lg shadow-orange-950/15 hover:shadow-xl hover:scale-[1.02] active:scale-95 transition-all text-center">
                            {{ ($canShop ?? true) ? $home['hero_cta_primary'] : 'استعرض المطاعم' }}
                        </a>
                        <a href="{{ route('restaurants.index') }}" class="px-8 py-3.5 rounded-full bg-white/90 hover:bg-stone-900 hover:text-white border-2 border-stone-800 text-stone-900 font-extrabold text-base transition-all text-center">
                            {{ $home['hero_cta_secondary'] }}
                        </a>
                    </div>

                    {{-- Social Proof / Happy Customers (Matching Foodly reference) --}}
                    <div class="flex items-center gap-4 pt-1">
                        <div class="flex items-center -space-x-2.5 space-x-reverse shrink-0 p-1">
                            <img class="relative inline-block h-11 w-11 rounded-full ring-2 ring-white object-cover shadow-xs z-40" src="{{ asset('images/avatars/customer_arab_gaza_1.jpg') }}?v={{ filemtime(public_path('images/avatars/customer_arab_gaza_1.jpg')) }}" alt="عميل من غزة">
                            <img class="relative inline-block h-11 w-11 rounded-full ring-2 ring-white object-cover shadow-xs z-30" src="{{ asset('images/avatars/customer_arab_hijab_1.jpg') }}?v={{ filemtime(public_path('images/avatars/customer_arab_hijab_1.jpg')) }}" alt="أخت فلسطينية محجبة">
                            <img class="relative inline-block h-11 w-11 rounded-full ring-2 ring-white object-cover shadow-xs z-20" src="{{ asset('images/avatars/customer_arab_1.jpg') }}?v={{ filemtime(public_path('images/avatars/customer_arab_1.jpg')) }}" alt="عميل من غزة">
                            <img class="relative inline-block h-11 w-11 rounded-full ring-2 ring-white object-cover shadow-xs z-10" src="{{ asset('images/avatars/customer_arab_3.jpg') }}?v={{ filemtime(public_path('images/avatars/customer_arab_3.jpg')) }}" alt="عميل من غزة">
                        </div>
                        <div class="flex flex-col text-right justify-center">
                            <div class="flex items-center gap-1.5 leading-none mb-1.5">
                                <div class="flex items-center gap-0.5 text-[#d65e15]">
                                    <svg class="w-4 h-4 sm:w-[17px] sm:h-[17px] fill-current shrink-0" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <svg class="w-4 h-4 sm:w-[17px] sm:h-[17px] fill-current shrink-0" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <svg class="w-4 h-4 sm:w-[17px] sm:h-[17px] fill-current shrink-0" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <svg class="w-4 h-4 sm:w-[17px] sm:h-[17px] fill-current shrink-0" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <svg class="w-4 h-4 sm:w-[17px] sm:h-[17px] fill-current shrink-0" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </div>
                                <span class="font-extrabold text-stone-900 text-lg leading-none">{{ $home['hero_rating'] }}</span>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-stone-800 leading-tight">{{ $home['hero_customers'] }}</span>
                        </div>
                    </div>

                </div>

                {{-- Left Column (Courier Hero Visual - Grounded Flush to Bottom) --}}
                <div class="lg:col-span-6 relative flex items-end justify-center self-end mt-auto h-full w-full">
                    <img src="{{ $heroImage }}" 
                         alt="كابتن توصيل سفرة غزة" 
                         class="w-full max-w-lg lg:max-w-xl xl:max-w-2xl 2xl:max-w-3xl h-auto max-h-[58vh] lg:max-h-[68vh] object-contain object-bottom block select-none -mb-1 transition-transform duration-500 hover:scale-[1.02]">
                </div>

            </div>
        </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         2. DYNAMIC LIVE NEWS & GASTRONOMY TICKER (شريط أخبار ومطابخ غزة الحي)
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="sg-news-ticker shrink-0 w-full mb-0" aria-label="شريط الأخبار والتنبيهات المباشرة">
        {{-- Right Badge --}}
        <div class="sg-news-badge">
            <span class="sg-pulse-dot"></span>
            <span>{{ $home['ticker_badge'] }}</span>
        </div>

        {{-- Marquee Stream --}}
        <div class="sg-ticker-track-wrap">
            <div class="sg-ticker-track">
                
                {{-- Set 1 of news items --}}
                @foreach($latestDishes ?? [] as $dish)
                    <a href="{{ route('restaurants.show', $dish->restaurant_id) }}" class="sg-news-item">
                        <span class="sg-news-tag sg-news-tag--dish">🍽️ طبق جديد</span>
                        <strong class="text-amber-300">{{ $dish->name }}</strong>
                        <span class="text-stone-400">لدى {{ $dish->restaurant->name ?? 'سفرة غزة' }}</span>
                        <span class="text-xs font-bold text-amber-500">({{ number_format($dish->price, 0) }} ₪)</span>
                    </a>
                    <span class="text-stone-700 select-none">•</span>
                @endforeach

                @foreach($restaurants->take(4) as $place)
                    <a href="{{ route('restaurants.show', $place) }}" class="sg-news-item">
                        <span class="sg-news-tag sg-news-tag--place">🏪 انضم حديثاً</span>
                        <strong class="text-emerald-300">{{ $place->name }}</strong>
                        <span class="text-stone-400">في {{ $place->area }}</span>
                    </a>
                    <span class="text-stone-700 select-none">•</span>
                @endforeach

                <span class="sg-news-item">
                    <span class="sg-news-tag sg-news-tag--promo">🔥 عرض اليوم</span>
                    <strong class="text-rose-300">نقاط مضاعفة</strong>
                    <span class="text-stone-300">على جميع طلبات المشاوي والشاورما هذا المساء</span>
                </span>
                <span class="text-stone-700 select-none">•</span>

                <span class="sg-news-item">
                    <span class="sg-news-tag sg-news-tag--place">🛵 أسطول الكباتن</span>
                    <span class="text-stone-300">توصيل سريع بحقائب حرارية معقمة لكافة أحياء غزة</span>
                </span>
                <span class="text-stone-700 select-none">•</span>

                {{-- Set 2 (Duplicate for continuous loop) --}}
                @foreach($latestDishes ?? [] as $dish)
                    <a href="{{ route('restaurants.show', $dish->restaurant_id) }}" class="sg-news-item">
                        <span class="sg-news-tag sg-news-tag--dish">🍽️ طبق جديد</span>
                        <strong class="text-amber-300">{{ $dish->name }}</strong>
                        <span class="text-stone-400">لدى {{ $dish->restaurant->name ?? 'سفرة غزة' }}</span>
                        <span class="text-xs font-bold text-amber-500">({{ number_format($dish->price, 0) }} ₪)</span>
                    </a>
                    <span class="text-stone-700 select-none">•</span>
                @endforeach

                @foreach($restaurants->take(4) as $place)
                    <a href="{{ route('restaurants.show', $place) }}" class="sg-news-item">
                        <span class="sg-news-tag sg-news-tag--place">🏪 انضم حديثاً</span>
                        <strong class="text-emerald-300">{{ $place->name }}</strong>
                        <span class="text-stone-400">في {{ $place->area }}</span>
                    </a>
                    <span class="text-stone-700 select-none">•</span>
                @endforeach

                <span class="sg-news-item">
                    <span class="sg-news-tag sg-news-tag--promo">🔥 عرض اليوم</span>
                    <strong class="text-rose-300">نقاط مضاعفة</strong>
                    <span class="text-stone-300">على جميع طلبات المشاوي والشاورما هذا المساء</span>
                </span>
                <span class="text-stone-700 select-none">•</span>

                <span class="sg-news-item">
                    <span class="sg-news-tag sg-news-tag--place">🛵 أسطول الكباتن</span>
                    <span class="text-stone-300">توصيل سريع بحقائب حرارية معقمة لكافة أحياء غزة</span>
                </span>
            </div>
        </div>
    </div>

    @php
        $catThemes = [
            'grill' => [
                'bg' => '#3db37a',
                'arc1' => ['d' => 'M 32 62 A 84 84 0 0 1 78 28', 'color' => '#f0b429', 'w' => '5'],
                'arc2' => ['d' => 'M 148 158 A 84 84 0 0 1 92 186', 'color' => '#ee5a24', 'w' => '5'],
                'arc3' => ['d' => 'M 168 46 A 84 84 0 0 1 186 82', 'color' => '#d5dbe3', 'w' => '3'],
            ],
            'cafe' => [
                'bg' => '#ff6a3d',
                'arc1' => ['d' => 'M 22 78 A 84 84 0 0 1 32 138', 'color' => '#f0b429', 'w' => '5'],
                'arc2' => ['d' => 'M 148 160 A 84 84 0 0 1 102 186', 'color' => '#22c55e', 'w' => '5'],
                'arc3' => ['d' => 'M 164 42 A 84 84 0 0 1 186 78', 'color' => '#d5dbe3', 'w' => '3'],
            ],
            'fries' => [
                'bg' => '#f3c23b',
                'arc1' => ['d' => 'M 22 92 A 84 84 0 0 1 40 148', 'color' => '#1f7a3a', 'w' => '5'],
                'arc2' => ['d' => 'M 152 32 A 84 84 0 0 1 186 78', 'color' => '#ee5a24', 'w' => '5'],
                'arc3' => ['d' => 'M 158 160 A 84 84 0 0 1 112 186', 'color' => '#d5dbe3', 'w' => '3'],
            ],
            'burger' => [
                'bg' => '#e63946',
                'arc1' => ['d' => 'M 23 70 A 87 87 0 0 1 70 23', 'color' => '#fbbf24', 'w' => '4.5'],
                'arc2' => ['d' => 'M 162 162 A 87 87 0 0 1 85 187', 'color' => '#14b8a6', 'w' => '4.5'],
                'arc3' => ['d' => 'M 165 48 A 87 87 0 0 1 185 85', 'color' => '#cbd5e1', 'w' => '2.5'],
            ],
            'shawarma' => [
                'bg' => '#10b981',
                'arc1' => ['d' => 'M 18 80 A 87 87 0 0 1 30 145', 'color' => '#f59e0b', 'w' => '4.5'],
                'arc2' => ['d' => 'M 162 162 A 87 87 0 0 1 105 187', 'color' => '#f97316', 'w' => '4.5'],
                'arc3' => ['d' => 'M 160 40 A 87 87 0 0 1 184 80', 'color' => '#cbd5e1', 'w' => '2.5'],
            ],
            'pizza' => [
                'bg' => '#f59e0b',
                'arc1' => ['d' => 'M 15 95 A 87 87 0 0 1 38 155', 'color' => '#10b981', 'w' => '4.5'],
                'arc2' => ['d' => 'M 148 30 A 87 87 0 0 1 184 80', 'color' => '#ef4444', 'w' => '4.5'],
                'arc3' => ['d' => 'M 162 162 A 87 87 0 0 1 115 187', 'color' => '#cbd5e1', 'w' => '2.5'],
            ],
            'sweets' => [
                'bg' => '#ec4899',
                'arc1' => ['d' => 'M 18 80 A 87 87 0 0 1 30 145', 'color' => '#f59e0b', 'w' => '4.5'],
                'arc2' => ['d' => 'M 162 162 A 87 87 0 0 1 105 187', 'color' => '#8b5cf6', 'w' => '4.5'],
                'arc3' => ['d' => 'M 160 40 A 87 87 0 0 1 184 80', 'color' => '#cbd5e1', 'w' => '2.5'],
            ],
            'seafood' => [
                'bg' => '#0284c7',
                'arc1' => ['d' => 'M 23 70 A 87 87 0 0 1 70 23', 'color' => '#f59e0b', 'w' => '4.5'],
                'arc2' => ['d' => 'M 162 162 A 87 87 0 0 1 85 187', 'color' => '#10b981', 'w' => '4.5'],
                'arc3' => ['d' => 'M 165 48 A 87 87 0 0 1 185 85', 'color' => '#cbd5e1', 'w' => '2.5'],
            ],
        ];
    @endphp
    <section class="sg-cat-band w-full" dir="rtl" aria-label="{{ $home['categories_title'] }}">
    <div class="sg-cat-section max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        {{-- Categories Slider (Exact 3 Cards on Desktop Viewport) --}}
        <div class="sg-cat-track">
            <button type="button" aria-label="السابق" id="cat-prev-btn" class="sg-cat-nav sg-cat-nav--prev">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
            <button type="button" aria-label="التالي" id="cat-next-btn" class="sg-cat-nav sg-cat-nav--next">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <div class="sg-cat-viewport" id="categories-viewport">
            <div class="sg-cat-slider" id="categories-slider">
                @foreach($categories as $category)
                    @php
                        $key = $category['key'];
                        $theme = $catThemes[$key] ?? $catThemes['grill'];
                        $foodClass = 'sg-cat-food--' . $key;
                        $linkUrl = $key === 'fries'
                            ? route('restaurants.index', ['q' => 'بطاطس'])
                            : route('restaurants.index', ['cuisine' => $category['key']]);
                    @endphp
                    <a href="{{ $linkUrl }}" class="sg-cat-slide group">
                        
                        {{-- 1. Badge Stage (Discs + Dashed Ring + Orbital Arcs) --}}
                        <div class="sg-cat-badge-wrap">
                            <svg class="sg-cat-orbit-svg" viewBox="0 0 200 200" fill="none">
                                {{-- Central Colored Disc --}}
                                <circle cx="100" cy="100" r="68" fill="{{ $theme['bg'] }}" />
                                <circle cx="100" cy="100" r="60" stroke="#ffffff" stroke-width="1.8" stroke-dasharray="3.5 3.5" fill="none" opacity="0.9" />
                                
                                {{-- Floating Accent Arcs --}}
                                <path d="{{ $theme['arc1']['d'] }}" stroke="{{ $theme['arc1']['color'] }}" stroke-width="{{ $theme['arc1']['w'] }}" stroke-linecap="round" fill="none" />
                                <path d="{{ $theme['arc2']['d'] }}" stroke="{{ $theme['arc2']['color'] }}" stroke-width="{{ $theme['arc2']['w'] }}" stroke-linecap="round" fill="none" />
                                <path d="{{ $theme['arc3']['d'] }}" stroke="{{ $theme['arc3']['color'] }}" stroke-width="{{ $theme['arc3']['w'] }}" stroke-linecap="round" fill="none" />
                            </svg>

                            {{-- Food Cut-out Layer (Overflowing Disc) --}}
                            <div class="sg-cat-food-wrap {{ $foodClass }}">
                                <img src="{{ asset($category['image']) }}?v={{ @filemtime(public_path($category['image'])) ?: time() }}" alt="{{ $category['name'] }}" class="sg-cat-food-img" loading="lazy">
                            </div>
                        </div>

                        <h4 class="sg-cat-title-en">
                            {{ $category['en_name'] ?? $category['name'] }}
                        </h4>
                        <span class="sg-cat-title-ar">
                            {{ $category['name'] }}
                        </span>

                        <div class="sg-cat-cta-btn">
                            <span>{{ ($canShop ?? true) ? $home['categories_cta'] : 'عرض القائمة' }}</span>
                            <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                        </div>

                    </a>
                @endforeach
            </div>
            </div>
        </div>
    </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         4. THE MASTER RESTAURANT EXPLORER & GRID
         ═══════════════════════════════════════════════════════════════════ --}}
    @include('partials.home-personal-picks')
    <section class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full" id="places-section">
        

        {{-- Section Head --}}
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <div>
                <span class="text-xs font-black text-amber-700 uppercase tracking-wider block mb-1">{{ $home['places_kicker'] }}</span>
                <h2 class="text-2xl sm:text-3xl font-black text-stone-900">{{ $home['places_title'] }}</h2>
                <p class="text-xs font-bold text-stone-500 mt-1">يظهر حالياً: <span id="visible-places-count" class="text-amber-700 font-extrabold">{{ $restaurants->take(8)->count() }}</span> مكان متاح للتوصيل</p>
            </div>

            {{-- Type Filter & Link --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center p-1 rounded-2xl bg-stone-200/70" id="type-filter-group">
                    <button type="button" class="px-4 py-1.5 rounded-xl font-extrabold text-xs transition-all bg-white text-stone-900 shadow-2xs" data-type="">الكل</button>
                    <button type="button" class="px-4 py-1.5 rounded-xl font-extrabold text-xs transition-all text-stone-600 hover:text-stone-900" data-type="restaurant">مطاعم</button>
                    <button type="button" class="px-4 py-1.5 rounded-xl font-extrabold text-xs transition-all text-stone-600 hover:text-stone-900" data-type="cafe">كافيهات</button>
                </div>

                <a href="{{ route('restaurants.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-stone-100 hover:bg-amber-100 text-stone-700 hover:text-amber-800 text-xs font-extrabold transition-colors">
                    <span>{{ $home['places_view_all'] }}</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                </a>
            </div>
        </div>

        @php
            $boostedPlaces = $restaurants->filter(fn ($place) => $place->isBoosted())->values();
        @endphp
        @if($boostedPlaces->isNotEmpty())
            <div class="sg-ad-rail" aria-label="إعلانات مدفوعة">
                <div class="sg-ad-rail__head">
                    <span class="material-symbols-outlined">campaign</span>
                    <span>إعلانات تظهر أولاً</span>
                </div>
                <div class="sg-ad-rail__grid">
                    @foreach($boostedPlaces->take(3) as $place)
                        <a href="{{ route('restaurants.show', $place) }}" class="sg-ad-card">
                            <img src="{{ $place->coverUrl() }}" alt="{{ $place->name }}">
                            <div>
                                <em>إعلان</em>
                                <strong>{{ $place->name }}</strong>
                                <span>{{ $place->cuisineLabel() }} • {{ $place->areaLabel() }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Dynamic Restaurant Grid --}}
        <div class="sg-place-grid" id="restaurants-grid">
            @forelse($restaurants->take(8) as $restaurant)
                @php
                    $rating = number_format($restaurant->averageRating(), 1);
                    $reviewsCount = $restaurant->reviewsCount();
                    $eta = $restaurant->type === 'cafe' ? '20-25 دقيقة' : '25-35 دقيقة';
                    $isAd = $restaurant->isBoosted();
                @endphp
                <a href="{{ route('restaurants.show', $restaurant) }}" 
                   class="sg-card place-card group {{ $isAd ? 'place-card--ad' : '' }}" 
                   data-area="{{ $restaurant->area }}" 
                   data-type="{{ $restaurant->type }}">
                    
                    {{-- Media --}}
                    <div class="sg-card__media">
                        <img src="{{ $restaurant->coverUrl() }}" alt="{{ $restaurant->name }}" class="sg-card__img" loading="lazy">
                        
                        {{-- Top Badge --}}
                        <span class="sg-card__badge-promo {{ $isAd ? 'sg-card__badge-promo--ad' : '' }}">
                            {{ $isAd ? 'إعلان' : $restaurant->badgeLabel() }}
                        </span>

                        {{-- Favorite button --}}
                        @include('partials.favorite-button', [
                            'type' => 'restaurant',
                            'id' => $restaurant->id,
                            'class' => 'sg-card__fav-btn',
                        ])

                        {{-- Delivery Time Badge --}}
                        <div class="sg-card__badge-time">
                            <span class="material-symbols-outlined text-[14px] text-amber-400">schedule</span>
                            <span>{{ $eta }}</span>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="sg-card__body">
                        
                        {{-- Title & Rating --}}
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-1.5">
                                <h3 class="text-base font-black text-stone-900 group-hover:text-amber-700 transition-colors">
                                    {{ $restaurant->name }}
                                </h3>
                                <span class="material-symbols-outlined text-amber-600 text-[18px]" title="مكان موثّق">verified</span>
                            </div>

                            <div class="flex items-center gap-1 px-2 py-0.5 rounded-lg bg-[#fff7ed] border border-[#fed7aa] text-[#9a3412] text-xs font-black shrink-0">
                                @include('partials.star-icon', ['class' => 'w-3.5 h-3.5 text-[#d65e15]'])
                                <span>{{ $rating }}</span>
                                @if($reviewsCount > 0)
                                    <span class="text-[10px] text-stone-400">({{ $reviewsCount }})</span>
                                @endif
                            </div>
                        </div>

                        {{-- Cuisine & Location --}}
                        <p class="text-xs font-semibold text-stone-500 mb-3 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px] text-stone-400">location_on</span>
                            <span>{{ $restaurant->cuisineLabel() }} • {{ $restaurant->address }}</span>
                        </p>

                        {{-- Bottom Info: Free Delivery / Points Perks --}}
                        <div class="mt-auto pt-3 border-t border-stone-100 flex items-center justify-between text-xs font-bold">
                            <span class="text-emerald-700 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">electric_moped</span>
                                <span>توصيل سريع</span>
                            </span>
                            <span class="text-amber-700 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">stars</span>
                                <span>+25 نقطة</span>
                            </span>
                        </div>

                    </div>
                </a>
            @empty
                <div class="col-span-full p-12 text-center bg-white rounded-3xl border border-stone-200">
                    <span class="material-symbols-outlined text-5xl text-stone-300 mb-2">storefront</span>
                    <h3 class="text-lg font-black text-stone-700">لا توجد مطاعم متاحة حالياً في هذه المنطقة</h3>
                    <p class="text-xs text-stone-400 mt-1">يرجى تجربة اختيار حي آخر من القائمة بالأعلى.</p>
                </div>
            @endforelse
        </div>

    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         4b. MOBILE APP DOWNLOAD (قسم التطبيق — بعد المواقع)
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(\App\Support\HomeContent::isOn('app_section_visible'))
    <div class="hidden lg:block w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        @include('partials.app-download')
    </div>
    @endif

    <section id="partners-section" class="sg-partners-band hidden lg:block w-full" aria-label="{{ $home['partners_title'] }}">
        <h2 class="sg-partners-title">{{ $home['partners_title'] }}</h2>
        @if($partners->isNotEmpty())
            @php $partnerLoop = $partners->concat($partners); @endphp
            <div class="sg-partners-viewport">
                <div class="sg-partners-track">
                    @foreach($partnerLoop as $partner)
                        @if($partner->url)
                            <a class="sg-partners-logo" href="{{ $partner->url }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $partner->imageUrl() }}" alt="{{ $partner->name }}">
                            </a>
                        @else
                            <div class="sg-partners-logo">
                                <img src="{{ $partner->imageUrl() }}" alt="{{ $partner->name }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         5. THE GOLD REWARDS VIP CLUB (نادي سفرة الذهبي)
         ═══════════════════════════════════════════════════════════════════ --}}
    <section class="sg-gold-wrap max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="sg-gold-club">
            <div class="sg-gold-grid">
                <div class="sg-gold-copy">
                    <span class="sg-gold-kicker">
                        <span class="material-symbols-outlined">stars</span>
                        {{ $home['loyalty_kicker'] }}
                    </span>
                    <h2 class="sg-gold-title">{{ $home['loyalty_title'] }}</h2>
                    <p class="sg-gold-text">{{ $home['loyalty_text'] }}</p>
                    <div class="sg-gold-actions">
                        @if($canShop ?? true)
                            <div class="sg-gold-balance">
                                <div class="sg-gold-balance__coin" aria-hidden="true">
                                    <span class="material-symbols-outlined">monetization_on</span>
                                </div>
                                <div>
                                    <small>رصيدك الحالي</small>
                                    <strong>{{ number_format(auth()->user()->points_balance ?? 0) }} نقطة</strong>
                                </div>
                            </div>
                            <a href="{{ route('redeem.create') }}" class="sg-gold-cta">
                                <span class="material-symbols-outlined text-[18px]">redeem</span>
                                <span>{{ $home['loyalty_cta'] }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="sg-gold-rewards">
                    @forelse($rewards as $reward)
                        @if($canShop ?? true)
                            <a href="{{ $reward['url'] ?? route('redeem.create') }}" class="sg-gold-ticket">
                        @else
                            <div class="sg-gold-ticket">
                        @endif
                            <img src="{{ $reward['image'] }}" alt="{{ $reward['name'] }}" loading="lazy">
                            <div class="sg-gold-ticket__meta">
                                <span class="sg-gold-ticket__pts">
                                    <span class="material-symbols-outlined">stars</span>
                                    {{ $reward['points'] }} نقطة
                                </span>
                                <h4>{{ $reward['name'] }}</h4>
                                <p>{{ $reward['place'] }}</p>
                                @if($canShop ?? true)
                                    <span class="sg-gold-ticket__go">
                                        <span>استبدال مجاناً</span>
                                        <span class="material-symbols-outlined">arrow_back</span>
                                    </span>
                                @endif
                            </div>
                        @if($canShop ?? true)
                            </a>
                        @else
                            </div>
                        @endif
                    @empty
                        <div class="sg-gold-empty">
                            <span class="material-symbols-outlined text-4xl text-amber-400 mb-2">loyalty</span>
                            <h4 class="text-sm font-black text-white">مكافآت مميزة بانتظارك</h4>
                            <p class="text-xs text-stone-400 mt-1">اطلب وجبتك المفضلة اليوم وابدأ بجمع أول نقاطك فوراً.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         6. THE PILLARS OF TRUST (لماذا سفرة غزة؟ — تصميم مطابق للصورة تماماً)
         ═══════════════════════════════════════════════════════════════════ --}}
    <section id="why-us" class="sg-why max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="sg-why-head">
            <h2>{{ $home['why_title'] }}</h2>
            <em>{{ $home['why_kicker'] }}</em>
            <p>{{ $home['why_subtitle'] }}</p>
        </div>

        <div class="sg-why-stage">
            <article class="sg-why-card" data-why-card>
                <div class="sg-why-card__3d">
                    <span class="sg-why-card__slab" aria-hidden="true"></span>
                    <div class="sg-why-card__face">
                        <div class="sg-why-icon"><span class="material-symbols-outlined">electric_moped</span></div>
                        <h3>{{ $home['why_1_title'] }}</h3>
                        <p>{{ $home['why_1_text'] }}</p>
                        <a href="{{ route('restaurants.index') }}">
                            <span>{{ $home['why_cta'] }}</span>
                            <span class="material-symbols-outlined">expand_more</span>
                        </a>
                    </div>
                </div>
            </article>

            <article class="sg-why-card is-featured" data-why-card>
                <div class="sg-why-card__3d">
                    <span class="sg-why-card__slab" aria-hidden="true"></span>
                    <div class="sg-why-card__face">
                        <div class="sg-why-icon"><span class="material-symbols-outlined">verified</span></div>
                        <h3>{{ $home['why_2_title'] }}</h3>
                        <p>{{ $home['why_2_text'] }}</p>
                        <a href="{{ route('restaurants.index') }}">
                            <span>{{ $home['why_cta'] }}</span>
                            <span class="material-symbols-outlined">expand_more</span>
                        </a>
                    </div>
                </div>
            </article>

            <article class="sg-why-card" data-why-card>
                <div class="sg-why-card__3d">
                    <span class="sg-why-card__slab" aria-hidden="true"></span>
                    <div class="sg-why-card__face">
                        <div class="sg-why-icon"><span class="material-symbols-outlined">payments</span></div>
                        <h3>{{ $home['why_3_title'] }}</h3>
                        <p>{{ $home['why_3_text'] }}</p>
                        <a href="{{ ($canShop ?? true) ? route('account.wallet') : route('restaurants.index') }}">
                            <span>{{ $home['why_cta'] }}</span>
                            <span class="material-symbols-outlined">expand_more</span>
                        </a>
                    </div>
                </div>
            </article>

            <article class="sg-why-card" data-why-card>
                <div class="sg-why-card__3d">
                    <span class="sg-why-card__slab" aria-hidden="true"></span>
                    <div class="sg-why-card__face">
                        <div class="sg-why-icon"><span class="material-symbols-outlined">favorite</span></div>
                        <h3>{{ $home['why_4_title'] }}</h3>
                        <p>{{ $home['why_4_text'] }}</p>
                        <a href="{{ route('restaurants.index') }}">
                            <span>{{ $home['why_cta'] }}</span>
                            <span class="material-symbols-outlined">expand_more</span>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         7. JOIN AS RESTAURANT / DELIVERY (بانر الشراكة)
         ═══════════════════════════════════════════════════════════════════ --}}
    <section class="py-14 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full mb-4" aria-label="{{ $home['join_title'] }}">
        <div class="sg-join-banner">
            <div class="sg-join-banner__bg" aria-hidden="true">
                <img src="{{ $joinImage }}" alt="">
                <div class="sg-join-banner__overlay"></div>
            </div>
            <div class="sg-join-banner__content">
                <h2 class="sg-join-banner__title">{!! nl2br(e($home['join_title'])) !!}</h2>
                <div class="sg-join-cta">
                    <a href="{{ route('partner.register') }}" class="sg-join-cta__btn sg-join-cta__btn--rest">{{ $home['join_cta_restaurant'] }}</a>
                    <a href="{{ route('courier.register') }}" class="sg-join-cta__btn sg-join-cta__btn--del">{{ $home['join_cta_courier'] }}</a>
                </div>
            </div>
        </div>
    </section>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* Categories Carousel — infinite auto-scroll, side arrows, drag */
    const catSlider = document.getElementById('categories-slider');
    const catViewport = document.getElementById('categories-viewport');
    const catPrev = document.getElementById('cat-prev-btn');
    const catNext = document.getElementById('cat-next-btn');

    if (catSlider && catViewport) {
        const originals = Array.from(catSlider.children);
        originals.forEach(function (node) {
            catSlider.appendChild(node.cloneNode(true));
        });

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let offset = 0;
        let dragging = false;
        let animating = false;
        let startX = 0;
        let startOffset = 0;
        let hasMoved = false;
        let autoTimer = null;

        function slideWidth() {
            return catViewport.clientWidth / 3;
        }
        function loopWidth() {
            return slideWidth() * originals.length;
        }
        function applyOffset() {
            const loop = loopWidth();
            if (loop <= 0) return;
            offset = ((offset % loop) + loop) % loop;
            catSlider.style.transform = 'translateX(' + offset + 'px)';
        }

        function animateBy(delta) {
            if (animating || !delta) return;
            animating = true;
            const start = offset;
            const target = offset + delta;
            const duration = 520;
            const startTime = performance.now();
            function step(now) {
                const t = Math.min(1, (now - startTime) / duration);
                const ease = 1 - Math.pow(1 - t, 3);
                offset = start + (target - start) * ease;
                applyOffset();
                if (t < 1) {
                    requestAnimationFrame(step);
                } else {
                    animating = false;
                }
            }
            requestAnimationFrame(step);
        }

        function snapToCard() {
            const w = slideWidth();
            if (w <= 0) return;
            const target = Math.round(offset / w) * w;
            animateBy(target - offset);
        }

        function startAuto() {
            if (autoTimer) clearInterval(autoTimer);
            if (reduceMotion) return;
            autoTimer = setInterval(function () {
                if (!dragging && !animating) {
                    animateBy(slideWidth());
                }
            }, 3200);
        }

        if (catPrev) {
            catPrev.addEventListener('click', function () {
                animateBy(-slideWidth());
                startAuto();
            });
        }
        if (catNext) {
            catNext.addEventListener('click', function () {
                animateBy(slideWidth());
                startAuto();
            });
        }

        catSlider.addEventListener('mousedown', function (e) {
            dragging = true;
            hasMoved = false;
            startX = e.pageX;
            startOffset = offset;
        });
        window.addEventListener('mouseup', function () {
            if (!dragging) return;
            dragging = false;
            if (hasMoved) snapToCard();
            startAuto();
        });
        window.addEventListener('mousemove', function (e) {
            if (!dragging) return;
            const walk = e.pageX - startX;
            if (Math.abs(walk) > 4) hasMoved = true;
            offset = startOffset + walk;
            applyOffset();
        });
        catSlider.addEventListener('click', function (e) {
            if (hasMoved) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
        catSlider.addEventListener('dragstart', function (e) { e.preventDefault(); });

        catViewport.addEventListener('wheel', function (e) {
            const delta = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
            if (Math.abs(delta) < 10) return;
            animateBy(delta > 0 ? slideWidth() : -slideWidth());
            startAuto();
        }, { passive: true });

        applyOffset();
        startAuto();
        window.addEventListener('resize', function () {
            snapToCard();
        });
    }

    /* Type Filtering */
    const placeCards = document.querySelectorAll('.place-card');
    const countDisplay = document.getElementById('visible-places-count');
    const typeButtons = document.querySelectorAll('#type-filter-group [data-type]');

    let currentType = '';

    function filterPlaces() {
        let count = 0;
        placeCards.forEach(function (card) {
            const cardType = card.getAttribute('data-type') || '';
            const matchType = !currentType || cardType === currentType;

            if (matchType) {
                card.style.display = 'flex';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        if (countDisplay) {
            countDisplay.textContent = count;
        }
    }

    typeButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            typeButtons.forEach(b => {
                b.classList.remove('bg-white', 'text-stone-900', 'shadow-2xs');
                b.classList.add('text-stone-600');
            });
            this.classList.add('bg-white', 'text-stone-900', 'shadow-2xs');
            this.classList.remove('text-stone-600');
            currentType = this.getAttribute('data-type') || '';
            filterPlaces();
        });
    });

    /* 3D tilt for why-us cards */
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('[data-why-card]').forEach(function (card) {
            const inner = card.querySelector('.sg-why-card__3d');
            if (!inner) return;
            const rest = getComputedStyle(inner).transform;
            card.addEventListener('mousemove', function (e) {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                inner.style.transition = 'transform 90ms linear';
                inner.style.transform = rest + ' rotateY(' + (x * 16) + 'deg) rotateX(' + (-y * 12) + 'deg) translateZ(18px)';
            });
            card.addEventListener('mouseleave', function () {
                inner.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
                inner.style.transform = rest;
            });
        });
    }
});
</script>
@endsection
