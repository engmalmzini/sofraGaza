@extends('layouts.public')

@section('title', 'طلباتي')

@section('content')
<div class="mx-auto max-w-5xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <h1 class="font-headline-md text-2xl font-bold text-stone-900">طلباتي</h1>
    <div class="mt-6 space-y-3">
        @forelse($orders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="flex items-center justify-between gap-3 rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs">
                <div>
                    <div class="font-bold text-on-surface">#{{ $order->id }} — {{ $order->restaurant->name }}</div>
                    <div class="text-sm text-on-surface-variant">{{ $order->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="text-left shrink-0">
                    <div class="font-bold">{{ number_format($order->total, 2) }} <span class="ils">₪</span></div>
                    <div class="text-sm rounded-full bg-surface-container-low px-2 py-0.5 mt-1 inline-block">{{ $order->statusLabel() }}</div>
                </div>
            </a>
        @empty
            <p class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-8 text-on-surface-variant">لا توجد طلبات.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</div>
@endsection
