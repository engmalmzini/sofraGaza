@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'منيو '.$restaurant->name)

@section('content')
<div class="admin-toolbar">
    <a href="{{ route('admin.restaurants.index') }}" class="admin-btn admin-btn--ghost">عودة للمطاعم</a>
    <a href="{{ route('admin.restaurants.menu-items.create', $restaurant) }}" class="admin-btn admin-btn--primary">إضافة صنف</a>
</div>
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>الصنف</th>
                <th>التصنيف</th>
                <th>السعر</th>
                <th>اكتساب</th>
                <th>استبدال</th>
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
                    <td>@include('admin.partials.pill', ['status' => $item->is_available ? 'approved' : 'cancelled', 'label' => $item->is_available ? 'متوفر' : 'غير متوفر'])</td>
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
