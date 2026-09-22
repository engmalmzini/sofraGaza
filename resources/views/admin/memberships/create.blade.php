@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', 'إضافة عضوية')

@section('content')
<form method="POST" action="{{ route('admin.memberships.store') }}" class="admin-card admin-form">
    @csrf
    @include('admin.memberships._form')
    <button class="admin-btn admin-btn--primary w-fit">حفظ</button>
</form>
@endsection
