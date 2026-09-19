@extends('layouts.public')

@section('title', 'العناوين')

@section('content')
<div class="mx-auto max-w-3xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <h1 class="font-headline-md text-2xl font-bold text-stone-900">عناويني</h1>
    <form method="POST" action="{{ route('account.addresses.store') }}" class="mt-6 space-y-3 rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs">
        @csrf
        <input name="label" placeholder="الاسم (المنزل، العمل)" class="w-full h-12 rounded-xl bg-surface-container-low border-none px-3">
        <textarea name="details" rows="3" placeholder="العنوان بالتفصيل" class="w-full rounded-xl bg-surface-container-low border-none px-3 py-3"></textarea>
        <input name="phone" value="{{ auth()->user()->phone }}" class="w-full h-12 rounded-xl bg-surface-container-low border-none px-3">
        <button class="rounded-xl bg-primary hover:bg-primary-container text-on-primary px-5 py-2.5 font-semibold">حفظ عنوان</button>
    </form>
    <div class="mt-6 space-y-3">
        @forelse($addresses as $address)
            <div class="flex items-start justify-between rounded-2xl bg-surface-container-lowest border border-slate-100 p-4 shadow-xs">
                <div>
                    <div class="font-bold text-on-surface">{{ $address->label }}</div>
                    <div class="text-sm text-on-surface-variant">{{ $address->details }}</div>
                    <div class="text-sm text-on-surface-variant">{{ $address->phone }}</div>
                </div>
                <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">@csrf @method('DELETE')<button class="text-sm text-primary font-semibold">حذف</button></form>
            </div>
        @empty
            <p class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-6 text-on-surface-variant">لا توجد عناوين محفوظة.</p>
        @endforelse
    </div>
</div>
@endsection
