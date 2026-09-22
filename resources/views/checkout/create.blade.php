@extends('layouts.public')

@section('title', 'إتمام الطلب')

@section('content')
<div class="mx-auto grid max-w-5xl gap-6 px-margin lg:px-margin-desktop py-5 lg:py-10 lg:grid-cols-5">
    <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs lg:col-span-3">
        @csrf
        <h1 class="font-headline-md text-2xl font-bold text-stone-900">بيانات التوصيل والدفع</h1>
        @if($addresses->isNotEmpty())
            <div class="space-y-2">
                <div class="text-sm font-bold text-on-surface">عناوين محفوظة</div>
                @foreach($addresses as $address)
                    <button type="button" class="block w-full rounded-xl bg-surface-container-low p-3 text-right text-sm"
                        onclick="document.getElementById('addr').value = @json($address->details); document.getElementById('phone').value = @json($address->phone);">
                        <strong>{{ $address->label }}</strong> — {{ $address->details }}
                    </button>
                @endforeach
            </div>
        @endif
        <div>
            <label class="mb-1 block text-sm font-bold">العنوان بالتفصيل</label>
            <textarea id="addr" name="address_details" rows="4" class="w-full rounded-xl bg-surface-container-low border-none px-3 py-3" placeholder="الحي، الشارع، أقرب معلم، رقم البناية">{{ old('address_details') }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">رقم هاتف للتواصل</label>
            <input id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="w-full h-12 rounded-xl bg-surface-container-low border-none px-3">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">ملاحظات</label>
            <input name="notes" value="{{ old('notes') }}" class="w-full h-12 rounded-xl bg-surface-container-low border-none px-3">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">صورة إشعار الحوالة</label>
            <input type="file" name="receipt" accept="image/*" class="w-full rounded-xl border border-dashed border-outline/40 p-3 text-sm bg-surface">
            <p class="mt-1 text-xs text-on-surface-variant">حوّل المبلغ النهائي ثم ارفع صورة الإشعار. الطلب يبقى بانتظار التأكيد حتى المراجعة.</p>
        </div>
        <button class="w-full rounded-xl bg-primary hover:bg-primary-container text-on-primary py-3 font-semibold">إرسال الطلب</button>
    </form>
    <aside class="rounded-2xl bg-primary p-5 text-on-primary lg:col-span-2 h-fit">
        <h2 class="font-bold text-lg">ملخص الطلب</h2>
        <p class="mt-1 text-sm text-on-primary/80">{{ $quote['restaurant']->name }}</p>
        <ul class="mt-4 space-y-2 text-sm">
            @foreach($quote['lines'] as $line)
                <li class="flex justify-between gap-2"><span>{{ $line['item']->name }} × {{ $line['qty'] }}</span><span>{{ number_format($line['line_total'], 2) }}</span></li>
            @endforeach
        </ul>
        <div class="mt-4 border-t border-white/15 pt-4 text-sm">
            <div class="flex justify-between"><span>السعر الأصلي</span><span>{{ number_format($quote['subtotal'], 2) }} <span class="ils">₪</span></span></div>
            <div class="mt-2 flex justify-between"><span>الخصم {{ $quote['discount_percent'] }}%</span><span>{{ number_format($quote['discount_amount'], 2) }} <span class="ils">₪</span></span></div>
            <div class="mt-2 flex justify-between"><span>التوصيل</span><span>{{ $quote['delivery_fee'] ?: 'مجاني' }}</span></div>
            <div class="mt-4 flex justify-between text-lg font-bold"><span>المطلوب تحويله</span><span>{{ number_format($quote['total'], 2) }} <span class="ils">₪</span></span></div>
            <p class="mt-3 text-tertiary-fixed font-medium">ستحصل على {{ $quote['points'] }} نقطة من هذه الطلبية بعد التسليم</p>
        </div>
    </aside>
</div>
@endsection
