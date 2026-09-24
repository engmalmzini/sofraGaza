@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', 'العضويات')

@section('content')
<div class="admin-toolbar">
                <p class="text-sm text-on-surface-variant">باقات الولاء: أضف عضوية، عدّل السعر والمزايا والنسب، أو أوقف ظهورها للزبائن.</p>
    <a href="{{ route('admin.memberships.create') }}" class="admin-btn admin-btn--primary">عضوية جديدة</a>
</div>
<div class="admin-grid-2">
    @foreach($memberships as $membership)
        <article class="admin-card">
            <div class="flex justify-between items-center">
                <h2>{{ $membership->name }}</h2>
                @include('admin.partials.pill', ['status' => $membership->is_active ? 'approved' : 'cancelled', 'label' => $membership->is_active ? 'نشطة' : 'متوقفة'])
            </div>
            <div class="mt-2 text-2xl font-extrabold text-primary">{{ number_format($membership->monthly_price) }} <span class="ils">₪</span></div>
            <ul class="mt-3 text-sm leading-7 text-on-surface-variant">
                @foreach($membership->benefitsList() as $benefit)
                    <li>{{ $benefit }}</li>
                @endforeach
            </ul>
            <div class="mt-4 flex flex-wrap gap-3 text-sm">
                <a class="font-bold text-primary" href="{{ route('admin.memberships.edit', $membership) }}">تعديل الأسعار والمزايا</a>
                <form method="POST" action="{{ route('admin.memberships.toggle', $membership) }}">
                    @csrf
                    <button class="font-bold text-on-surface-variant">{{ $membership->is_active ? 'إيقاف' : 'تفعيل' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.memberships.destroy', $membership) }}" onsubmit="return confirm('حذف العضوية؟')">@csrf @method('DELETE')<button class="text-primary">حذف</button></form>
            </div>
        </article>
    @endforeach
</div>
@endsection
