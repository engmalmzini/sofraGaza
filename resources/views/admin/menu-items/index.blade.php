@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'منيو '.$restaurant->name)

@section('content')
<div class="admin-toolbar">
    <a href="{{ route('admin.restaurants.index') }}" class="admin-btn admin-btn--ghost">عودة للمطاعم</a>
    <a href="{{ route('admin.restaurants.menu-items.create', $restaurant) }}" class="admin-btn admin-btn--primary">إضافة صنف</a>
</div>
@php
    $usedCategories = $restaurant->menuItems()
        ->whereNotNull('category')
        ->where('category', '!=', '')
        ->distinct()
        ->pluck('category');
@endphp
@if($usedCategories->isNotEmpty())
    <div class="admin-chips mb-4">
        @foreach($usedCategories as $category)
            <span class="admin-chip is-active inline-flex items-center gap-2">
                {{ $category }}
                @include('admin.partials.delete-form', [
                    'action' => route('admin.restaurants.menu-categories.destroy', $restaurant),
                    'confirm' => "حذف تصنيف {$category} وكل أصنافه؟ الطلبات القديمة لن تتأثر.",
                    'label' => 'حذف',
                    'hidden' => ['category' => $category],
                    'buttonClass' => 'text-xs font-bold text-rose-600 bg-transparent border-0 p-0',
                    'icon' => false,
                ])
            </span>
        @endforeach
    </div>
@endif
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>الصنف</th>
                <th>التصنيف</th>
                <th>السعر</th>
                <th>اكتساب</th>
                <th>استبدال</th>
                <th>الكمية</th>
                <th>التوفر</th>
                <th class="text-center">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td class="font-bold text-slate-900">{{ $item->name }}</td>
                    <td><span class="font-semibold text-slate-700">{{ $item->category }}</span></td>
                    <td class="font-mono font-bold text-slate-900">{{ number_format($item->price, 2) }} <span class="ils">₪</span></td>
                    <td class="text-xs text-slate-600">{{ $item->earn_points !== null ? $item->earn_points.' نقطة' : 'حسب السعر' }}</td>
                    <td class="text-xs text-slate-600">{{ $item->redeem_points !== null ? $item->redeem_points.' نقطة' : 'حسب السعر' }}</td>
                    <td>
                        @include('admin.partials.pill', [
                            'status' => $item->isUnlimitedStock() ? 'approved' : ($item->remainingStock() > 0 ? 'pending' : 'cancelled'),
                            'label' => $item->stockLabel(),
                        ])
                    </td>
                    <td>@include('admin.partials.pill', ['status' => $item->isOrderable() ? 'approved' : 'cancelled', 'label' => $item->isOrderable() ? 'متوفر' : 'غير متوفر'])</td>
                    <td class="whitespace-nowrap">
                        <div class="admin-table-actions">
                            <a class="admin-action-btn admin-action-btn--outline admin-action-btn--sm" href="{{ route('admin.restaurants.menu-items.edit', [$restaurant, $item]) }}">
                                <span class="material-symbols-outlined">edit</span>
                                <span>تعديل</span>
                            </a>
                            <form class="inline" method="POST" action="{{ route('admin.restaurants.menu-items.destroy', [$restaurant, $item]) }}" onsubmit="return confirm('حذف هذا الصنف؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-action-btn admin-action-btn--danger admin-action-btn--sm">
                                    <span class="material-symbols-outlined">delete</span>
                                    <span>حذف</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
