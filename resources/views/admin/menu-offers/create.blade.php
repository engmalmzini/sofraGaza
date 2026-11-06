@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'عرض جديد — '.$restaurant->name)

@section('content')
<div class="admin-toolbar">
    <a href="{{ route('admin.restaurants.menu-items.index', $restaurant) }}" class="admin-btn admin-btn--ghost">عودة للمنيو</a>
</div>
<form method="POST" action="{{ route('admin.restaurants.menu-offers.store', $restaurant) }}" enctype="multipart/form-data" class="admin-card admin-form space-y-4">
    @csrf
    @include('admin.menu-offers._form')
    @if($items->isNotEmpty())
        <button class="admin-btn admin-btn--primary w-fit">حفظ العرض</button>
    @endif
</form>
@endsection
