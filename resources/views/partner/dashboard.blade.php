@extends('layouts.partner')

@section('title', 'نظرة عامة')

@section('content')
@if($restaurant->panel_suspended)
    <div class="admin-alert admin-alert--err">
        تم إيقاف اللوحة. بياناتك محفوظة ولن يظهر {{ $restaurant->venueNounYours() }} على الموقع حتى تعيد الإدارة تفعيله.
    </div>
@elseif($restaurant->isPending())
    <div class="admin-alert admin-alert--wait">
        حالتك الآن: <strong>جاري التحقق</strong>. يمكنك تجهيز بيانات {{ $restaurant->venueNoun() }} والتصنيفات والمنيو من الآن. لن يظهر {{ $restaurant->venueNounYours() }} في الصفحة الرئيسية حتى توافق الإدارة.
    </div>
@elseif($restaurant->isRejected())
    <div class="admin-alert admin-alert--err">
        لم يُقبل الطلب بعد. السبب: {{ $restaurant->rejection_reason }}
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('partner.restaurant.edit') }}" class="admin-btn admin-btn--ghost">تصحيح البيانات</a>
            <form method="POST" action="{{ route('partner.restaurant.resubmit') }}">
                @csrf
                <button class="admin-btn admin-btn--primary">إعادة الإرسال للمراجعة</button>
            </form>
        </div>
    </div>
@else
    <div class="admin-alert admin-alert--ok">
        {{ $restaurant->venueNounYours() }} موثّق{{ $restaurant->isVisible() ? ' وظاهر للزبائن في الصفحة الرئيسية' : '' }}. الطلبات تُدار من الإدارة فقط.
    </div>
@endif

<div class="admin-metrics">
    <article class="admin-metric admin-metric--accent">
        <span>الطلبات الواردة النشطة</span>
        <strong class="!text-2xl text-primary" id="partner-dash-counter">{{ $activeOrdersCount }}</strong>
    </article>
    <article class="admin-metric">
        <span>حالة {{ $restaurant->venueNoun() }}</span>
        <strong class="!text-xl">{{ $restaurant->verificationLabel() }}</strong>
    </article>
    <article class="admin-metric">
        <span>أصناف المنيو</span>
        <strong>{{ $restaurant->menu_items_count }}</strong>
    </article>
    <article class="admin-metric">
        <span>الظهور للزبائن</span>
        <strong class="!text-xl">{{ $restaurant->isVisible() ? 'ظاهر' : ($restaurant->isApproved() ? 'متوقف' : 'قيد المراجعة') }}</strong>
    </article>
</div>

@if($restaurant->isApproved() && ! $restaurant->panel_suspended)
    <section class="admin-card mb-4 border border-amber-200 bg-amber-50/40">
        <div class="admin-toolbar flex-wrap gap-2">
            <div>
                <h2>إعلان مدفوع — الظهور أولاً</h2>
                <p class="text-sm text-slate-600 mt-1">{{ number_format(\App\Support\Finance::BOOST_DAILY_RATE, 0) }} ₪ لليوم. حدّد الأيام، يُحسب السعر، ثم حوّل وأرفق الإشعار.</p>
            </div>
            <a class="admin-btn admin-btn--primary text-xs" href="{{ route('partner.boosts.index') }}">شراء إعلان</a>
        </div>
    </section>
@endif

<section class="admin-card mb-4" id="partner-live-orders-section" data-last-id="{{ $activeOrders->max('id') ?? 0 }}" data-live-url="{{ route('partner.orders.live') }}">
    <div class="admin-toolbar flex-wrap gap-2">
        <div class="flex items-center gap-2">
            <h2>الطلبات الواردة (تحديث فوري)</h2>
            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold animate-pulse">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                مباشر
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-toggle-sound" class="admin-btn admin-btn--ghost text-xs flex items-center gap-1" title="اختبار نغمة التنبيه">
                <span class="material-symbols-outlined text-primary text-[16px]">volume_up</span>
                <span id="sound-status-label">صوت التنبيه: مفعّل</span>
            </button>
            <a class="admin-btn admin-btn--ghost text-xs" href="{{ route('partner.orders.index') }}">كل الطلبات</a>
        </div>
    </div>
    <p class="mt-1 text-xs text-on-surface-variant">اسحب الطلب وأفلته في قيد التحضير ليبدأ التحضير تلقائياً. الطلبات الجديدة تظهر هنا فوراً مع رنين إشعار.</p>

    <div id="partner-orders-container" class="mt-4">
        @include('partner.orders.partials.order-board', ['ordersByStatus' => $ordersByStatus])
    </div>
