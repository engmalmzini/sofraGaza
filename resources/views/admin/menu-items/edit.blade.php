@extends('layouts.admin')

@section('title', 'تعديل صنف')

@section('content')
@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'تعديل صنف')

@section('content')
<form method="POST" action="{{ route('admin.restaurants.menu-items.update', [$restaurant, $menuItem]) }}" enctype="multipart/form-data" class="admin-card admin-form">
    @csrf
    @method('PUT')
    @include('admin.menu-items._form')
    <button class="admin-btn admin-btn--primary w-fit">تحديث</button>
</form>
@endsection
@endsection
