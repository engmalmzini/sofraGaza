@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'المطاعم والكوفيهات')

@section('content')
<div class="admin-toolbar">
    <div class="admin-chips">
        <a class="admin-chip {{ request('status') ? '' : 'is-active' }}" href="{{ route('admin.restaurants.index', request()->except('status')) }}">الكل</a>
        @foreach(\App\Models\Restaurant::VERIFICATION_STATUSES as $key => $label)
            <a class="admin-chip {{ request('status') === $key ? 'is-active' : '' }}" href="{{ route('admin.restaurants.index', array_merge(request()->except('page'), ['status' => $key])) }}">
                {{ $label }}
                @if($key === 'pending' && $pendingCount)
                    ({{ $pendingCount }})
                @endif
            </a>
        @endforeach
        @if($pendingListingCount)
            <a class="admin-chip" href="{{ route('admin.listings.index', ['status' => 'pending']) }}">حوالات بانتظار التأكيد ({{ $pendingListingCount }})</a>
        @endif
    </div>
    <a href="{{ route('admin.restaurants.create') }}" class="admin-btn admin-btn--primary">إضافة مطعم / كافي</a>
</div>
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>صاحب المطعم</th>
                <th>المنيو</th>
                <th>ينتهي</th>
                <th>التحقق</th>
                <th>الظهور</th>
                <th>اللوحة</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($restaurants as $restaurant)
                <tr>
                    <td class="font-bold">
                        <a href="{{ route('admin.restaurants.show', $restaurant) }}">{{ $restaurant->name }}</a>
                        <div class="text-xs text-on-surface-variant">{{ $restaurant->typeLabel() }}</div>
                    </td>
                    <td>{{ $restaurant->owner->name ?? 'مضاف من الإدارة' }}</td>
                    <td>{{ $restaurant->menu_items_count }}</td>
                    <td>
                        @if($restaurant->isPending())
                            —
                        @else
                            {{ $restaurant->expires_at?->format('Y-m-d') }}
                            <div class="text-xs {{ $restaurant->daysRemaining() <= 7 ? 'text-primary' : 'text-on-surface-variant' }}">باقي {{ $restaurant->daysRemaining() }} يوم</div>
                        @endif
                    </td>
                    <td>@include('admin.partials.pill', ['status' => $restaurant->verification_status, 'label' => $restaurant->verificationLabel()])</td>
                    <td>@include('admin.partials.pill', ['status' => $restaurant->isVisible() ? 'approved' : 'cancelled', 'label' => $restaurant->isVisible() ? 'ظاهر' : 'غير منشور'])</td>
                    <td>@include('admin.partials.pill', ['status' => $restaurant->panel_suspended ? 'rejected' : 'approved', 'label' => $restaurant->panel_suspended ? 'موقوفة' : 'مفتوحة'])</td>
                    <td class="space-x-2 space-x-reverse whitespace-nowrap">
                        <a class="font-bold text-primary" href="{{ route('admin.restaurants.show', $restaurant) }}">مراجعة</a>
                        <a href="{{ route('admin.restaurants.menu-items.index', $restaurant) }}">المنيو</a>
                        <a href="{{ route('admin.restaurants.edit', $restaurant) }}">تعديل</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">لا توجد مطاعم مطابقة.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $restaurants->links() }}</div>
@endsection
