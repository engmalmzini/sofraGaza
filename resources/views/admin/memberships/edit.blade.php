@extends('layouts.admin')

@section('title', 'تعديل عضوية')

@section('content')
@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', 'تعديل عضوية')

@section('content')
<form method="POST" action="{{ route('admin.memberships.update', $membership) }}" class="admin-card admin-form">
    @csrf
    @method('PUT')
    @include('admin.memberships._form')
    <button class="admin-btn admin-btn--primary w-fit">تحديث</button>
</form>
@endsection
@endsection
