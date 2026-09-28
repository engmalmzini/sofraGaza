@php
    $tier = $tier ?? $user->tier();
    $allTiers = [
        ['key' => 'starter', 'name' => 'مبتدئ', 'min' => 0, 'max' => 150, 'threshold' => '0 ₪'],
        ['key' => 'bronze', 'name' => 'برونزي', 'min' => 150, 'max' => 300, 'threshold' => '150 ₪'],
        ['key' => 'silver', 'name' => 'فضي', 'min' => 300, 'max' => 600, 'threshold' => '300 ₪'],
        ['key' => 'gold', 'name' => 'ذهبي', 'min' => 600, 'max' => 1000, 'threshold' => '600 ₪'],
        ['key' => 'platinum', 'name' => 'بلاتيني (VIP)', 'min' => 1000, 'max' => null, 'threshold' => '1000 ₪'],
    ];
@endphp

<style>
    .account-menu-icon {
        width: 36px !important;
        height: 36px !important;
        border-radius: 12px !important;
        background-color: #fffbeb !important;
        color: #d97706 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .account-menu-icon svg {
        fill: currentColor !important;
    }
</style>

<div class="flex flex-col w-full px-3.5 py-3 space-y-3.5 pb-24 bg-[#F6F7F9] min-h-screen">

    {{-- 1. بطاقة الهوية والملف الشخصي (Profile Card) --}}
    <div class="bg-white rounded-2xl p-4 shadow-[0_4px_16px_rgba(0,0,0,0.06)] border-0 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            {{-- الصورة الرمزية مع شارة مستوى الولاء --}}
            <div class="relative w-14 h-14 rounded-2xl bg-stone-900 text-white font-black text-xl flex items-center justify-center shadow-xs shrink-0">
                @if($user->photo_path)
                    <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}" class="w-full h-full rounded-2xl object-cover">
                @else
                    <span>{{ mb_substr($user->name, 0, 1) }}</span>
                @endif
                <span class="absolute -bottom-1 -left-1 w-5 h-5 rounded-full bg-white ring-2 ring-white shadow-xs flex items-center justify-center z-10" title="مستوى الولاء: {{ $tier['name'] }}">
                    @include('partials.tier-icon', ['tier' => $tier['key'], 'class' => 'w-full h-full'])
                </span>
            </div>

            {{-- الاسم ورقم الهاتف --}}
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <h1 class="text-base font-extrabold text-stone-900 truncate">{{ $user->name }}</h1>
                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-amber-50 border border-amber-200/60 text-amber-800 text-[10px] font-bold">
                        <span>{{ $tier['name'] }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-stone-400 mt-1 font-medium">
                    <span class="flex items-center gap-1 font-mono text-stone-600">
                        <span class="material-symbols-outlined text-[13px] text-stone-400">call</span>
                        <span dir="ltr">{{ $user->phone }}</span>
                    </span>
                    <span>•</span>
                    <span>عضو منذ {{ $user->created_at->format('Y/m') }}</span>
                </div>
            </div>
        </div>

        {{-- لوحة المطعم إذا كان شريكاً --}}
        @if($user->isRestaurantOwner())
            <a href="{{ auth()->user()->partnerPanelRoute() }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-primary text-white text-[11px] font-bold shrink-0 shadow-2xs">
                <span class="material-symbols-outlined text-[15px]">storefront</span>
                <span>لوحتي</span>
            </a>
        @endif
    </div>

    {{-- 2. شريط الرصيد والنقاط (Wallet & Points Bar) --}}
    <div class="grid grid-cols-2 gap-3">
        {{-- كرت المحفظة --}}
        <div class="bg-white rounded-2xl p-3.5 shadow-[0_4px_16px_rgba(0,0,0,0.06)] border-0 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500">رصيد المحفظة</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="flex items-baseline gap-1">
                <strong class="font-mono font-black text-lg text-stone-900 leading-none">{{ number_format($user->wallet_balance ?? 0, 2) }}</strong>
                <span class="ils text-xs font-bold text-stone-500">₪</span>
            </div>
            <div class="flex items-center gap-1.5 pt-1 border-t border-stone-100">
                <a href="{{ route('account.wallet.topup') }}" class="flex-1 text-center py-1 rounded-lg bg-primary hover:bg-stone-900 text-white text-[11px] font-bold transition-colors shadow-2xs">
                    + شحن
                </a>
                <a href="{{ route('account.wallet') }}" class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 text-[11px] font-semibold transition-colors" title="سجل العمليات">
                    السجل
                </a>
            </div>
        </div>

        {{-- كرت النقاط --}}
        <div class="bg-white rounded-2xl p-3.5 shadow-[0_4px_16px_rgba(0,0,0,0.06)] border-0 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500">نقاط الولاء</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50/80 flex items-center justify-center shadow-2xs">
                    @include('partials.gold-coin-icon', ['class' => 'w-6 h-6'])
                </div>
            </div>
            <div class="flex items-baseline gap-1">
                <strong class="font-mono font-black text-lg text-stone-900 leading-none">{{ number_format($user->points_balance ?? 0) }}</strong>
                <span class="text-xs font-bold text-stone-500">نقطة</span>
            </div>
            <div class="flex items-center gap-1.5 pt-1 border-t border-stone-100">
                <a href="{{ route('redeem.create') }}" class="flex-1 text-center py-1 rounded-lg bg-stone-900 hover:bg-black text-white text-[11px] font-bold transition-colors shadow-2xs">
                    استبدال
                </a>
                <a href="{{ route('account.points') }}" class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 text-[11px] font-semibold transition-colors" title="سجل النقاط">
                    السجل
                </a>
            </div>
        </div>
    </div>

    {{-- 3. مستوى الولاء ومسار الترقية المختصر (Loyalty Tier Card - Black & Orange Theme) --}}
    <div class="relative overflow-hidden rounded-2xl p-4 shadow-[0_6px_24px_rgba(0,0,0,0.22)] border-0 space-y-3.5 text-white" style="background: linear-gradient(145deg, #18181b 0%, #0d0d0f 100%);">
        
        {{-- Ambient orange subtle glow in top-left corner --}}
        <div class="absolute -top-10 -left-10 w-28 h-28 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="p-0.5 rounded-full ring-1 ring-amber-400/40">
                    @include('partials.tier-icon', ['tier' => $tier['key'], 'class' => 'w-7 h-7'])
                </div>
                <div>
                    <span class="text-[10px] text-stone-400 block leading-tight">مستوى الحساب</span>
                    <strong class="text-[13px] font-black text-white">{{ $tier['name'] }}</strong>
                </div>
            </div>
            @if($tier['next_tier'])
                <div class="text-left">
                    <span class="text-[10px] text-stone-400 block leading-tight">المتبقي للترقية</span>
                    <strong class="text-xs font-mono font-black text-amber-400">{{ number_format($tier['remaining'], 0) }} ₪</strong>
                </div>
            @else
                <span class="text-[10px] font-bold text-amber-400 bg-amber-400/10 border border-amber-400/30 px-2 py-0.5 rounded-full">أعلى مستوى (VIP)</span>
            @endif
        </div>

        {{-- شريط التقدم للترقية --}}
        @if($tier['next_tier'])
            <div class="relative z-10 space-y-1.5">
                <div class="w-full bg-stone-800 rounded-full h-2 overflow-hidden p-0.5">
                    <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-400 h-full rounded-full transition-all duration-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]" style="width: {{ max(4, $tier['progress_percent']) }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[10.5px]">
                    <span class="text-stone-400">مشترياتك: <strong class="text-white font-mono font-bold">{{ number_format($tier['current_spent'], 1) }} ₪</strong></span>
                    <span class="text-stone-400">المستوى القادم: <strong class="text-amber-400 font-bold">{{ $tier['next_tier'] }}</strong> <span class="text-stone-500 font-mono">({{ $tier['next_min'] }} ₪)</span></span>
                </div>
            </div>
        @endif

        {{-- خريطة المستويات كقائمة قابلة للتوسيع (للحفاظ على اختصار الصفحة) --}}
        <details class="group relative z-10 pt-2.5 border-t border-stone-800/80">
            <summary class="flex items-center justify-between cursor-pointer list-none text-[11px] font-bold text-stone-300 hover:text-amber-400 transition-colors py-0.5">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-amber-400">military_tech</span>
                    <span class="text-white font-semibold">خريطة ومزايا مستويات سفرة غزة</span>
                </span>
                <span class="material-symbols-outlined text-[16px] text-amber-400/70 group-open:rotate-180 transition-transform">expand_more</span>
            </summary>
            
            <div class="pt-3 space-y-2.5">
                <div class="grid grid-cols-5 gap-1.5 text-center">
                    @foreach($allTiers as $t)
                        @php
                            $isAchieved = $tier['current_spent'] >= $t['min'];
                            $isCurrent = $tier['key'] === $t['key'];
                        @endphp
                        <div class="p-1.5 rounded-xl transition-all {{ $isCurrent ? 'bg-amber-500/20 border border-amber-400 text-white font-bold shadow-[0_0_10px_rgba(245,158,11,0.2)]' : ($isAchieved ? 'bg-stone-800/80 text-stone-200 border border-stone-700/60' : 'bg-stone-900/40 text-stone-500 border border-stone-800/40 opacity-50') }}">
                            <div class="flex justify-center mb-1">
                                @include('partials.tier-icon', ['tier' => $t['key'], 'class' => 'w-5 h-5'])
                            </div>
                            <div class="text-[9.5px] truncate {{ $isCurrent ? 'text-amber-400 font-extrabold' : 'text-stone-300' }}">{{ $t['name'] }}</div>
                            <div class="text-[8.5px] font-mono {{ $isCurrent ? 'text-white' : 'text-stone-400' }}">{{ $t['threshold'] }}</div>
                        </div>
                    @endforeach
                </div>

                @if(!empty($tier['perks']))
                    <div class="pt-2 flex items-center gap-1.5 text-[10.5px] flex-wrap">
                        <span class="font-bold text-amber-400">المزايا الحالية:</span>
                        @foreach($tier['perks'] as $perk)
                            <span class="inline-flex items-center gap-1 text-stone-200 bg-stone-800/90 px-2 py-0.5 rounded-md border border-stone-700/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shadow-[0_0_4px_rgba(245,158,11,0.8)]"></span>
                                <span>{{ $perk }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </details>
    </div>

    {{-- 4. العضوية النشطة أو ترقية VIP (Active VIP Membership) --}}
    @if($subscription)
        <div class="bg-gradient-to-r from-stone-900 to-stone-800 text-white rounded-2xl p-3.5 shadow-[0_4px_16px_rgba(0,0,0,0.08)] flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-400/20 text-amber-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px] fill-1">workspace_premium</span>
                </div>
                <div>
                    <span class="text-[10px] text-amber-300 font-bold block">عضوية VIP نشطة</span>
                    <strong class="text-xs font-bold text-white">{{ $subscription->membership->name }} (خصم {{ $subscription->membership->discount_percent }}%)</strong>
                    <span class="text-[9.5px] text-stone-400 block mt-0.5 font-mono">تنتهي في {{ $subscription->ends_at?->format('Y/m/d') }}</span>
                </div>
            </div>
            <a href="{{ route('memberships.index') }}" class="px-3 py-1.5 rounded-xl bg-amber-400 text-stone-900 font-bold text-xs shadow-xs shrink-0">
                إدارة
            </a>
        </div>
    @endif

    {{-- 5. قائمة خدمات الحساب (Grouped Settings List) --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.06)] border-0 overflow-hidden divide-y divide-stone-100">
        {{-- طلباتي --}}
        <a href="{{ route('account.orders') }}" class="flex items-center justify-between p-3.5 hover:bg-stone-50 active:bg-stone-100 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <div class="account-menu-icon w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px]">receipt_long</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-[13px] font-bold text-stone-900 leading-tight">طلباتي</h3>
                    <p class="text-[10.5px] text-stone-400 truncate">متابعة الطلبات وتفاصيل الفواتير</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[11px] font-bold text-stone-600 bg-stone-100 px-2 py-0.5 rounded-full">{{ $ordersCount ?? $user->orders()->count() }} طلب</span>
                <span class="material-symbols-outlined text-[18px] text-stone-300">chevron_left</span>
            </div>
        </a>

        {{-- عناويني المحفوظة --}}
        <a href="{{ route('account.addresses') }}" class="flex items-center justify-between p-3.5 hover:bg-stone-50 active:bg-stone-100 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <div class="account-menu-icon w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px]">location_on</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-[13px] font-bold text-stone-900 leading-tight">عناويني المحفوظة</h3>
                    <p class="text-[10.5px] text-stone-400 truncate">إدارة مواقع ومناطق التوصيل</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[11px] font-bold text-stone-600 bg-stone-100 px-2 py-0.5 rounded-full">{{ $addressesCount ?? $user->addresses()->count() }}</span>
                <span class="material-symbols-outlined text-[18px] text-stone-300">chevron_left</span>
            </div>
        </a>

        {{-- عضويات VIP --}}
        <a href="{{ route('memberships.index') }}" class="flex items-center justify-between p-3.5 hover:bg-stone-50 active:bg-stone-100 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <div class="account-menu-icon w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px]">workspace_premium</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-[13px] font-bold text-stone-900 leading-tight">عضويات VIP الرقمية</h3>
                    <p class="text-[10.5px] text-stone-400 truncate">خصومات حصرية وتوصيل مجاني</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($subscription)
                    <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full">نشطة</span>
                @else
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">خصم 15%</span>
                @endif
                <span class="material-symbols-outlined text-[18px] text-stone-300">chevron_left</span>
            </div>
        </a>

        {{-- الإشعارات --}}
        <a href="{{ $user->notificationsInboxRoute() }}" class="flex items-center justify-between p-3.5 hover:bg-stone-50 active:bg-stone-100 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <div class="account-menu-icon w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px]">notifications</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-[13px] font-bold text-stone-900 leading-tight">الإشعارات والتنبيهات</h3>
                    <p class="text-[10.5px] text-stone-400 truncate">تحديثات الطلبات والعروض</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if(($unreadNotifications ?? $user->unreadNotificationsCount()) > 0)
                    <span class="text-[10px] font-bold text-white bg-primary px-2 py-0.5 rounded-full">{{ $unreadNotifications ?? $user->unreadNotificationsCount() }} جديد</span>
                @endif
                <span class="material-symbols-outlined text-[18px] text-stone-300">chevron_left</span>
            </div>
        </a>

        {{-- الدعم الفني عبر واتساب --}}
        <a href="https://wa.me/972590000000" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3.5 hover:bg-stone-50 active:bg-stone-100 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <div class="account-menu-icon w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm.01 18.09c-1.49 0-2.95-.4-4.23-1.16l-.3-.18-3.12.82.83-3.04-.2-.31a8.136 8.136 0 0 1-1.26-4.31c0-4.5 3.66-8.16 8.16-8.16 2.18 0 4.23.85 5.77 2.39 1.54 1.54 2.39 3.59 2.39 5.77 0 4.5-3.66 8.17-8.17 8.17zm4.47-6.1c-.25-.13-1.47-.72-1.7-.81-.23-.09-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.53.07-.25-.13-1.04-.38-1.98-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.13-.14.17-.23.25-.39.09-.16.04-.3-.02-.43-.07-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.16 0-.43.06-.66.3-.23.25-.87.85-.87 2.08 0 1.22.89 2.41 1.02 2.57.13.17 1.76 2.68 4.26 3.76.6.26 1.06.41 1.42.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.14-1.18-.06-.11-.23-.17-.48-.29z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-[13px] font-bold text-stone-900 leading-tight">المساعدة والدعم الفني</h3>
                    <p class="text-[10.5px] text-stone-400 truncate">تواصل مباشر عبر واتساب</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="material-symbols-outlined text-[18px] text-stone-300">chevron_left</span>
            </div>
        </a>
    </div>

    {{-- 6. آخر الطلبات (Recent Orders - مختصر لآخر طلبين) --}}
    @if($recentOrders->isNotEmpty())
        <div class="space-y-2">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-xs font-bold text-stone-700">آخر الطلبات</h2>
                <a href="{{ route('account.orders') }}" class="text-[11px] font-bold text-primary flex items-center gap-0.5">
                    <span>عرض كل الطلبات</span>
                    <span class="material-symbols-outlined text-[13px]">arrow_back</span>
                </a>
            </div>

            @foreach($recentOrders->take(2) as $order)
                <a href="{{ route('account.orders.show', $order) }}" class="bg-white rounded-2xl p-3 shadow-[0_3px_12px_rgba(0,0,0,0.05)] border-0 flex items-center justify-between gap-3 hover:bg-stone-50 active:scale-[0.99] transition-all">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-stone-100 flex items-center justify-center text-primary shrink-0">
                            <span class="material-symbols-outlined text-[18px]">restaurant</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-stone-900 truncate">{{ $order->restaurant->name }}</h4>
                            <div class="text-[10px] text-stone-400 mt-0.5 font-mono">
                                <span>#{{ $order->id }}</span> • <span>{{ $order->created_at->format('m/d H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="text-left">
                            <span class="text-xs font-bold font-mono text-stone-900">{{ number_format($order->total, 1) }} ₪</span>
                            <span class="block text-[9.5px] font-bold {{ match($order->status) {
                                'delivered' => 'text-emerald-600',
                                'delivering', 'preparing', 'confirmed' => 'text-primary',
                                'cancelled', 'rejected' => 'text-stone-400',
                                default => 'text-stone-600',
                            } }}">{{ $order->statusLabel() }}</span>
                        </div>
                        <span class="material-symbols-outlined text-[16px] text-stone-300">chevron_left</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- 7. زر تسجيل الخروج (Sign Out) --}}
    <div class="pt-1">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 rounded-2xl bg-white hover:bg-red-50 text-red-600 text-xs font-bold transition-colors shadow-[0_2px_10px_rgba(0,0,0,0.04)] border border-red-100/80 active:scale-[0.98]">
                <span class="material-symbols-outlined text-[17px]">logout</span>
                <span>تسجيل الخروج من الحساب</span>
            </button>
        </form>
    </div>

</div>
