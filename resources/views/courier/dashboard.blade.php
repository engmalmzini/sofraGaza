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

    @forelse($orders as $order)
        @include('courier.partials.order-card', ['order' => $order])
    @empty
        <div class="courier-empty">
            <span class="material-symbols-outlined">{{ $tab === 'done' ? 'check_circle' : 'delivery_dining' }}</span>
            <strong>{{ $tab === 'done' ? 'لا تسليمات اليوم بعد' : 'لا طلبات مرسلة لك الآن' }}</strong>
            <p>{{ $tab === 'done' ? 'بعد أول تسليم ستظهر الطلبات هنا.' : 'عندما نرسل لك طلباً يظهر هنا مباشرة. حدّث الصفحة أو انتظر اتصال الإدارة.' }}</p>
        </div>
    @endforelse
@endif
@endsection
