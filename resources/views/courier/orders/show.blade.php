@extends('layouts.courier')

@php
    $pickup = $order->restaurant->address ?: $order->restaurant->areaLabel();
    $mapsPickup = 'https://www.google.com/maps/search/?api=1&query='.urlencode($pickup);
    $mapsDrop = 'https://www.google.com/maps/search/?api=1&query='.urlencode($order->address_details);
    $restaurantPhone = $order->restaurant->phone;
    $backTab = $order->status === 'delivered' ? 'done' : 'mine';
@endphp

@section('title', 'طلب #'.$order->id)
@section('back', route('courier.dashboard', ['tab' => $backTab]))

@section('content')
<article class="courier-detail">
    <header class="courier-detail__head">
        <span class="courier-pill courier-pill--{{ $order->status }}">{{ $order->statusLabel() }}</span>
        <b>{{ number_format($order->total, 2) }} <span class="ils">₪</span></b>
    </header>

    <section class="courier-stop">
        <div class="courier-stop__label">1 · استلام</div>
        <h2>{{ $order->restaurant->name }}</h2>
        <p>{{ $pickup }}</p>
        <div class="courier-card__actions">
            @if($restaurantPhone)
                <a class="courier-btn courier-btn--ghost" href="tel:{{ $restaurantPhone }}">
                    <span class="material-symbols-outlined">call</span>
                    المطعم
                </a>
            @endif
            <a class="courier-btn courier-btn--ghost" href="{{ $mapsPickup }}" target="_blank" rel="noopener">
                <span class="material-symbols-outlined">map</span>
                الخريطة
            </a>
        </div>
    </section>

    <section class="courier-stop courier-stop--drop">
        <div class="courier-stop__label">2 · تسليم</div>
        <h2>{{ $order->user->name }}</h2>
        <p>{{ $order->address_details }}</p>
        @if($order->notes)
            <p class="courier-note">{{ $order->notes }}</p>
        @endif
        <div class="courier-card__actions">
            <a class="courier-btn courier-btn--ghost" href="tel:{{ $order->phone }}">
                <span class="material-symbols-outlined">call</span>
                الزبون
            </a>
            <a class="courier-btn courier-btn--ghost" href="{{ $mapsDrop }}" target="_blank" rel="noopener">
                <span class="material-symbols-outlined">map</span>
                الخريطة
            </a>
        </div>
    </section>

    <section class="courier-items-wrap">
        <h2>الأصناف</h2>
        <ul class="courier-items">
            @foreach($order->items as $item)
                <li>
                    <span>{{ $item->quantity }}× {{ $item->name }}</span>
                    <b>{{ number_format($item->line_total, 2) }} <span class="ils">₪</span></b>
                </li>
            @endforeach
        </ul>
    </section>
</article>

@if($order->courier_id === auth()->id() && $order->status === 'delivering')
    <form method="POST" action="{{ route('courier.orders.complete', $order) }}" class="courier-sticky">
        @csrf
        <button class="courier-btn courier-btn--ok courier-btn--block">تم التسليم</button>
    </form>
@endif
@endsection