</section>

<section class="admin-card partner-cats-card">
    <div class="admin-toolbar">
        <h2>تصنيفات المنيو</h2>
        <a class="admin-btn admin-btn--ghost" href="{{ route('partner.menu-items.index') }}">إدارة المنيو</a>
    </div>
    <p class="mt-1 text-sm text-on-surface-variant">شاهد أصناف كل تصنيف وأضف منها مباشرة، سواء كان مطعماً أو كافياً أو غير ذلك.</p>
    <div class="partner-cats">
        @foreach($restaurant->defaultMenuCategories() as $category)
            @php $count = (int) ($categoryStats[$category] ?? 0); @endphp
            <a class="partner-cat" href="{{ route('partner.menu-items.index', ['category' => $category]) }}">
                <span>{{ $category }}</span>
                <strong>{{ $count }}</strong>
                <small>{{ $count ? 'صنف في المنيو' : 'فارغ — أضف أصنافاً' }}</small>
            </a>
        @endforeach
        @foreach($categoryStats as $category => $count)
            @continue(in_array($category, $restaurant->defaultMenuCategories(), true) || blank($category))
            <a class="partner-cat" href="{{ route('partner.menu-items.index', ['category' => $category]) }}">
                <span>{{ $category }}</span>
                <strong>{{ $count }}</strong>
                <small>صنف في المنيو</small>
            </a>
        @endforeach
    </div>
</section>

<section class="admin-card">
    <div class="admin-toolbar">
        <h2>منيو {{ $restaurant->venueNounYours() }}</h2>
        <a href="{{ route('partner.menu-items.create') }}" class="admin-btn admin-btn--primary">إضافة صنف</a>
    </div>
    @forelse($menuByCategory as $category => $items)
        <div class="partner-menu-group">
            <div class="partner-menu-group__head">
                <h3>{{ $category ?: 'بدون تصنيف' }}</h3>
                <a href="{{ route('partner.menu-items.create', ['category' => $category]) }}">إضافة هنا</a>
            </div>
            <ul class="partner-menu-list">
                @foreach($items->take(4) as $item)
                    <li>
                        <span>{{ $item->name }}</span>
                        <em>{{ number_format($item->price, 2) }} <span class="ils">₪</span></em>
                    </li>
                @endforeach
            </ul>
            @if($items->count() > 4)
                <a class="partner-menu-more" href="{{ route('partner.menu-items.index', ['category' => $category]) }}">عرض كل أصناف {{ $category }}</a>
            @endif
        </div>
    @empty
        <p class="mt-3 text-sm text-on-surface-variant">لا يوجد منيو بعد. ابدأ بتصنيف ثم أضف الأصناف والأسعار.</p>
    @endforelse
</section>

<div class="admin-grid-2">
    <section class="admin-card">
        <h2>جهّز {{ $restaurant->venueNounYours() }} قبل النشر</h2>
        <p class="mt-1 text-sm text-on-surface-variant">{{ $readyCount }} من {{ count($checklist) }} خطوات مكتملة</p>
        @foreach($checklist as $step)
            <div class="admin-row">
                <span>{{ $step['label'] }}</span>
                <span class="admin-pill {{ $step['done'] ? 'admin-pill--ok' : 'admin-pill--wait' }}">{{ $step['done'] ? 'مكتمل' : 'مطلوب' }}</span>
            </div>
        @endforeach
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('partner.restaurant.edit') }}" class="admin-btn admin-btn--ghost">تعديل البيانات</a>
            <a href="{{ route('partner.menu-items.create') }}" class="admin-btn admin-btn--primary">إضافة صنف</a>
        </div>
    </section>
    <section class="admin-card">
        <h2>{{ $restaurant->name }}</h2>
        <div class="mt-3 space-y-1 text-sm leading-7">
            <div>{{ $restaurant->typeLabel() }} · {{ $restaurant->cuisineLabel() }}</div>
            <div>{{ $restaurant->areaLabel() }}</div>
            <div>{{ $restaurant->address }}</div>
            <div>الهاتف: {{ $restaurant->phone }}</div>
            <div>ساعات العمل: {{ $restaurant->hoursLabel() }}</div>
        </div>
        @if($restaurant->isVisible())
            <a class="mt-4 inline-flex admin-btn admin-btn--ghost" href="{{ route('restaurants.show', $restaurant) }}">عرض صفحة {{ $restaurant->venueNoun() }}</a>
        @else
            <p class="mt-4 text-sm text-on-surface-variant">الصفحة العامة تظهر للزبائن بعد موافقة الإدارة.</p>
        @endif
    </section>
</div>
@endsection
