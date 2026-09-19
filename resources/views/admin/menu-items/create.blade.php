@extends('layouts.admin')

@section('title', 'إضافة صنف')

@section('content')
@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'إضافة صنف')

@section('content')
<form method="POST" action="{{ route('admin.restaurants.menu-items.store', $restaurant) }}" enctype="multipart/form-data" class="admin-card admin-form">
    @csrf
    @include('admin.menu-items._form')
    <button class="admin-btn admin-btn--primary w-fit">حفظ</button>
</form>
@endsection
@endsection
