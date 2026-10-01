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
                <th>التحقق</th>
                <th>الظهور</th>
                <th>اللوحة</th>
                <th class="text-center">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($restaurants as $restaurant)
                <tr>
                    <td class="font-bold">
                        <a href="{{ route('admin.restaurants.show', $restaurant) }}" class="text-slate-900 hover:text-primary">{{ $restaurant->name }}</a>
                        <div class="text-xs text-on-surface-variant font-normal">{{ $restaurant->typeLabel() }}</div>
                    </td>
                    <td>{{ $restaurant->owner->name ?? 'مضاف من الإدارة' }}</td>
                    <td><span class="font-bold text-slate-800">{{ $restaurant->menu_items_count }}</span> صنف</td>
                    <td>@include('admin.partials.pill', ['status' => $restaurant->verification_status, 'label' => $restaurant->verificationLabel()])</td>
                    <td>@include('admin.partials.pill', ['status' => $restaurant->isVisible() ? 'approved' : 'cancelled', 'label' => $restaurant->isVisible() ? 'ظاهر' : 'غير منشور'])</td>
                    <td>@include('admin.partials.pill', ['status' => $restaurant->panel_suspended ? 'rejected' : 'approved', 'label' => $restaurant->panel_suspended ? 'موقوفة' : 'مفتوحة'])</td>
                    <td class="whitespace-nowrap">
                        <div class="admin-table-actions">
                            <a class="admin-action-btn admin-action-btn--primary" href="{{ route('admin.restaurants.show', $restaurant) }}">
                                <span class="material-symbols-outlined">visibility</span>
                                <span>مراجعة</span>
                            </a>
                            <a class="admin-action-btn admin-action-btn--dark" href="{{ route('admin.restaurants.menu-items.index', $restaurant) }}">
                                <span class="material-symbols-outlined">restaurant_menu</span>
                                <span>المنيو</span>
                            </a>
                            <a class="admin-action-btn admin-action-btn--outline" href="{{ route('admin.restaurants.edit', $restaurant) }}">
                                <span class="material-symbols-outlined">edit</span>
                                <span>تعديل</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">لا توجد مطاعم مطابقة.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $restaurants->links() }}</div>
@endsection
