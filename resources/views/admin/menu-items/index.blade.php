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
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td class="font-bold">{{ $item->name }}</td>
                    <td>{{ $item->category }}</td>
                    <td>{{ number_format($item->price, 2) }} <span class="ils">₪</span></td>
                    <td>{{ $item->earn_points !== null ? $item->earn_points.' نقطة' : 'حسب السعر' }}</td>
                    <td>{{ $item->redeem_points !== null ? $item->redeem_points.' نقطة' : 'حسب السعر' }}</td>
                    <td>@include('admin.partials.pill', ['status' => $item->is_available ? 'approved' : 'cancelled', 'label' => $item->is_available ? 'متوفر' : 'غير متوفر'])</td>
                    <td class="whitespace-nowrap space-x-2 space-x-reverse">
                        <a href="{{ route('admin.restaurants.menu-items.edit', [$restaurant, $item]) }}">تعديل</a>
                        <form class="inline" method="POST" action="{{ route('admin.restaurants.menu-items.destroy', [$restaurant, $item]) }}">@csrf @method('DELETE')<button class="text-primary">حذف</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
