@extends('layouts.partner')

@section('title', 'إضافة صنف')

@section('content')
<form method="POST" action="{{ route('partner.menu-items.store') }}" enctype="multipart/form-data" class="admin-card admin-form">
    @csrf
    @include('admin.menu-items._form')
    <button class="admin-btn admin-btn--primary w-fit">حفظ</button>
</form>
@endsection
