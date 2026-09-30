@extends('layouts.courier')

@section('title', $tab === 'done' ? 'تسليمات اليوم' : 'طلباتي')

@section('content')
@if(! auth()->user()->isCourierApproved())
    <div class="courier-empty">
        <span class="material-symbols-outlined">{{ auth()->user()->isCourierRejected() ? 'cancel' : 'hourglass_top' }}</span>
        <strong>{{ auth()->user()->courierStatusLabel() }}</strong>
        @if(auth()->user()->isCourierPending())
            <p>طلبك قيد المراجعة. بعد موافقة الإدارة يصلك إشعار برابط هذه اللوحة، ثم نرسل لك الطلبات شخصياً.</p>
        @else
            <p>{{ auth()->user()->courier_rejection_reason ?: 'لم يُقبل الطلب. يمكنك التواصل مع الإدارة إذا لزم الأمر.' }}</p>
        @endif
    </div>
@else
    @php
        $courierBalance = auth()->user()->courierAvailableBalance();
    @endphp
    <a href="{{ route('courier.wallet') }}" class="mb-4 flex items-center justify-between p-3.5 bg-gradient-to-l from-emerald-600 to-teal-700 text-white rounded-2xl shadow-sm hover:opacity-95 transition-opacity">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-white">account_balance_wallet</span>
            </div>
            <div>
                <div class="text-xs text-white/80">رصيدك المتاح للسحب</div>
                <div class="text-lg font-black">{{ number_format($courierBalance, 2) }} ₪</div>
            </div>
        </div>
        <div class="flex items-center gap-1 text-xs font-bold bg-white/20 px-3 py-1.5 rounded-xl">
            <span>المحفظة وسحب الأرباح</span>
            <span class="material-symbols-outlined text-sm">arrow_back</span>
        </div>
    </a>

    <p class="courier-lead">
        @if($tab === 'mine')
            الطلبات التي أرسلتها لك الإدارة. افتح الطلب وتابع الاستلام والتسليم.
        @else
            ملخص ما سلّمته اليوم.
        @endif
    </p>

    <div id="courier-orders-container" data-last-id="{{ $orders->max('id') ?? 0 }}" data-tab="{{ $tab }}" data-live-url="{{ route('courier.orders.live') }}">
        @include('courier.partials.live-cards', ['orders' => $orders, 'tab' => $tab])
    </div>
@endif
@endsection
