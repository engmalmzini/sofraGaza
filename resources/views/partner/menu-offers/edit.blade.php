@extends('layouts.partner')

@section('title', 'تعديل عرض')

@section('content')
<form method="POST" action="{{ route('partner.menu-offers.update', $offer) }}" enctype="multipart/form-data" class="admin-card admin-form space-y-4">
    @csrf
    @method('PUT')
    @include('admin.menu-offers._form')
    @if($items->isNotEmpty())
        <button class="admin-btn admin-btn--primary w-fit">حفظ التعديل</button>
    @endif
</form>
@endsection
