@extends('layouts.courier')

@section('title', match($tab) {
    'mine' => 'توصيلي',
    'done' => 'تسليمات اليوم',
    default => 'طلبات جاهزة',
})

@section('content')
<p class="courier-lead">
    @if($tab === 'ready')
        جاهز للاستلام من المطبخ. خذ الطلب وابدأ الطريق.
    @elseif($tab === 'mine')
        طلباتك الحالية. عند الوصول للزبون أكّد التسليم.
    @else
        ملخص ما سلّمته اليوم.
    @endif
</p>

@forelse($orders as $order)
    @include('courier.partials.order-card', ['order' => $order])
@empty
    <div class="courier-empty">
        <span class="material-symbols-outlined">{{ $tab === 'done' ? 'check_circle' : 'local_shipping' }}</span>
        <strong>
            @if($tab === 'ready')
                لا طلبات جاهزة الآن
            @elseif($tab === 'mine')
                لا يوجد توصيل نشط
            @else
                لا تسليمات اليوم بعد
            @endif
        </strong>
        <p>
            @if($tab === 'ready')
                حدّث الصفحة بعد قليل، أو انتظر إشعار جاهزية الطلب.
            @elseif($tab === 'mine')
                اختر طلباً من تبويب جاهز لبدء التوصيل.
            @else
                بعد أول تسليم ستظهر الطلبات هنا.
            @endif
        </p>
    </div>
@endforelse
@endsection
