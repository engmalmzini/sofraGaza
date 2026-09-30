@php
    $home = $home ?? \App\Support\HomeContent::values();
    $playUrl = config('brand.app.play_store_url', '#') ?: '#';
    $iosUrl = config('brand.app.app_store_url', '#') ?: '#';
    $playRating = config('brand.app.play_rating', '4.5');
    $iosRating = config('brand.app.app_store_rating', '4.8');
    $starSvg = '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
@endphp

<section class="sg-app-section" aria-label="حمّل تطبيق سفرة غزة">
    <div class="sg-app-stage-wrap">
    <div class="sg-app-stage">

        {{-- Decorative Dots (far top right) --}}
        <span class="sg-app-dot-g" aria-hidden="true"></span>
        <span class="sg-app-dot-y" aria-hidden="true"></span>

        {{-- SVG Angled Orange Wedge Banner matching Image 1 exactly --}}
        <svg class="sg-app-wedge-svg" viewBox="0 0 780 220" fill="none" aria-hidden="true">
            <path d="M 38 65
                     L 738 0
                     C 765 -3, 780 14, 780 40
                     L 780 180
                     C 780 206, 765 220, 738 220
                     L 42 220
                     C 15 220, 0 206, 0 180
                     L 0 102
                     C 0 76, 15 67, 38 65
                     Z"
                  fill="url(#sgAppOrangeGrad)" />
            <defs>
                <linearGradient id="sgAppOrangeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#ea4d25" />
                    <stop offset="50%" stop-color="#ee5227" />
                    <stop offset="100%" stop-color="#f25a29" />
                </linearGradient>
            </defs>
        </svg>

        {{-- Floating Pizza with Slice (top right) --}}
        <div class="sg-app-floating-pizza" aria-hidden="true">
            <img src="{{ asset('images/app/pizza.png') }}" alt="">
        </div>

        {{-- Floating Drink Cup (center top slope) --}}
        <div class="sg-app-floating-cup" aria-hidden="true">
            <img src="{{ asset('images/app/cup.png') }}" alt="">
        </div>

        {{-- Content Overlay: Headline, Avatars, Store Arches --}}
        <div class="sg-app-banner-content">
            {{-- Headline & User Avatars --}}
            <div class="sg-app-text-block">
                <h2 class="sg-app-heading">
                    {{ $home['app_heading_line1'] }}<br>{{ $home['app_heading_line2'] }}
                </h2>
                <div class="sg-app-avatars-group">
                    <img class="sg-app-avatar-img" src="{{ asset('images/avatars/arab_man_1.jpg') }}" alt="عميل">
                    <img class="sg-app-avatar-img" src="{{ asset('images/avatars/customer_arab_gaza_1.jpg') }}" alt="عميل">
                    <img class="sg-app-avatar-img" src="{{ asset('images/avatars/candidates/cand1.jpg') }}" alt="عميل">
                    <img class="sg-app-avatar-img" src="{{ asset('images/avatars/customer_arab_hijab_1.jpg') }}" alt="عميلة">
                    <a href="{{ $playUrl !== '#' ? $playUrl : $iosUrl }}" @if($playUrl !== '#' || $iosUrl !== '#') target="_blank" rel="noopener noreferrer" @endif class="sg-app-arrow-btn" aria-label="حمّل تطبيقنا واستكشف العروض">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="7" y1="17" x2="17" y2="7" />
                            <polyline points="7 7 17 7 17 17" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Store Arches --}}
            <div class="sg-app-arches-wrap">
                {{-- Google Play Arch --}}
                <a href="{{ $playUrl }}" @if($playUrl !== '#') target="_blank" rel="noopener noreferrer" @endif class="sg-app-arch-card" aria-label="حمّل التطبيق من Google Play، التقييم {{ $playRating }} من 5">
                    <div class="sg-app-arch-icon-wrap">
                        <svg class="sg-app-play-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#34A853" d="M3.6 20.5 13.2 12 3.6 3.6v16.9z"/>
                            <path fill="#FBBC04" d="m3.6 3.6 9.6 8.4 4.7-2.7L5.2 1.4c-.7-.4-1.6.1-1.6.9v1.3z"/>
                            <path fill="#4285F4" d="M20.7 10.1 17.9 8.5l-4.7 3.5 4.7 3.5 2.8-1.6c.8-.5.8-1.8 0-2.3z"/>
                            <path fill="#EA4335" d="m13.2 12-9.6 8.5v.2c0 .8.9 1.3 1.6.9l12.7-7.3-4.7-2.3z"/>
                        </svg>
                    </div>
                    <div class="sg-app-arch-rating-wrap">
                        <div class="sg-app-arch-stars" aria-hidden="true">
                            {!! str_repeat($starSvg, 5) !!}
                        </div>
                        <div class="sg-app-arch-number">{{ $playRating }}/5</div>
                    </div>
                </a>

                {{-- App Store Arch --}}
                <a href="{{ $iosUrl }}" @if($iosUrl !== '#') target="_blank" rel="noopener noreferrer" @endif class="sg-app-arch-card" aria-label="حمّل التطبيق من App Store، التقييم {{ $iosRating }} من 5">
                    <div class="sg-app-arch-icon-wrap">
                        <div class="sg-app-ios-squircle" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" style="width:20px; height:20px; stroke:#ffffff; stroke-width:2.4; stroke-linecap:round; stroke-linejoin:round;">
                                <path d="M12 4v7m0 0l-5 9m5-9l5 9" />
                                <path d="M7 16h10" />
                            </svg>
                        </div>
                    </div>
                    <div class="sg-app-arch-rating-wrap">
                        <div class="sg-app-arch-stars" aria-hidden="true">
                            {!! str_repeat($starSvg, 5) !!}
                        </div>
                        <div class="sg-app-arch-number">{{ $iosRating }}/5</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Radiance sparks above phone --}}
        <div class="sg-app-radiance" aria-hidden="true">
            <svg viewBox="0 0 28 20" fill="none" stroke="#222" stroke-width="2.2" stroke-linecap="round">
                <line x1="6" y1="16" x2="2" y2="8" />
                <line x1="14" y1="18" x2="14" y2="4" />
                <line x1="22" y1="16" x2="26" y2="8" />
            </svg>
        </div>

        {{-- iPhone Mockup (Left) --}}
        <div class="sg-app-phone-container" aria-hidden="true">
            <div class="sg-app-screen">
                <div class="sg-app-screen-navtop">
                    <div class="sg-app-screen-bars">
                        <span></span>
                        <span></span>
                    </div>
                    <div class="sg-app-screen-userpic">
                        <img src="{{ asset('images/avatars/arab_man_1.jpg') }}" alt="">
                    </div>
                </div>

                <div class="sg-app-screen-greeting">
                    يلا نأكل<br>أكل فاخر 😋
                </div>

                <div class="sg-app-screen-searchbox">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <span>ابحث عن طعام...</span>
                    <div class="sg-app-screen-searchbtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="4" y1="6" x2="20" y2="6"/><circle cx="9" cy="6" r="2.5"/><line x1="4" y1="18" x2="20" y2="18"/><circle cx="15" cy="18" r="2.5"/></svg>
                    </div>
                </div>

                <div class="sg-app-screen-categories">
                    <span class="sg-app-screen-pill sg-app-screen-pill--on">🍔 سريع</span>
                    <span class="sg-app-screen-pill sg-app-screen-pill--off">🍓 فواكه</span>
                    <span class="sg-app-screen-pill sg-app-screen-pill--off">🥦 خضار</span>
                </div>

                <div class="sg-app-screen-products">
                    <div class="sg-app-item-card">
                        <img src="{{ asset('images/categories/test_burger.jpg') }}" alt="برجر">
                        <div class="sg-app-item-title">برجر جامبو</div>
                        <div class="sg-app-item-sub">برجر دجاج حار</div>
                        <div class="sg-app-item-cals">🔥 78 سعرة</div>
                        <div class="sg-app-item-price">9.80 ₪</div>
                    </div>
                    <div class="sg-app-item-card">
                        <img src="{{ asset('images/categories/shawarma.jpg') }}" alt="شاورما">
                        <div class="sg-app-item-title">شاورما دجاج</div>
                        <div class="sg-app-item-sub">دجاج حار</div>
                        <div class="sg-app-item-cals">🔥 45 سعرة</div>
                        <div class="sg-app-item-price">6.99 ₪</div>
                    </div>
                </div>

                <div class="sg-app-screen-bottomnav">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    <div class="sg-app-screen-cartcircle">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm0 10c-2.76 0-5-2.24-5-5h2c0 1.66 1.34 3 3 3s3-1.34 3-3h2c0 2.76-2.24 5-5 5z"/></svg>
                    </div>
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
            </div>
        </div>

    </div>
    </div>
</section>
