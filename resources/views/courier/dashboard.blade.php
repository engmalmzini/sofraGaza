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
