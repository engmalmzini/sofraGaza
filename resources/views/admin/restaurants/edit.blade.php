@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'تعديل مطعم')

@section('content')
<form method="POST" action="{{ route('admin.restaurants.update', $restaurant) }}" enctype="multipart/form-data" class="admin-card admin-form">
    @csrf
    @method('PUT')
    @include('admin.restaurants._form')
    <button class="admin-btn admin-btn--primary w-fit">تحديث</button>
</form>
@endsection
