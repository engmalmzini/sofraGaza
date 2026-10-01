@extends('layouts.public')

@section('title', $forHost ? 'إرسال الطلب الجماعي' : 'دفع نصيبي')
@section('hideFloatingCart', true)

@section('content')
@php
    $walletBalance = (float) auth()->user()->wallet_balance;
    $canWallet = $walletBalance >= (float) $charge;
@endphp
<div class="mx-auto max-w-lg px-3 sm:px-4 py-5 sm:py-8 pb-36">
    <div class="mb-5">
        <p class="text-xs font-extrabold text-primary mb-1">{{ $group->restaurant->name }}</p>
        <h1 class="text-2xl font-black text-stone-900 tracking-tight">
            {{ $forHost ? 'إرسال الطلب الجماعي' : 'ادفع نصيبك' }}
        </h1>
        <p class="mt-1 text-sm text-stone-500">
            {{ $forHost ? 'فاتورة واحدة وتوصيل واحد. الضيوف دفعوا أكلهم، وأنت تدفع أكلك + التوصيل.' : 'بتدفع قيمة أكلك فقط. التوصيل على صاحب الطلب.' }}
        </p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs mb-4 space-y-2">
        @if($forHost)
            @foreach($group->guests() as $guest)
                <div class="flex justify-between text-xs text-stone-600">
                    <span>{{ $guest->displayName() }}</span>
                    <span class="font-mono font-bold">{{ number_format((float) $guest->total, 2) }} ₪ · مدفوع</span>
                </div>
            @endforeach
            <div class="flex justify-between text-xs text-stone-600 pt-2 border-t border-slate-100">
                <span>أكلك</span>
                <span class="font-mono font-bold">{{ number_format((float) ($hostFood ?? 0), 2) }} ₪</span>
            </div>
            <div class="flex justify-between text-xs text-stone-600">
                <span>التوصيل</span>
                <span class="font-mono font-bold">{{ number_format((float) $deliveryFee, 2) }} ₪</span>
            </div>
        @elseif(!empty($quote['lines']))
            @foreach($quote['lines'] as $line)
                <div class="flex justify-between text-xs text-stone-600">
                    <span>{{ $line['qty'] }}× {{ $line['item']->name }}</span>
                    <span class="font-mono font-bold">{{ number_format($line['line_total'], 2) }} ₪</span>
                </div>
            @endforeach
        @endif
        <div class="flex justify-between pt-2 border-t border-slate-100">
            <span class="font-extrabold text-stone-900">{{ $forHost ? 'مطلوب منك الآن' : 'نصيبك' }}</span>
            <span class="font-mono text-lg font-black text-primary">{{ number_format((float) $charge, 2) }} ₪</span>
        </div>
    </div>

    <form method="POST" action="{{ route('group-orders.pay.store', $group) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="payment_method" id="payment-method-input" value="{{ old('payment_method', $canWallet ? 'wallet' : 'receipt') }}">

        @if($forHost)
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs space-y-3">
                <h3 class="text-sm font-extrabold text-stone-900">عنوان التوصيل</h3>
                <div>
                    <label class="block text-xs font-bold text-stone-800 mb-1.5">المنطقة</label>
                    <select name="area" class="w-full h-12 rounded-xl bg-stone-50 border border-slate-200 px-3 text-sm outline-none focus:border-primary">
                        @foreach($deliveryAreas as $area)
                            <option value="{{ $area['key'] }}" @selected(old('area', $selectedAreaKey) === $area['key'])>
                                {{ $area['label'] ?? $area['key'] }} — {{ number_format((float) ($area['delivery_fee'] ?? 0), 0) }} ₪
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-800 mb-1.5">العنوان بالتفصيل</label>
                    <textarea name="address_details" rows="3" required minlength="10"
                              class="w-full rounded-xl bg-stone-50 border border-slate-200 p-3 text-sm outline-none focus:border-primary"
                              placeholder="الحي، الشارع، أقرب معلم">{{ old('address_details', $user->addresses->first()?->details ?? '') }}</textarea>
                    @error('address_details')<p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-800 mb-1.5">رقم التواصل</label>
                    <input type="tel" name="phone" dir="ltr" required
                           value="{{ old('phone', $user->phone) }}"
                           class="w-full h-12 rounded-xl bg-stone-50 border border-slate-200 px-3 text-sm font-mono outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-800 mb-1.5">ملاحظة للكابتن (اختياري)</label>
                    <input type="text" name="notes" value="{{ old('notes') }}"
                           class="w-full h-12 rounded-xl bg-stone-50 border border-slate-200 px-3 text-sm outline-none focus:border-primary"
                           placeholder="مثلاً: اطرق الجرس">
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs space-y-2">
            <h3 class="text-sm font-extrabold text-stone-900 mb-2">طريقة الدفع</h3>

            <button type="button" id="method-wallet" onclick="selectGroupPay('wallet')"
                    class="w-full rounded-xl border-2 p-3 flex items-center justify-between text-right {{ $canWallet ? '' : 'opacity-50' }}"
                    @disabled(! $canWallet)>
                <div>
                    <p class="text-sm font-extrabold text-stone-900">محفظة سفرة غزة</p>
                    <p class="text-[11px] text-stone-500">رصيدك: {{ number_format($walletBalance, 2) }} ₪</p>
                </div>
                <span class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center">
                    <span id="dot-wallet" class="w-2.5 h-2.5 rounded-full bg-primary hidden"></span>
                </span>
            </button>

            <button type="button" id="method-receipt" onclick="selectGroupPay('receipt')"
                    class="w-full rounded-xl border-2 p-3 flex items-center justify-between text-right">
                <div>
                    <p class="text-sm font-extrabold text-stone-900">حوالة / إشعار</p>
                    <p class="text-[11px] text-stone-500">حوّل ثم ارفع صورة الإشعار</p>
                </div>
                <span class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center">
                    <span id="dot-receipt" class="w-2.5 h-2.5 rounded-full bg-primary hidden"></span>
                </span>
            </button>

            <div id="receipt-box" class="pt-2 {{ old('payment_method', $canWallet ? 'wallet' : 'receipt') === 'receipt' ? '' : 'hidden' }}">
                <p class="text-[11px] text-stone-500 mb-2">
                    جوال باي: <span dir="ltr" class="font-mono font-bold text-stone-800">{{ $paymentAccounts['jawwal_pay_number'] }}</span>
                </p>
                <label class="block rounded-xl border-2 border-dashed border-slate-200 p-4 text-center cursor-pointer">
                    <input type="file" name="receipt" id="receipt-input" accept="image/*" class="sr-only" onchange="previewGroupReceipt(this)">
                    <span class="material-symbols-outlined text-primary text-[28px]">upload</span>
                    <p class="text-xs font-extrabold text-stone-900 mt-1">ارفع صورة الإشعار</p>
                    <p id="receipt-name" class="text-[11px] text-stone-400 mt-0.5">لقطة شاشة من التطبيق</p>
                </label>
                @error('receipt')<p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>@enderror
            </div>
        </div>

        <button type="submit" class="w-full h-14 rounded-2xl bg-primary text-white font-extrabold text-base shadow-[0_10px_22px_rgba(163,57,0,0.28)]">
            {{ $forHost ? 'أرسل الطلب الجماعي' : 'ادفع نصيبي' }}
        </button>
    </form>
</div>

<script>
function selectGroupPay(method) {
    const input = document.getElementById('payment-method-input');
    const box = document.getElementById('receipt-box');
    const receipt = document.getElementById('receipt-input');
    input.value = method;
    document.getElementById('dot-wallet').classList.toggle('hidden', method !== 'wallet');
    document.getElementById('dot-receipt').classList.toggle('hidden', method !== 'receipt');
    document.getElementById('method-wallet').classList.toggle('border-primary', method === 'wallet');
    document.getElementById('method-receipt').classList.toggle('border-primary', method === 'receipt');
    box.classList.toggle('hidden', method !== 'receipt');
    if (receipt) receipt.required = method === 'receipt';
}
function previewGroupReceipt(input) {
    const label = document.getElementById('receipt-name');
    if (input.files && input.files[0] && label) label.textContent = input.files[0].name;
}
selectGroupPay(document.getElementById('payment-method-input').value);
</script>
@endsection
