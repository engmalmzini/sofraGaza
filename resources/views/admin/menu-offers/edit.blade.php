@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'تعديل عرض — '.$restaurant->name)

@section('content')
<div class="admin-toolbar">
    <a href="{{ route('admin.restaurants.menu-items.index', $restaurant) }}" class="admin-btn admin-btn--ghost">عودة للمنيو</a>
</div>
<form method="POST" action="{{ route('admin.restaurants.menu-offers.update', [$restaurant, $offer]) }}" enctype="multipart/form-data" class="admin-card admin-form space-y-4">
    @csrf
    @method('PUT')
    @include('admin.menu-offers._form')
    @if($items->isNotEmpty())
        <button class="admin-btn admin-btn--primary w-fit">حفظ التعديل</button>
    @endif
</form>
@endsection
