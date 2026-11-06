@extends('layouts.partner')

@section('title', 'عرض جديد')

@section('content')
<form method="POST" action="{{ route('partner.menu-offers.store') }}" enctype="multipart/form-data" class="admin-card admin-form space-y-4">
    @csrf
    @include('admin.menu-offers._form')
    @if($items->isNotEmpty())
        <button class="admin-btn admin-btn--primary w-fit">حفظ العرض</button>
    @endif
</form>
@endsection
