@extends('layouts.public')

@section('title', 'النقاط')

@section('content')
<div class="mx-auto max-w-3xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <div class="rounded-2xl bg-secondary-fixed text-on-secondary-fixed p-5 mb-6 flex items-center justify-between gap-3">
        <div>
            <div class="text-sm opacity-80">رصيدك الحالي</div>
            <div class="text-4xl font-bold mt-1">{{ auth()->user()->points_balance }} نقطة</div>
        </div>
        <a href="{{ route('redeem.create') }}" class="inline-flex items-center gap-1 rounded-full bg-secondary text-on-secondary px-4 py-2 text-sm font-bold">
            <span class="material-symbols-outlined text-[16px]">redeem</span>
            استبدال
        </a>
    </div>
    <h1 class="font-headline-sm text-xl font-bold text-on-surface mb-4">سجل النقاط</h1>
    <div class="space-y-2">
        @forelse($transactions as $row)
            <div class="flex justify-between rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs">
                <div>
                    <div class="font-bold text-on-surface">{{ $row->description }}</div>
                    <div class="text-xs text-on-surface-variant">{{ $row->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="{{ $row->points >= 0 ? 'text-secondary' : 'text-primary' }} font-bold">{{ $row->points > 0 ? '+' : '' }}{{ $row->points }}</div>
            </div>
        @empty
            <p class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-8 text-on-surface-variant">لا توجد حركات بعد.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $transactions->links() }}</div>
</div>
@endsection
