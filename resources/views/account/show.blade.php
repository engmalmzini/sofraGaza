@extends('layouts.public')

@section('title', 'حسابي')

@section('content')
<div class="lg:hidden">
    @include('account.partials.show-mobile')
</div>

<div class="hidden lg:block mx-auto max-w-6xl px-margin-desktop py-8 space-y-7">

    @php
        $tier = $tier ?? $user->tier();
        $allTiers = [
            [
                'key' => 'starter',
                'name' => 'مبتدئ',
                'min' => 0,
                'max' => 150,
                'threshold' => '0 ₪',
            ],
            [
                'key' => 'bronze',
                'name' => 'برونزي',
                'min' => 150,
                'max' => 300,
                'threshold' => '150 ₪',
            ],
            [
                'key' => 'silver',
                'name' => 'فضي',
                'min' => 300,
                'max' => 600,
                'threshold' => '300 ₪',
            ],
            [
                'key' => 'gold',
                'name' => 'ذهبي',
                'min' => 600,
                'max' => 1000,
                'threshold' => '600 ₪',
            ],
            [
                'key' => 'platinum',
                'name' => 'بلاتيني (VIP)',
                'min' => 1000,
                'max' => null,
                'threshold' => '1000 ₪',
            ],
        ];
    @endphp

    {{-- 1. Master Consolidated Profile & Loyalty Dashboard Card --}}
    <div class="rounded-3xl bg-white border border-stone-200 p-5 sm:p-7 shadow-xs space-y-6">
        
        {{-- Top Section: User Info, Balance + Recharge & Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 pb-5 border-b border-stone-100">
            <div class="flex items-center gap-4">
                {{-- Avatar with Verified Tier Badge --}}
                <div class="relative w-16 h-16 sm:w-18 sm:h-18 rounded-2xl bg-stone-900 text-white font-black text-2xl flex items-center justify-center shadow-xs shrink-0">
                    @if($user->photo_path)
                        <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}" class="w-full h-full rounded-2xl object-cover">
                    @else
                        <span>{{ mb_substr($user->name, 0, 1) }}</span>
                    @endif
                    <span class="absolute -bottom-1 -left-1 w-6 h-6 rounded-full bg-white ring-2 ring-white shadow-xs flex items-center justify-center z-10" title="مستوى الولاء: {{ $tier['name'] }}">
                        @include('partials.tier-icon', ['tier' => $tier['key'], 'class' => 'w-full h-full'])
                    </span>
                </div>

                {{-- User Info + Balance + Charge --}}
                <div>
                    <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-stone-900 font-headline-md tracking-tight">{{ $user->name }}</h1>
                        
                        {{-- Balance & Topup next to name --}}
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-stone-50 border border-stone-200">
                            <a href="{{ route('account.wallet') }}" class="flex items-baseline gap-1 hover:text-primary transition-colors" title="المحفظة وسجل الحركات">
                                <span class="text-xs text-stone-500 font-medium">الرصيد:</span>
                                <strong class="font-mono font-black text-sm text-stone-900">{{ number_format($user->wallet_balance ?? 0, 2) }}</strong>
                                <span class="ils text-xs font-bold text-stone-600">₪</span>
                            </a>
                            <a href="{{ route('account.wallet.topup') }}" class="px-2 py-0.5 rounded-lg bg-primary hover:bg-stone-900 text-white text-[11px] font-bold transition-colors shadow-2xs" title="شحن المحفظة">
                                + شحن
                            </a>
                        </div>

                        @if($subscription)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary/10 border border-primary/20 text-primary text-xs font-bold">
                                <span class="material-symbols-outlined text-[14px] fill-1 text-primary">workspace_premium</span>
                                <span>{{ $subscription->membership->name }}</span>
                            </span>
                        @endif
                    </div>

                    <div class="mt-1.5 flex items-center gap-3 sm:gap-4 text-xs text-stone-500 flex-wrap font-medium">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-stone-400">call</span>
                            <span dir="ltr" class="font-mono">{{ $user->phone }}</span>
                        </span>
                        @if($user->email)
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-stone-400">mail</span>
                                <span>{{ $user->email }}</span>
                            </span>
                        @endif
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-stone-400">calendar_today</span>
                            <span>عضو منذ {{ $user->created_at->format('Y/m/d') }}</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Profile Actions --}}
            <div class="flex items-center gap-2.5 shrink-0 self-start sm:self-center">
                @if($user->isRestaurantOwner())
                    <a href="{{ auth()->user()->partnerPanelRoute() }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary text-white hover:bg-stone-900 transition-colors text-xs font-bold shadow-xs">
                        <span class="material-symbols-outlined text-[16px]">storefront</span>
                        <span>لوحة المطعم</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-stone-200 bg-white text-stone-700 hover:text-primary hover:border-primary/40 hover:bg-stone-50 transition-colors text-xs font-bold">
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        <span>تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Lower Section: 2 Columns Grid (بطاقة المستويات عامودية + البطاقات أفقية مرتبة عمودياً فوق بعضها) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            {{-- Right Column (lg:col-span-7): بطاقة المستويات بشكل عامودي --}}
            <div class="lg:col-span-7 rounded-2xl bg-stone-50/70 border border-stone-200/90 p-4 sm:p-5 space-y-4">
                {{-- Header: Tier & Spending --}}
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2.5">
                        @include('partials.tier-icon', ['tier' => $tier['key'], 'class' => 'w-8 h-8'])
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-stone-500">المستوى:</span>
                            <strong class="text-sm font-bold text-stone-900">{{ $tier['name'] }}</strong>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 text-xs">
                        <div>
                            <span class="text-stone-500">المشتريات:</span>
                            <strong class="font-mono font-bold text-stone-900">{{ number_format($tier['current_spent'], 1) }} ₪</strong>
                        </div>
                        @if($tier['next_tier'])
                            <span class="text-stone-300">|</span>
                            <div>
                                <span class="text-stone-500">المتبقي:</span>
                                <strong class="font-mono font-bold text-primary">{{ number_format($tier['remaining'], 1) }} ₪</strong>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Progress Bar --}}
                @if($tier['next_tier'])
                    <div class="space-y-1">
                        <div class="w-full bg-stone-200/80 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: {{ max(4, $tier['progress_percent']) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-stone-500">
                            <span>القادم: <strong class="text-stone-900">{{ $tier['next_tier'] }}</strong> ({{ $tier['next_min'] }} ₪)</span>
                            <span class="font-mono font-bold text-primary">{{ $tier['progress_percent'] }}%</span>
                        </div>
                    </div>
                @endif

                {{-- Stages Stepper (خريطة مستويات الزبائن في سفرة غزة:) --}}
                <div class="pt-3 border-t border-stone-200/70">
                    <div class="text-xs font-bold text-stone-900 mb-2">خريطة مستويات الزبائن في سفرة غزة:</div>
                    
                    <div class="grid grid-cols-5 gap-1.5 text-center">
                        @foreach($allTiers as $t)
                            @php
                                $isAchieved = $tier['current_spent'] >= $t['min'];
                                $isCurrent = $tier['key'] === $t['key'];
                            @endphp
                            <div class="p-1.5 sm:p-2 rounded-xl transition-all {{ $isCurrent ? 'bg-primary/10 border border-primary text-primary' : ($isAchieved ? 'bg-white border border-stone-200 text-stone-900 shadow-2xs' : 'bg-transparent text-stone-400') }}">
                                <div class="flex justify-center mb-1">
                                    @include('partials.tier-icon', ['tier' => $t['key'], 'class' => 'w-6 h-6'])
                                </div>
                                <div class="text-[11px] font-bold truncate">{{ $t['name'] }}</div>
                                <div class="text-[9.5px] font-mono opacity-80 mt-0.5">{{ $t['threshold'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Perks (Concise) --}}
                @if(!empty($tier['perks']))
                    <div class="pt-2 border-t border-stone-200/70 flex items-center gap-1.5 text-xs flex-wrap">
                        <span class="font-bold text-stone-900">المزايا:</span>
                        @foreach($tier['perks'] as $perk)
                            <span class="inline-flex items-center gap-1 text-stone-700 bg-white px-2 py-0.5 rounded-md border border-stone-200 text-[11px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                <span>{{ $perk }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Left Column (lg:col-span-5): البطاقات أفقية مرتبة عمودياً فوق بعضها --}}
            <div class="lg:col-span-5 flex flex-col gap-3">
                {{-- 1. Points Horizontal Card --}}
                <div class="flex items-center justify-between p-3.5 sm:p-4 rounded-2xl bg-stone-50 border border-stone-200/80 hover:border-stone-300 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center shrink-0 shadow-2xs">
                            @include('partials.gold-coin-icon', ['class' => 'w-7 h-7'])
                        </span>
                        <div>
                            <span class="block text-[11px] font-bold text-stone-500">نقاط المكافآت</span>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <strong class="text-base sm:text-lg font-black font-mono text-stone-900">{{ number_format($user->points_balance ?? 0) }}</strong>
                                <span class="text-xs font-bold text-stone-500">نقطة</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('redeem.create') }}" class="px-2.5 py-1.5 rounded-lg bg-stone-900 hover:bg-black text-white text-xs font-bold shadow-2xs transition-colors" title="استبدال النقاط">
                            استبدال
                        </a>
                        <a href="{{ route('account.points') }}" class="p-1.5 rounded-lg text-stone-400 hover:text-stone-800 hover:bg-white transition-colors" title="سجل النقاط">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                        </a>
                    </div>
                </div>

                {{-- 2. VIP Membership Horizontal Card --}}
                <div class="flex items-center justify-between p-3.5 sm:p-4 rounded-2xl bg-stone-50 border border-stone-200/80 hover:border-stone-300 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px] fill-1 text-primary">workspace_premium</span>
                        </span>
                        <div>
                            @if($subscription)
                                <span class="block text-[11px] font-bold text-stone-500">عضوية نشطة</span>
                                <div class="flex items-baseline gap-1 mt-0.5">
                                    <strong class="text-sm font-black text-stone-900">{{ $subscription->membership->name }}</strong>
                                    <span class="text-[11px] text-primary font-bold">(-{{ $subscription->membership->discount_percent }}%)</span>
                                </div>
                            @else
                                <span class="block text-[11px] font-bold text-stone-500">عضويات VIP الرقمية</span>
                                <div class="flex items-baseline gap-1 mt-0.5">
                                    <strong class="text-xs sm:text-sm font-bold text-stone-900">خصم 15% وتوصيل</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('memberships.index') }}" class="px-2.5 py-1.5 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-stone-800 text-xs font-bold shadow-2xs transition-colors flex items-center gap-0.5">
                            <span>{{ $subscription ? 'إدارة' : 'اشترك' }}</span>
                            <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                        </a>
                    </div>
                </div>

                {{-- Digital Card View if Subscribed --}}
                @if($subscription)
                    <div class="pt-3 border-t border-stone-200/70">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-primary text-[18px]">credit_card</span>
                                <h3 class="text-xs font-bold text-stone-900">بطاقة عضوية رقمية</h3>
                            </div>
                            <span class="text-[11px] text-stone-500 font-mono">صالحة حتى {{ $subscription->ends_at?->format('Y/m/d') }}</span>
                        </div>
                        <div>
                            @include('partials.membership-card', ['subscription' => $subscription, 'holder' => $user])
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>

    {{-- Expiring Soon Notice if active --}}
    @if($subscription?->isExpiringSoon())
        <div class="rounded-2xl bg-stone-100 border border-stone-300 p-4 text-xs font-medium text-stone-900 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">warning</span>
                <span>عضويتك تنتهي خلال {{ $subscription->daysRemaining() }} أيام. جدّد الاشتراك الآن لتستمر بالاستفادة من الخصم والنقاط الإضافية.</span>
            </div>
            <a href="{{ route('memberships.index') }}" class="px-3.5 py-1.5 rounded-lg bg-primary text-white font-bold text-xs hover:bg-primary-container shrink-0">تجديد العضوية</a>
        </div>
    @endif

    {{-- 3. Quick Navigation & Services Grid --}}
    <div>
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-base font-bold text-stone-900">الخدمات والوصول السريع</h2>
            <span class="text-xs text-stone-400">إدارة حسابك ونشاطك</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
            {{-- My Orders --}}
            <a href="{{ route('account.orders') }}" class="group rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs hover:border-primary/40 hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-900 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                    </div>
                    <span class="text-[11px] font-bold text-stone-600 bg-stone-100 px-2 py-0.5 rounded-full">{{ $ordersCount ?? $user->orders()->count() }} طلب</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900 group-hover:text-primary transition-colors">طلباتي</h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">متابعة وتفاصيل الفواتير</p>
                </div>
            </a>

            {{-- Saved Addresses --}}
            <a href="{{ route('account.addresses') }}" class="group rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs hover:border-primary/40 hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-900 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-[20px]">location_on</span>
                    </div>
                    <span class="text-[11px] font-bold text-stone-600 bg-stone-100 px-2 py-0.5 rounded-full">{{ $addressesCount ?? $user->addresses()->count() }} عنوان</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900 group-hover:text-primary transition-colors">عناويني المحفوظة</h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">مناطق ومواقع التوصيل</p>
                </div>
            </a>

            {{-- Wallet Top-up --}}
            <a href="{{ route('account.wallet.topup') }}" class="group rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs hover:border-primary/40 hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-900 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-[20px]">add_card</span>
                    </div>
                    <span class="text-[11px] font-bold text-stone-600 bg-stone-100 px-2 py-0.5 rounded-full">إيداع رصيد</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900 group-hover:text-primary transition-colors">شحن المحفظة</h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">طرق دفع محلية مباشرة</p>
                </div>
            </a>

            {{-- Notifications --}}
            <a href="{{ $user->notificationsInboxRoute() }}" class="group rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs hover:border-primary/40 hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-900 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                    </div>
                    @if(($unreadNotifications ?? $user->unreadNotificationsCount()) > 0)
                        <span class="text-[11px] font-bold text-white bg-primary px-2 py-0.5 rounded-full">{{ $unreadNotifications ?? $user->unreadNotificationsCount() }} جديد</span>
                    @else
                        <span class="text-[11px] font-medium text-stone-400">لا جديد</span>
                    @endif
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900 group-hover:text-primary transition-colors">الإشعارات</h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">تحديثات الطلبات والحساب</p>
                </div>
            </a>
        </div>
    </div>

    {{-- 4. Recent Orders Section --}}
    <div>
        <div class="flex items-center justify-between mb-3.5">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">history</span>
                <h2 class="text-base font-bold text-stone-900">آخر الطلبات</h2>
            </div>
            @if(($ordersCount ?? $user->orders()->count()) > 0)
                <a href="{{ route('account.orders') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span>عرض كل الطلبات</span>
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                </a>
            @endif
        </div>

        @if($recentOrders->isNotEmpty())
            <div class="space-y-2.5">
                @foreach($recentOrders as $order)
                    <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-stone-300 transition-colors">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-stone-100 flex items-center justify-center text-primary shrink-0">
                                <span class="material-symbols-outlined text-[22px]">restaurant</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-stone-900">{{ $order->restaurant->name }}</span>
                                    <span class="text-xs font-mono text-stone-400">#{{ $order->id }}</span>
                                    @if($order->isPaidWithWallet())
                                        <span class="text-[10px] font-semibold bg-stone-100 text-stone-800 border border-stone-200 px-2 py-0.5 rounded-full">مدفوع بالمحفظة</span>
                                    @endif
                                </div>
                                <div class="text-xs text-stone-500 mt-0.5">
                                    <span>{{ $order->created_at->format('Y/m/d H:i') }}</span>
                                    @if($order->delivery_area)
                                        <span>• {{ $order->deliveryAreaLabel() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-4 pt-3 sm:pt-0 border-t sm:border-0 border-slate-100">
                            <div class="text-left sm:text-right">
                                <span class="text-sm font-black text-stone-900 font-mono">{{ number_format($order->total, 1) }}</span>
                                <span class="ils text-xs">₪</span>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full {{ match($order->status) {
                                'delivered' => 'bg-stone-100 text-stone-900 border border-stone-300',
                                'delivering', 'preparing', 'confirmed' => 'bg-primary/10 text-primary border border-primary/20',
                                'cancelled', 'rejected' => 'bg-stone-100 text-stone-400 border border-stone-200',
                                default => 'bg-stone-100 text-stone-600',
                            } }}">
                                {{ $order->statusLabel() }}
                            </span>
                            <a href="{{ route('account.orders.show', $order) }}" class="p-1.5 rounded-lg text-stone-400 hover:text-primary hover:bg-stone-50 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl bg-white border border-slate-200/80 p-8 sm:p-12 text-center shadow-xs space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-stone-100 text-primary flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined text-[28px]">dinner_dining</span>
                </div>
                <h3 class="text-base font-bold text-stone-900">سلتك تنتظر وجبتك الأولى!</h3>
                <p class="text-xs text-stone-500 max-w-md mx-auto leading-relaxed">
                    استكشف أشهى المأكولات والمطاعم المتوفرة في منطقتك بقطاع غزة، واطلب وجبتك المفضلة لتصلك ساخنة وطازجة.
                </p>
                <div class="pt-2">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white font-bold text-xs hover:bg-primary-container shadow-xs transition-colors">
                        <span>تصفح المطاعم واطلب الآن</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
