@extends('layouts.partner')

@section('title', 'نظرة عامة')

@section('content')
@if($restaurant->isPending())
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
        {{ $restaurant->venueNounYours() }} موثّق وظاهر للزبائن{{ $restaurant->is_active ? ' في الصفحة الرئيسية' : '، لكنه متوقف مؤقتاً عن استقبال الطلبات' }}.
    </div>
@endif

<div class="admin-metrics">
    <article class="admin-metric admin-metric--accent">
        <span>حالة {{ $restaurant->venueNoun() }}</span>
        <strong class="!text-xl">{{ $restaurant->verificationLabel() }}</strong>
    </article>
    <article class="admin-metric">
        <span>أصناف المنيو</span>
        <strong>{{ $restaurant->menu_items_count }}</strong>
    </article>
    <article class="admin-metric">
        <span>طلبات بانتظار التأكيد</span>
        <strong>{{ $pendingOrders }}</strong>
    </article>
    <article class="admin-metric">
        <span>طلبات اليوم</span>
        <strong>{{ $todayOrders }}</strong>
    </article>
</div>

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
        @endif
    </section>
</div>

<section class="admin-card">
    <div class="admin-toolbar">
        <h2>آخر الطلبات</h2>
        <a class="admin-btn admin-btn--ghost" href="{{ route('partner.orders.index') }}">عرض الكل</a>
    </div>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الزبون</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestOrders as $order)
                    <tr class="admin-click-row" data-href="{{ route('partner.orders.show', $order) }}" role="link" tabindex="0">
                        <td><a class="font-bold text-primary" href="{{ route('partner.orders.show', $order) }}">{{ $order->id }}</a></td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ number_format($order->total, 2) }} <span class="ils">₪</span></td>
                        <td>
                            @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
                            <span class="material-symbols-outlined admin-click-row__open" aria-hidden="true">chevron_left</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">لا توجد طلبات بعد. ستظهر هنا بعد النشر واستقبال الزبائن.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
