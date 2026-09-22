@extends('layouts.partner')

@section('title', 'المنيو والتصنيفات')

@section('actions')
<a href="{{ route('partner.menu-items.create', request()->only('category')) }}" class="admin-btn admin-btn--primary">إضافة صنف</a>
@endsection

@section('content')
<p class="mb-4 text-sm text-on-surface-variant">رتّب منيو {{ $restaurant->venueNounYours() }} حسب التصنيف: وجبات، مشروبات، أو أصناف الكافي. حتى لو كان الحساب قيد التحقق، سيكون المنيو جاهزاً لحظة الموافقة.</p>

<div class="admin-chips mb-4">
    <a class="admin-chip {{ request('category') ? '' : 'is-active' }}" href="{{ route('partner.menu-items.index', request()->except('category')) }}">الكل</a>
    @foreach($categories as $category)
        <a class="admin-chip {{ request('category') === $category ? 'is-active' : '' }}" href="{{ route('partner.menu-items.index', array_merge(request()->except('page'), ['category' => $category])) }}">{{ $category }}</a>
    @endforeach
</div>

@forelse($grouped as $category => $groupItems)
    <section class="admin-card partner-menu-group-card">
        <div class="partner-menu-group__head">
            <h2>{{ $category ?: 'بدون تصنيف' }} <small>{{ $groupItems->count() }}</small></h2>
            <a class="admin-btn admin-btn--ghost" href="{{ route('partner.menu-items.create', ['category' => $category]) }}">إضافة لصنف {{ $category }}</a>
        </div>
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>الصنف</th>
                        <th>السعر</th>
                        <th>التوفر</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupItems as $item)
                        <tr>
                            <td class="font-bold">{{ $item->name }}</td>
                            <td>{{ number_format($item->price, 2) }} <span class="ils">₪</span></td>
                            <td>@include('admin.partials.pill', ['status' => $item->is_available ? 'approved' : 'cancelled', 'label' => $item->is_available ? 'متوفر' : 'غير متوفر'])</td>
                            <td class="whitespace-nowrap space-x-2 space-x-reverse">
                                <a href="{{ route('partner.menu-items.edit', $item) }}">تعديل</a>
                                <form class="inline" method="POST" action="{{ route('partner.menu-items.destroy', $item) }}">@csrf @method('DELETE')<button class="text-primary">حذف</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@empty
    <div class="admin-card">
        <p>لا توجد أصناف في هذا التصنيف بعد. ابدأ بإضافة أول صنف.</p>
        <a href="{{ route('partner.menu-items.create', request()->only('category')) }}" class="admin-btn admin-btn--primary mt-3 inline-flex">إضافة صنف</a>
    </div>
@endforelse
@endsection
