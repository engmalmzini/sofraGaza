@php
    $points = auth()->user()->points_balance ?? 0;
    $mobileBanners = \App\Models\Setting::homeBanners();
@endphp

<style>
    /* بطاقات المطاعم البارزة بظلال ناعمة دون حدود */
    .mobile-place-card {
        background-color: #ffffff !important;
        border: none !important;
        border-width: 0 !important;
        outline: none !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.09), 0 1px 4px rgba(0, 0, 0, 0.04) !important;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .mobile-place-card:hover {
        box-shadow: 0 8px 26px rgba(0, 0, 0, 0.13), 0 2px 6px rgba(0, 0, 0, 0.06) !important;
        transform: translateY(-2px);
    }
    .mobile-place-card:active {
        transform: scale(0.97) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    }
</style>

<div class="flex flex-col w-full pb-24">
    {{-- 1. قسم الإعلانات (Advertisements / Promos Banner Slider - Pure Images, Ultra Compact Height) --}}
    @if(count($mobileBanners) > 0)
        <section class="px-3.5 pt-1.5 pb-1">
            <div class="relative w-full">
                {{-- Slim horizontal banner strip: height is very low (~82px) --}}
                <div id="mobile-banner-slider" class="flex gap-2.5 overflow-x-auto snap-x snap-mandatory scrollbar-none scroll-smooth rounded-xl h-[82px] sm:h-[90px]">
                    @foreach($mobileBanners as $slideIndex => $bannerImg)
                        <div class="snap-center shrink-0 w-full h-full relative rounded-xl overflow-hidden bg-stone-100 shadow-2xs cursor-pointer group" data-slide="{{ $slideIndex }}">
                            <img src="{{ $bannerImg }}" alt="إعلان {{ $slideIndex + 1 }}" class="w-full h-full object-cover rounded-xl" loading="lazy">
                        </div>
                    @endforeach
                </div>

                {{-- Tiny Dots Indicator below (Only when more than 1 banner) --}}
                @if(count($mobileBanners) > 1)
                    <div id="mobile-banner-dots" class="flex items-center justify-center gap-1 pt-1">
                        @foreach($mobileBanners as $dotIndex => $bannerImg)
                            <button type="button" aria-label="شريحة {{ $dotIndex + 1 }}" class="banner-dot h-1 {{ $dotIndex === 0 ? 'w-4 bg-amber-500' : 'w-1 bg-stone-300' }} rounded-full transition-all duration-300" data-index="{{ $dotIndex }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- 2. التصنيفات (Categories Row - Professional Vector Food Icons) --}}
    <section class="pt-2.5 pb-2">
        <div class="flex items-center gap-3.5 overflow-x-auto scrollbar-none px-3.5 pt-3.5 pb-2" id="mobile-categories-row">
            {{-- "الكل" (active by default) --}}
            <button type="button" 
                    class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0 is-active" 
                    data-cat="">
                <div class="w-14 h-14 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-xs transition-all duration-200 group-hover:scale-105 cat-circle">
                    <svg viewBox="0 0 24 24" class="w-6 h-6 fill-current">
                        <rect x="3" y="3" width="7.5" height="7.5" rx="2.5"></rect>
                        <rect x="13.5" y="3" width="7.5" height="7.5" rx="2.5"></rect>
                        <rect x="3" y="13.5" width="7.5" height="7.5" rx="2.5"></rect>
                        <rect x="13.5" y="13.5" width="7.5" height="7.5" rx="2.5"></rect>
                    </svg>
                </div>
                <span class="text-[12px] font-extrabold text-amber-600 transition-colors cat-label">الكل</span>
            </button>

            {{-- 1. شرقي (Falafel Pita Pocket Wrap) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="palestinian">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="pitaGrad" x1="12" y1="20" x2="36" y2="44" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FDE68A"/>
                                <stop offset="0.6" stop-color="#F59E0B"/>
                                <stop offset="1" stop-color="#D97706"/>
                            </linearGradient>
                            <radialGradient id="falafelGrad" cx="30%" cy="30%" r="70%">
                                <stop stop-color="#A16207"/>
                                <stop offset="0.7" stop-color="#78350F"/>
                                <stop offset="1" stop-color="#451A03"/>
                            </radialGradient>
                        </defs>
                        <!-- Pita pocket base -->
                        <path d="M10 24 C10 37, 20 42, 28 42 C37 42, 40 34, 40 26 C40 23, 38 18, 30 18 C20 18, 10 20, 10 24 Z" fill="url(#pitaGrad)"/>
                        <path d="M12 25 C12 35, 20 40, 27 40 C34 40, 38 33, 38 26 C38 23, 36 20, 29 20 C21 20, 12 22, 12 25 Z" fill="#FEF3C7" opacity="0.4"/>
                        <!-- Crisp fresh herbs & lettuce -->
                        <path d="M14 20 C11 14, 18 10, 22 15 C25 9, 31 11, 33 16 C38 11, 41 16, 39 21 C33 22, 21 21, 14 20 Z" fill="#22C55E"/>
                        <path d="M17 17 C15 13, 19 11, 22 14 C24 10, 29 11, 31 15" stroke="#16A34A" stroke-width="1.5" stroke-linecap="round"/>
                        <!-- Red tomato wedge -->
                        <path d="M28 12 C33 12, 36 16, 34 20 C30 20, 27 16, 28 12 Z" fill="#EF4444"/>
                        <path d="M29 14 C32 14, 34 16, 33 18" stroke="#FCA5A5" stroke-width="1" stroke-linecap="round"/>
                        <!-- Crunchy falafel balls with sesame -->
                        <circle cx="19" cy="18" r="5" fill="url(#falafelGrad)"/>
                        <circle cx="17.5" cy="16.5" r="0.7" fill="#FEF08A"/>
                        <circle cx="20.5" cy="18" r="0.7" fill="#FEF08A"/>
                        <circle cx="19" cy="20" r="0.7" fill="#FEF08A"/>
                        
                        <circle cx="27" cy="17" r="5.2" fill="url(#falafelGrad)"/>
                        <circle cx="25.5" cy="15.5" r="0.7" fill="#FEF08A"/>
                        <circle cx="28.5" cy="17" r="0.7" fill="#FEF08A"/>
                        <circle cx="26.5" cy="19" r="0.7" fill="#FEF08A"/>

                        <circle cx="22" cy="24" r="5.5" fill="url(#falafelGrad)"/>
                        <circle cx="20.5" cy="22.5" r="0.8" fill="#FEF08A"/>
                        <circle cx="23.5" cy="24" r="0.8" fill="#FEF08A"/>
                        <circle cx="22" cy="26" r="0.8" fill="#FEF08A"/>

                        <!-- Creamy tahini drizzle -->
                        <path d="M15 22 Q 22 25 30 22 Q 24 28 18 29" stroke="#FEF9C3" stroke-width="1.8" stroke-linecap="round" fill="none" opacity="0.9"/>
                        <!-- Baked pita fold front -->
                        <path d="M8 26 C8 38, 18 43, 27 43 C37 43, 41 35, 41 27 C34 31, 26 31, 19 28 C14 26, 10 24, 8 26 Z" fill="url(#pitaGrad)"/>
                        <path d="M12 32 C17 38, 25 39, 32 37" stroke="#B45309" stroke-width="1.2" stroke-linecap="round" opacity="0.4"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">شرقي</span>
            </button>

            {{-- 2. غربي (Crispy Taco) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="international">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="tacoShellGrad" x1="8" y1="18" x2="40" y2="38" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FCD34D"/>
                                <stop offset="0.7" stop-color="#F59E0B"/>
                                <stop offset="1" stop-color="#D97706"/>
                            </linearGradient>
                        </defs>
                        <!-- Taco Shell Back -->
                        <path d="M7 32 C7 17, 41 17, 41 32 Z" fill="#D97706"/>
                        <!-- Savory Meat Filling -->
                        <path d="M10 31 C12 21, 20 19, 24 22 C28 19, 36 21, 38 31 Z" fill="#78350F"/>
                        <!-- Fresh Wavy Lettuce -->
                        <path d="M9 30 C12 22, 16 26, 19 23 C22 26, 26 21, 29 24 C32 21, 37 25, 39 29" stroke="#22C55E" stroke-width="3.5" stroke-linecap="round"/>
                        <!-- Diced Red Tomatoes -->
                        <rect x="15" y="21" width="3.5" height="3.5" rx="1" fill="#EF4444" transform="rotate(15 15 21)"/>
                        <rect x="23" y="19" width="3.5" height="3.5" rx="1" fill="#EF4444" transform="rotate(-10 23 19)"/>
                        <rect x="31" y="22" width="3.5" height="3.5" rx="1" fill="#EF4444" transform="rotate(20 31 22)"/>
                        <!-- Shredded Cheddar Cheese -->
                        <path d="M13 25 L16 22" stroke="#FEF08A" stroke-width="2" stroke-linecap="round"/>
                        <path d="M20 24 L23 20" stroke="#FEF08A" stroke-width="2" stroke-linecap="round"/>
                        <path d="M28 23 L31 21" stroke="#FEF08A" stroke-width="2" stroke-linecap="round"/>
                        <!-- Taco Shell Front Fold -->
                        <path d="M7 32 C8 19, 37 19, 41 32 C38 39, 10 39, 7 32 Z" fill="url(#tacoShellGrad)"/>
                        <!-- Shell Rim Highlight -->
                        <path d="M9 32 C12 23, 35 23, 39 32" stroke="#FDE68A" stroke-width="1.8" stroke-linecap="round" opacity="0.8"/>
                        <!-- Toast speckles -->
                        <circle cx="16" cy="31" r="0.8" fill="#B45309" opacity="0.6"/>
                        <circle cx="24" cy="33" r="0.9" fill="#B45309" opacity="0.6"/>
                        <circle cx="32" cy="30" r="0.8" fill="#B45309" opacity="0.6"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">غربي</span>
            </button>

            {{-- 3. شاورما (Rolled Shawarma Wrap) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="shawarma">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="shawarmaBreadGrad" x1="16" y1="12" x2="32" y2="38" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FEF3C7"/>
                                <stop offset="0.5" stop-color="#FDE68A"/>
                                <stop offset="1" stop-color="#F59E0B"/>
                            </linearGradient>
                            <linearGradient id="foilGrad" x1="15" y1="26" x2="33" y2="40" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F1F5F9"/>
                                <stop offset="0.5" stop-color="#CBD5E1"/>
                                <stop offset="1" stop-color="#94A3B8"/>
                            </linearGradient>
                        </defs>
                        <g transform="rotate(-30 24 24)">
                            <!-- Wrap Cylinder Body -->
                            <rect x="16" y="14" width="16" height="26" rx="8" fill="url(#shawarmaBreadGrad)"/>
                            
                            <!-- Grill press toast stripes -->
                            <line x1="18" y1="20" x2="29" y2="17" stroke="#B45309" stroke-width="2" stroke-linecap="round" opacity="0.75"/>
                            <line x1="17" y1="24" x2="31" y2="21" stroke="#B45309" stroke-width="2" stroke-linecap="round" opacity="0.75"/>
                            <line x1="18" y1="28" x2="30" y2="25" stroke="#B45309" stroke-width="2" stroke-linecap="round" opacity="0.75"/>
                            
                            <!-- Open top savory filling -->
                            <ellipse cx="24" cy="14" rx="7.5" ry="4" fill="#78350F"/>
                            <ellipse cx="24" cy="13.5" rx="5" ry="2.5" fill="#FEF08A"/>
                            <circle cx="21.5" cy="13.5" r="1.8" fill="#16A34A"/>
                            <circle cx="26" cy="13" r="1.8" fill="#DC2626"/>
                            <circle cx="23.5" cy="14.5" r="1.5" fill="#B45309"/>

                            <!-- Aluminum Foil Wrapper at bottom -->
                            <path d="M16 28 C16 28, 20 29.5, 24 29 C28 28.5, 32 30, 32 30 L32 34 C32 38, 16 38, 16 34 Z" fill="url(#foilGrad)"/>
                            <path d="M16 29 Q 24 31 32 30" stroke="#FFFFFF" stroke-width="1.2" stroke-linecap="round" fill="none"/>
                            <!-- Foil crinkle lines -->
                            <path d="M19 32 L22 35 L26 31 L29 36" stroke="#64748B" stroke-width="0.8" fill="none" opacity="0.7"/>
                        </g>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">شاورما</span>
            </button>

            {{-- 4. بيتزا (Artisan Pizza Slice) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="pizza">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="crustGrad" x1="10" y1="12" x2="38" y2="12" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F59E0B"/>
                                <stop offset="0.5" stop-color="#D97706"/>
                                <stop offset="1" stop-color="#B45309"/>
                            </linearGradient>
                            <linearGradient id="cheeseGrad" x1="24" y1="15" x2="24" y2="40" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FEF08A"/>
                                <stop offset="0.3" stop-color="#FBBF24"/>
                                <stop offset="1" stop-color="#F59E0B"/>
                            </linearGradient>
                            <radialGradient id="pepperoniGrad" cx="35%" cy="35%" r="65%">
                                <stop stop-color="#EF4444"/>
                                <stop offset="0.8" stop-color="#DC2626"/>
                                <stop offset="1" stop-color="#991B1B"/>
                            </radialGradient>
                        </defs>
                        <!-- Thick Puffy Crust -->
                        <path d="M10 16 C19 10, 29 10, 38 16" stroke="url(#crustGrad)" stroke-width="5.5" stroke-linecap="round"/>
                        <path d="M11 16 C19 11.5, 29 11.5, 37 16" stroke="#FDE68A" stroke-width="1.8" stroke-linecap="round" opacity="0.6"/>
                        <!-- Melted Cheese Triangular Slice Body -->
                        <path d="M11.5 17.5 L24 41 L36.5 17.5 C28 13.5, 20 13.5, 11.5 17.5 Z" fill="url(#cheeseGrad)"/>
                        
                        <!-- Savory Pepperoni Slices -->
                        <circle cx="24" cy="23" r="3.6" fill="url(#pepperoniGrad)"/>
                        <circle cx="23" cy="22" r="1" fill="#FCA5A5" opacity="0.6"/>
                        
                        <circle cx="18.5" cy="29" r="3" fill="url(#pepperoniGrad)"/>
                        <circle cx="17.8" cy="28.2" r="0.8" fill="#FCA5A5" opacity="0.6"/>
                        
                        <circle cx="29" cy="28.5" r="3.2" fill="url(#pepperoniGrad)"/>
                        <circle cx="28.2" cy="27.6" r="0.9" fill="#FCA5A5" opacity="0.6"/>

                        <!-- Green Basil Leaves -->
                        <path d="M23 33 C25 31, 26 34, 24 35 C22 35, 22 33, 23 33 Z" fill="#16A34A"/>
                        <path d="M17 21 C18 19, 20 21, 19 22 C18 22, 17 21, 17 21 Z" fill="#16A34A"/>
                        <path d="M30 20 C32 19, 32 22, 31 22 C30 22, 29 20, 30 20 Z" fill="#16A34A"/>

                        <!-- Dripping cheese tip -->
                        <path d="M23.5 40 Q 24 43 24.5 40" stroke="#FBBF24" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">بيتزا</span>
            </button>

            {{-- 5. برجر (Gourmet Cheeseburger) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="burger">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="burgerBunGrad" x1="12" y1="12" x2="36" y2="24" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FBBF24"/>
                                <stop offset="0.6" stop-color="#F59E0B"/>
                                <stop offset="1" stop-color="#D97706"/>
                            </linearGradient>
                            <linearGradient id="pattyGrad" x1="12" y1="28" x2="36" y2="33" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#854D0E"/>
                                <stop offset="0.5" stop-color="#713F12"/>
                                <stop offset="1" stop-color="#451A03"/>
                            </linearGradient>
                        </defs>
                        <!-- Top Glazed Brioche Bun -->
                        <path d="M11 21 C11 11, 37 11, 37 21 Z" fill="url(#burgerBunGrad)"/>
                        <path d="M14 15 C17 12.5, 31 12.5, 34 15" stroke="#FEF3C7" stroke-width="1.2" stroke-linecap="round" opacity="0.6"/>
                        <!-- Sesame Seeds -->
                        <ellipse cx="18" cy="16" rx="0.9" ry="0.6" fill="#FFFBEB" transform="rotate(-20 18 16)"/>
                        <ellipse cx="24" cy="14" rx="0.9" ry="0.6" fill="#FFFBEB"/>
                        <ellipse cx="30" cy="16" rx="0.9" ry="0.6" fill="#FFFBEB" transform="rotate(20 30 16)"/>
                        <ellipse cx="21" cy="18" rx="0.8" ry="0.5" fill="#FFFBEB" transform="rotate(10 21 18)"/>
                        <ellipse cx="27" cy="18" rx="0.8" ry="0.5" fill="#FFFBEB" transform="rotate(-15 27 18)"/>

                        <!-- Juicy Tomato Slice -->
                        <rect x="12" y="21.5" width="24" height="2.8" rx="1.4" fill="#EF4444"/>

                        <!-- Melted Cheddar Cheese Layer (with drips) -->
                        <path d="M11 24.5 L37 24.5 L35 28 L29 25.5 L24 29.5 L19 25.5 L13 28 Z" fill="#FACC15"/>

                        <!-- Grilled Beef Patty -->
                        <rect x="11.5" y="27.5" width="25" height="4.5" rx="2.25" fill="url(#pattyGrad)"/>
                        <!-- Char grill lines on patty -->
                        <line x1="16" y1="28.5" x2="18" y2="31" stroke="#291203" stroke-width="1" stroke-linecap="round"/>
                        <line x1="23" y1="28.5" x2="25" y2="31" stroke="#291203" stroke-width="1" stroke-linecap="round"/>
                        <line x1="30" y1="28.5" x2="32" y2="31" stroke="#291203" stroke-width="1" stroke-linecap="round"/>

                        <!-- Crisp Wavy Lettuce -->
                        <path d="M10 32.5 C12 30.5, 15 33.5, 18 31.5 C21 33.5, 24 30.5, 27 32.5 C30 30.5, 33 33.5, 36 31.5 C37 32.5, 38 32.5, 38 32.5" stroke="#22C55E" stroke-width="2.8" stroke-linecap="round"/>

                        <!-- Bottom Bun -->
                        <path d="M12.5 33.5 C12.5 37.5, 35.5 37.5, 35.5 33.5 Z" fill="url(#burgerBunGrad)"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">برجر</span>
            </button>

            {{-- 6. مشاوي (Grill Skewers) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="grill">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="meatGrad" x1="0" y1="0" x2="1" y2="1">
                                <stop stop-color="#991B1B"/>
                                <stop offset="0.6" stop-color="#78350F"/>
                                <stop offset="1" stop-color="#451A03"/>
                            </linearGradient>
                        </defs>
                        <!-- Metal Skewer Rod -->
                        <line x1="8" y1="40" x2="40" y2="8" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>
                        <line x1="8" y1="40" x2="12" y2="36" stroke="#64748B" stroke-width="3.5" stroke-linecap="round"/>
                        <!-- Grilled Meat Chunks -->
                        <rect x="14" y="28" width="7" height="6.5" rx="2.5" fill="url(#meatGrad)" transform="rotate(-45 17.5 31.2)"/>
                        <line x1="14" y1="31" x2="19" y2="33" stroke="#270F03" stroke-width="1.2"/>
                        <!-- Charred Pepper -->
                        <rect x="19" y="23" width="5.5" height="5" rx="1.5" fill="#DC2626" transform="rotate(-45 21.7 25.5)"/>
                        <!-- Meat Chunk 2 -->
                        <rect x="23.5" y="18.5" width="7.5" height="7" rx="2.5" fill="url(#meatGrad)" transform="rotate(-45 27.2 22)"/>
                        <line x1="24" y1="21" x2="29" y2="23" stroke="#270F03" stroke-width="1.2"/>
                        <!-- Grilled Onion Ring -->
                        <circle cx="31.5" cy="16.5" r="3.2" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.2"/>
                        <!-- Meat Chunk 3 -->
                        <rect x="31" y="11" width="7" height="6.5" rx="2.5" fill="url(#meatGrad)" transform="rotate(-45 34.5 14.2)"/>
                        <line x1="32" y1="13" x2="36" y2="15" stroke="#270F03" stroke-width="1.2"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">مشاوي</span>
            </button>

            {{-- 7. كافيهات (Specialty Coffee Cup) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="cafe">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="coffeeCupGrad" x1="14" y1="18" x2="34" y2="36" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFFFFF"/>
                                <stop offset="1" stop-color="#E2E8F0"/>
                            </linearGradient>
                        </defs>
                        <!-- Steam wisps -->
                        <path d="M20 12 C19 9, 22 8, 21 5" stroke="#CBD5E1" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M26 13 C25 10, 28 9, 27 6" stroke="#CBD5E1" stroke-width="1.8" stroke-linecap="round"/>
                        <!-- Saucer plate -->
                        <ellipse cx="23" cy="38" rx="14" ry="3.5" fill="#CBD5E1"/>
                        <ellipse cx="23" cy="37" rx="13" ry="3" fill="#E2E8F0"/>
                        <ellipse cx="23" cy="36.5" rx="11" ry="2" fill="#F8FAFC"/>
                        <!-- Cup Handle -->
                        <path d="M30 22 C37 22, 37 31, 30 31" stroke="#CBD5E1" stroke-width="3" stroke-linecap="round"/>
                        <!-- Cup Body -->
                        <path d="M13 18 L15.5 33 C15.5 35.5, 30.5 35.5, 30.5 33 L33 18 Z" fill="url(#coffeeCupGrad)"/>
                        <!-- Coffee Surface -->
                        <ellipse cx="23" cy="18" rx="10" ry="3.5" fill="#78350F"/>
                        <!-- Steamed Milk Latte Art (Heart) -->
                        <path d="M23 16.5 C22 15, 19.5 15.5, 20.5 17.5 C21.5 19, 23 20.5, 23 20.5 C23 20.5, 24.5 19, 25.5 17.5 C26.5 15.5, 24 15, 23 16.5 Z" fill="#FEF3C7"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">كافيهات</span>
            </button>

            {{-- 8. حلويات (Knafeh & Sweets) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="sweets">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="knafehGrad" x1="10" y1="20" x2="38" y2="36" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FB923C"/>
                                <stop offset="0.6" stop-color="#F97316"/>
                                <stop offset="1" stop-color="#EA580C"/>
                            </linearGradient>
                        </defs>
                        <!-- Serving Plate -->
                        <ellipse cx="24" cy="38" rx="15" ry="4" fill="#E2E8F0"/>
                        <ellipse cx="24" cy="37" rx="14" ry="3.5" fill="#F8FAFC"/>
                        <!-- Stretchy Warm Cheese Base -->
                        <path d="M12 28 L36 28 L36 35 C36 36.5, 12 36.5, 12 35 Z" fill="#FEF3C7"/>
                        <!-- Golden Crispy Orange Knafeh Crust -->
                        <path d="M11 25 L24 17 L37 25 L37 29 L11 29 Z" fill="url(#knafehGrad)"/>
                        <line x1="13" y1="23" x2="35" y2="23" stroke="#FDE047" stroke-width="1.2" stroke-linecap="round" opacity="0.6"/>
                        <!-- Crushed Green Pistachios -->
                        <circle cx="21" cy="20" r="1.2" fill="#65A30D"/>
                        <circle cx="24" cy="18" r="1.2" fill="#4D7C0F"/>
                        <circle cx="27" cy="20" r="1.2" fill="#65A30D"/>
                        <circle cx="23" cy="22" r="1" fill="#4D7C0F"/>
                        <circle cx="26" cy="23" r="1" fill="#65A30D"/>
                        <!-- Red Rose Petal Accent -->
                        <ellipse cx="24" cy="16" rx="1.8" ry="1.2" fill="#E11D48" transform="rotate(-15 24 16)"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">حلويات</span>
            </button>

            {{-- 9. فطور (Sunny-side Up Egg & Breakfast) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="breakfast">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <radialGradient id="yolkGrad" cx="35%" cy="35%" r="65%">
                                <stop stop-color="#FDE047"/>
                                <stop offset="0.6" stop-color="#F59E0B"/>
                                <stop offset="1" stop-color="#D97706"/>
                            </radialGradient>
                        </defs>
                        <!-- Mini Skillet Pan -->
                        <circle cx="24" cy="24" r="17" fill="#334155"/>
                        <circle cx="24" cy="24" r="15" fill="#1E293B"/>
                        <rect x="36" y="22" width="8" height="4" rx="2" fill="#334155"/>
                        <!-- Egg White (Albumin) with crisp edges -->
                        <path d="M14 24 C14 18, 19 15, 25 15 C31 15, 34 19, 34 24 C34 30, 29 33, 23 33 C17 33, 14 29, 14 24 Z" fill="#F8FAFC"/>
                        <path d="M15 25 C16 19, 20 16, 26 16 C30 16, 33 20, 33 24" stroke="#FEF9C3" stroke-width="1" opacity="0.8"/>
                        <!-- Golden Runny Egg Yolk -->
                        <circle cx="23.5" cy="23.5" r="5.5" fill="url(#yolkGrad)"/>
                        <circle cx="21.5" cy="21.5" r="1.5" fill="#FFFFFF" opacity="0.8"/>
                        <!-- Herb Garnish Specks -->
                        <circle cx="29" cy="22" r="0.7" fill="#15803D"/>
                        <circle cx="18" cy="26" r="0.7" fill="#15803D"/>
                        <circle cx="26" cy="29" r="0.7" fill="#15803D"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">فطور</span>
            </button>

            {{-- 10. بحريات (Seafood / Fresh Fish) --}}
            <button type="button" class="mobile-cat-btn group flex flex-col items-center gap-1 shrink-0" data-cat="seafood">
                <div class="w-14 h-14 rounded-full bg-white border border-stone-200/90 flex items-center justify-center shadow-2xs transition-all duration-200 group-hover:scale-105 cat-circle p-2">
                    <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                        <defs>
                            <linearGradient id="fishGrad" x1="10" y1="20" x2="38" y2="28" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#38BDF8"/>
                                <stop offset="0.6" stop-color="#0284C7"/>
                                <stop offset="1" stop-color="#0369A1"/>
                            </linearGradient>
                        </defs>
                        <!-- Fish Tail Fin -->
                        <polygon points="12,24 6,17 9,24 6,31" fill="#0284C7"/>
                        <!-- Fish Dorsal & Ventral Fins -->
                        <path d="M24 17 C26 13, 29 14, 28 17 Z" fill="#0369A1"/>
                        <path d="M24 31 C26 35, 29 34, 28 31 Z" fill="#0369A1"/>
                        <!-- Fish Body -->
                        <path d="M38 24 C33 16, 18 17, 10 24 C18 31, 33 32, 38 24 Z" fill="url(#fishGrad)"/>
                        <path d="M12 24 C18 19, 31 19, 36 24" stroke="#BAE6FD" stroke-width="1.2" opacity="0.6"/>
                        <!-- Fish Eye -->
                        <circle cx="33.5" cy="22.5" r="2" fill="#FFFFFF"/>
                        <circle cx="34" cy="22.5" r="1.2" fill="#0F172A"/>
                        <circle cx="34.3" cy="22.2" r="0.4" fill="#FFFFFF"/>
                        <!-- Fresh Yellow Lemon Wedge -->
                        <path d="M20 28 C23 28, 25 30, 24 33 C21 33, 19 31, 20 28 Z" fill="#FACC15"/>
                        <path d="M21 29 C22.5 29, 23.5 30.5, 23 32" stroke="#FEF08A" stroke-width="0.8"/>
                    </svg>
                </div>
                <span class="text-[12px] font-bold text-stone-700 group-hover:text-amber-600 transition-colors cat-label">بحريات</span>
            </button>
        </div>
    </section>

    {{-- 3. المطاعم (Restaurant Cards - 2 per row) --}}
    <section class="pt-1">
        <div class="grid grid-cols-2 gap-3 px-3.5" id="mobile-restaurants-grid">
            @forelse($restaurants as $index => $restaurant)
                @php
                    $rating = number_format($restaurant->averageRating(), 1);
                    $isNew = $index === 1 || $index === 6 || $restaurant->created_at->diffInDays(now()) < 14;
                @endphp
                <a href="{{ route('restaurants.show', $restaurant) }}" 
                   class="mobile-place-card group bg-white rounded-2xl overflow-hidden shadow-[0_4px_16px_rgba(0,0,0,0.08)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.12)] transition-all duration-200 flex flex-col active:scale-[0.98]"
                   data-cuisine="{{ $restaurant->cuisine }}"
                   data-area="{{ $restaurant->area }}"
                   data-name="{{ $restaurant->name }}">
                    
                    {{-- Cover Image --}}
                    <div class="relative w-full aspect-[4/3] bg-stone-100 overflow-hidden">
                        <img src="{{ $restaurant->coverUrl() }}" 
                             alt="{{ $restaurant->name }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             loading="lazy">
                        
                        {{-- Top Badges: 'جديد' or Rating Pill '★ 4.8' (Top-Left) --}}
                        @if($isNew && $index % 3 === 1)
                            <div class="absolute top-2 left-2 bg-amber-400 text-stone-900 font-extrabold text-[10px] px-2.5 py-0.5 rounded-full shadow-xs">
                                جديد
                            </div>
                        @else
                            <div class="absolute top-2 left-2 bg-black/60 backdrop-blur-xs text-white text-[11px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-xs">
                                <span class="text-amber-400 text-[12px] leading-none">★</span>
                                <span>{{ $rating > 0 ? $rating : '4.5' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="p-2.5 pb-3 flex flex-col gap-0.5 text-right bg-white">
                        {{-- Line 1: Green indicator dot + Restaurant Name --}}
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <h4 class="font-bold text-[13px] text-stone-900 truncate leading-snug group-hover:text-primary transition-colors">
                                {{ $restaurant->name }}
                            </h4>
                        </div>

                        {{-- Line 2: Cuisine / Description --}}
                        <p class="text-[11px] text-stone-400 font-medium truncate leading-tight pr-3.5">
                            {{ $restaurant->cuisineLabel() }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="col-span-2 py-12 text-center text-stone-400">
                    <span class="material-symbols-outlined text-4xl mb-2 text-stone-300">restaurant</span>
                    <p class="text-sm font-bold">لا توجد مطاعم متاحة حالياً</p>
                </div>
            @endforelse
        </div>

        {{-- No results state when category filter has no matching restaurants --}}
        <div id="mobile-no-results" class="hidden px-3.5 py-12 text-center flex flex-col items-center justify-center">
            <div class="w-16 h-16 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-3xl">search_off</span>
            </div>
            <h4 class="text-sm font-bold text-stone-800">لا توجد مطاعم في هذا التصنيف</h4>
            <p class="text-xs text-stone-400 mt-1 mb-4">اختر تصنيفاً آخر أو تصفح جميع المطاعم</p>
            <button type="button" id="reset-cat-btn" class="px-4 py-2 rounded-full bg-amber-500 text-white text-xs font-bold shadow-xs">
                عرض جميع المطاعم
            </button>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Banner Slider Logic
        const slider = document.getElementById('mobile-banner-slider');
        const dots = document.querySelectorAll('#mobile-banner-dots .banner-dot');
        let currentSlide = 0;
        let autoSlideTimer = null;

        if (slider && dots.length) {
            const updateDots = (index) => {
                dots.forEach((dot, i) => {
                    if (i === index) {
                        dot.classList.remove('w-1', 'bg-stone-300');
                        dot.classList.add('w-4', 'bg-amber-500');
                    } else {
                        dot.classList.remove('w-4', 'bg-amber-500');
                        dot.classList.add('w-1', 'bg-stone-300');
                    }
                });
                currentSlide = index;
            };

            const scrollToSlide = (index) => {
                const slides = slider.querySelectorAll('[data-slide]');
                if (slides[index]) {
                    slider.scrollTo({
                        left: slides[index].offsetLeft,
                        behavior: 'smooth'
                    });
                    updateDots(index);
                }
            };

            dots.forEach((dot) => {
                dot.addEventListener('click', () => {
                    const idx = parseInt(dot.dataset.index, 10);
                    scrollToSlide(idx);
                    resetTimer();
                });
            });

            // Update on manual scroll
            let scrollTimeout;
            slider.addEventListener('scroll', () => {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    const slides = slider.querySelectorAll('[data-slide]');
                    const scrollLeft = slider.scrollLeft;
                    let closestIdx = 0;
                    let minDiff = Infinity;
                    slides.forEach((slide, i) => {
                        const diff = Math.abs(slide.offsetLeft - scrollLeft);
                        if (diff < minDiff) {
                            minDiff = diff;
                            closestIdx = i;
                        }
                    });
                    updateDots(closestIdx);
                }, 100);
            }, { passive: true });

            const startTimer = () => {
                autoSlideTimer = setInterval(() => {
                    const next = (currentSlide + 1) % dots.length;
                    scrollToSlide(next);
                }, 4500);
            };

            const resetTimer = () => {
                clearInterval(autoSlideTimer);
                startTimer();
            };

            slider.addEventListener('touchstart', () => clearInterval(autoSlideTimer), { passive: true });
            slider.addEventListener('touchend', resetTimer, { passive: true });

            startTimer();
        }

        // Category Filter Logic
        const catButtons = document.querySelectorAll('.mobile-cat-btn');
        const cards = document.querySelectorAll('.mobile-place-card');
        const noResults = document.getElementById('mobile-no-results');
        const resetBtn = document.getElementById('reset-cat-btn');

        const filterByCategory = (cat) => {
            let visibleCount = 0;
            catButtons.forEach(btn => {
                const btnCat = btn.dataset.cat || '';
                const isActive = btnCat === cat;
                btn.classList.toggle('is-active', isActive);
                const circle = btn.querySelector('.cat-circle');
                const label = btn.querySelector('.cat-label');
                
                if (btnCat === '') {
                    // "الكل" (All) button
                    if (isActive) {
                        circle?.classList.remove('bg-white', 'text-stone-500', 'border', 'border-stone-200/90');
                        circle?.classList.add('bg-amber-500', 'text-white', 'shadow-xs');
                    } else {
                        circle?.classList.remove('bg-amber-500', 'text-white', 'shadow-xs');
                        circle?.classList.add('bg-white', 'text-stone-500', 'border', 'border-stone-200/90');
                    }
                } else {
                    // Food category buttons with colored illustrations
                    if (isActive) {
                        circle?.classList.add('ring-2', 'ring-amber-500', 'ring-offset-2', 'border-amber-400', 'scale-105', 'shadow-xs');
                        circle?.classList.remove('scale-100');
                    } else {
                        circle?.classList.remove('ring-2', 'ring-amber-500', 'ring-offset-2', 'border-amber-400', 'scale-105', 'shadow-xs');
                        circle?.classList.add('scale-100');
                    }
                }

                if (isActive) {
                    label?.classList.remove('text-stone-700');
                    label?.classList.add('text-amber-600', 'font-extrabold');
                } else {
                    label?.classList.remove('text-amber-600', 'font-extrabold');
                    label?.classList.add('text-stone-700');
                }
            });

            cards.forEach(card => {
                const cuisine = card.dataset.cuisine || '';
                const show = !cat || cuisine === cat;
                card.classList.toggle('hidden', !show);
                if (show) visibleCount++;
            });

            if (noResults) {
                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                    noResults.classList.add('flex');
                } else {
                    noResults.classList.add('hidden');
                    noResults.classList.remove('flex');
                }
            }
        };

        catButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const cat = btn.dataset.cat || '';
                filterByCategory(cat);
            });
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                filterByCategory('');
            });
        }
    });
</script>
