@extends('layouts.admin')

@section('title', 'إضافة مطعم')

@section('content')
@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'إضافة مطعم')

@section('content')
<form method="POST" action="{{ route('admin.restaurants.store') }}" enctype="multipart/form-data" class="admin-card admin-form">
    @csrf
    @include('admin.restaurants._form')
    <button class="admin-btn admin-btn--primary w-fit">حفظ</button>
</form>
@endsection
@endsection
