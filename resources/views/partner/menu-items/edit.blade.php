@extends('layouts.partner')

@section('title', 'تعديل طبق')

@section('content')
<form method="POST" action="{{ route('partner.menu-items.update', $menuItem) }}" enctype="multipart/form-data" class="admin-card admin-form">
    @csrf
    @method('PUT')
    @include('admin.menu-items._form')
    <button class="admin-btn admin-btn--primary w-fit">تحديث</button>
</form>
@endsection
