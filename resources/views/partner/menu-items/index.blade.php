@extends('layouts.partner')

@section('title', 'المنيو والأطباق')

@section('actions')
<a href="{{ route('partner.menu-items.create') }}" class="admin-btn admin-btn--primary">إضافة طبق</a>
@endsection

@section('content')
<p class="mb-4 text-sm text-on-surface-variant">أضف التصنيفات والأطباق والأسعار الآن. حتى لو كان مطعمك قيد التحقق، سيكون المنيو جاهزاً لحظة الموافقة.</p>
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>الصنف</th>
                <th>التصنيف</th>
                <th>السعر</th>
                <th>التوفر</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td class="font-bold">{{ $item->name }}</td>
                    <td>{{ $item->category }}</td>
                    <td>{{ number_format($item->price, 2) }} <span class="ils">₪</span></td>
                    <td>@include('admin.partials.pill', ['status' => $item->is_available ? 'approved' : 'cancelled', 'label' => $item->is_available ? 'متوفر' : 'غير متوفر'])</td>
                    <td class="whitespace-nowrap space-x-2 space-x-reverse">
                        <a href="{{ route('partner.menu-items.edit', $item) }}">تعديل</a>
                        <form class="inline" method="POST" action="{{ route('partner.menu-items.destroy', $item) }}">@csrf @method('DELETE')<button class="text-primary">حذف</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">لا توجد أصناف بعد. ابدأ بإضافة أول طبق.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
