@extends('layouts.partner')

@section('title', 'بيانات المطعم')

@section('content')
<form method="POST" action="{{ route('partner.restaurant.update') }}" enctype="multipart/form-data" class="admin-card admin-form admin-form--wide">
    @csrf
    @method('PUT')
    <div class="grid gap-3 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-bold">اسم المطعم</label>
            <input name="name" value="{{ old('name', $restaurant->name) }}">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">النوع</label>
            <select name="type">
                <option value="restaurant" @selected(old('type', $restaurant->type) === 'restaurant')>مطعم</option>
                <option value="cafe" @selected(old('type', $restaurant->type) === 'cafe')>كافي</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">نوع المطبخ</label>
            <select name="cuisine">
                @foreach($cuisines as $key => $label)
                    <option value="{{ $key }}" @selected(old('cuisine', $restaurant->cuisine) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">هاتف المطعم</label>
            <input name="phone" value="{{ old('phone', $restaurant->phone) }}" inputmode="numeric" minlength="10" maxlength="15" pattern="{{ \App\Support\PalestinianPhone::HTML_PATTERN }}" placeholder="059XXXXXXXX">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">رقم الهوية</label>
            <input name="owner_national_id" value="{{ old('owner_national_id', $restaurant->owner_national_id) }}" maxlength="9">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">رقم الرخصة (اختياري)</label>
            <input name="license_number" value="{{ old('license_number', $restaurant->license_number) }}">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">المنطقة</label>
            <select name="area">
                @foreach($areas as $area)
                    <option value="{{ $area['key'] }}" @selected(old('area', $restaurant->area) === $area['key'])>{{ $area['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-bold">العنوان التفصيلي</label>
            <input name="address" value="{{ old('address', $restaurant->address) }}">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">يفتح الساعة</label>
            <input type="time" name="opens_at" value="{{ old('opens_at', $restaurant->opens_at ? substr($restaurant->opens_at, 0, 5) : '09:00') }}">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">يغلق الساعة</label>
            <input type="time" name="closes_at" value="{{ old('closes_at', $restaurant->closes_at ? substr($restaurant->closes_at, 0, 5) : '23:00') }}">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-bold">الوصف</label>
            <textarea name="description" rows="4">{{ old('description', $restaurant->description) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-bold">صورة الغلاف</label>
            @if($restaurant->imageUrl())
                <img src="{{ $restaurant->imageUrl() }}" alt="" class="mb-3 h-28 w-full rounded-xl object-cover">
            @endif
            <input type="file" name="image" accept="image/*">
        </div>
    </div>
    @if($restaurant->isApproved())
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $restaurant->is_active))>
            استقبال الطلبات وظهور المطعم للزبائن
        </label>
    @endif
    <button class="admin-btn admin-btn--primary w-fit">حفظ التفاصيل</button>
</form>
@if($restaurant->isRejected())
    <form method="POST" action="{{ route('partner.restaurant.resubmit') }}" class="mt-4">
        @csrf
        <button class="admin-btn admin-btn--secondary">بعد تصحيح البيانات: إعادة الإرسال للمراجعة</button>
    </form>
@endif
@endsection
