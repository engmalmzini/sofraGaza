@extends('layouts.admin')

@section('kicker', 'الموقع')
@section('title', 'محتوى الصفحة الرئيسية')

@section('content')
@php
    $imageUrls = [];
    foreach (\App\Support\HomeContent::fieldKeysByType('image') as $imgKey) {
        $imageUrls[$imgKey] = \App\Support\HomeContent::imageUrl($imgKey);
    }
@endphp
<div class="space-y-6">
    <div class="admin-card !p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-stone-900 text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-primary text-[24px]">handshake</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-stone-900">شعارات الشراكات</h2>
                    <p class="text-xs text-slate-500">تظهر أسفل قسم التطبيق في الصفحة الرئيسية. ارفع شعاراً أبيض/شفاف أو رمادياً لأفضل نتيجة.</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-stone-800">
                {{ $partners->count() }} شريك
            </span>
        </div>

        <div class="p-5 space-y-5">
            @if($partners->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($partners as $partner)
                        <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                            <form method="POST" action="{{ route('admin.homepage.partners.update', $partner) }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <div class="h-16 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center px-4">
                                    <img src="{{ $partner->imageUrl() }}" alt="{{ $partner->name }}" class="max-h-10 max-w-full object-contain grayscale">
                                </div>
                                <input type="text" name="name" value="{{ $partner->name }}" class="text-xs font-bold w-full !h-9 bg-white" placeholder="اسم الشريك">
                                <input type="url" name="url" value="{{ $partner->url }}" dir="ltr" class="text-xs font-mono w-full !h-9 bg-white" placeholder="https:// (اختياري)">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" name="sort_order" value="{{ $partner->sort_order }}" min="0" class="text-xs font-bold w-full !h-9 bg-white" placeholder="الترتيب">
                                    <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($partner->is_active)>
                                        ظاهر
                                    </label>
                                </div>
                                <input type="file" name="image" accept="image/*" class="text-[11px] w-full">
                                <button type="submit" class="admin-action-btn admin-action-btn--sm">حفظ</button>
                            </form>
                            <form method="POST" action="{{ route('admin.homepage.partners.destroy', $partner) }}" onsubmit="return confirm('حذف شعار {{ $partner->name }}؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-action-btn admin-action-btn--danger admin-action-btn--sm">حذف</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-500">لا توجد شعارات بعد. أضف أول شريك من النموذج أدناه.</p>
            @endif

            <form method="POST" action="{{ route('admin.homepage.partners.store') }}" enctype="multipart/form-data" class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-4 grid gap-3 md:grid-cols-4 items-end">
                @csrf
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-stone-900 mb-1">اسم الشريك</label>
                    <input type="text" name="name" required class="text-xs font-bold w-full !h-10 bg-white" placeholder="مثال: Zeitoun">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-900 mb-1">الشعار</label>
                    <input type="file" name="image" accept="image/*" required class="text-xs w-full bg-white rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-900 mb-1">رابط (اختياري)</label>
                    <input type="url" name="url" dir="ltr" class="text-xs font-mono w-full !h-10 bg-white" placeholder="https://">
                </div>
                <button type="submit" class="admin-action-btn h-10">إضافة شريك</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @foreach($schema as $sectionKey => $section)
            <div class="admin-card !p-0 overflow-hidden" id="home-{{ $sectionKey }}">
                <div class="px-5 py-4 border-b border-slate-100 bg-white">
                    <h2 class="text-base font-bold text-stone-900">{{ $section['label'] }}</h2>
                    @if(!empty($section['hint']))
                        <p class="text-xs text-slate-500 mt-0.5">{{ $section['hint'] }}</p>
                    @endif
                </div>
                <div class="p-5 grid gap-4 md:grid-cols-2">
                    @foreach($section['fields'] as $key => $field)
                        <div class="{{ in_array($field['type'] ?? 'text', ['textarea', 'image', 'toggle'], true) ? 'md:col-span-2' : '' }}">
                            @if(($field['type'] ?? 'text') !== 'toggle')
                                <label class="block text-xs font-bold text-stone-900 mb-1.5">{{ $field['label'] }}</label>
                            @endif
                            @if(($field['type'] ?? 'text') === 'toggle')
                                <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 cursor-pointer hover:border-primary/40 transition-colors">
                                    <input type="hidden" name="content[{{ $key }}]" value="0">
                                    <input type="checkbox" name="content[{{ $key }}]" value="1" class="mt-0.5 w-4 h-4 accent-primary" @checked(\App\Support\HomeContent::isOn($key))>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold text-stone-900">{{ $field['label'] }}</span>
                                        @if(!empty($field['help']))
                                            <span class="block text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ $field['help'] }}</span>
                                        @endif
                                    </span>
                                </label>
                            @elseif(($field['type'] ?? 'text') === 'textarea')
                                <textarea name="content[{{ $key }}]" rows="3" class="text-sm font-medium w-full rounded-lg bg-white">{{ $values[$key] ?? '' }}</textarea>
                            @elseif(($field['type'] ?? '') === 'image')
                                <div class="flex flex-col sm:flex-row gap-4 items-start">
                                    @if(!empty($imageUrls[$key]))
                                        <div class="w-40 h-24 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 shrink-0">
                                            <img src="{{ $imageUrls[$key] }}" alt="" class="w-full h-full object-cover">
                                        </div>
                                    @endif
                                    <div class="flex-1 space-y-2">
                                        <input type="file" name="images[{{ $key }}]" accept="image/*" class="text-xs w-full p-2 border border-dashed border-slate-300 rounded-lg bg-white">
                                        @if(!empty($values[$key]))
                                            <label class="flex items-center gap-2 text-xs font-bold text-rose-700">
                                                <input type="checkbox" name="remove_images[{{ $key }}]" value="1">
                                                حذف الصورة والعودة للافتراضية
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <input type="text" name="content[{{ $key }}]" value="{{ $values[$key] ?? '' }}" class="text-sm font-bold w-full !h-10 bg-white">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="sticky bottom-4 z-10 flex justify-end">
            <button type="submit" class="admin-action-btn shadow-lg px-6 h-11">حفظ كل النصوص والصور</button>
        </div>
    </form>
</div>
@endsection
