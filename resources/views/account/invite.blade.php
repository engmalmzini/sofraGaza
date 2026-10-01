@extends('layouts.public')

@section('title', 'ادعُ صديق')

@section('content')
<div class="mx-auto max-w-3xl px-4 sm:px-6 py-6 sm:py-8 pb-24 space-y-5">
    <div>
        <p class="text-xs font-extrabold text-amber-700">نمو عضوي مستمر</p>
        <h1 class="text-2xl font-black text-stone-900">ادعُ صديق، وكلاكما ياخذ نقاط</h1>
        <p class="text-sm text-stone-500 mt-1">البرنامج شغال دائماً — مش بس وقت الإطلاق. كل زبون عنده كود. صديقك يحطه عند إنشاء الحساب، وأنت وهو بتاخدوا نقاط.</p>
    </div>

    <section class="rounded-3xl bg-stone-900 text-white p-5 sm:p-6 space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold text-white/60">كود الدعوة تاعك</p>
                <p class="mt-1 text-3xl font-black tracking-[0.28em] font-mono" dir="ltr">{{ $code }}</p>
            </div>
            <span class="material-symbols-outlined text-[28px] text-amber-300">group_add</span>
        </div>
        <p class="text-sm text-white/80 leading-relaxed">
            أنت تاخذ <strong class="text-white">{{ $inviterPoints }}</strong> نقطة عند كل صديق يسجّل بكودك، وهو ياخذ <strong class="text-white">{{ $inviteePoints }}</strong> نقطة هدية انضمام.
        </p>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="js-copy inline-flex items-center gap-1.5 rounded-full bg-white text-stone-900 px-4 py-2 text-sm font-bold" data-copy="{{ $code }}">
                <span class="material-symbols-outlined text-[18px]">content_copy</span>
                <span class="js-copy-label">نسخ الكود</span>
            </button>
            <button type="button" class="js-copy inline-flex items-center gap-1.5 rounded-full bg-white/10 text-white px-4 py-2 text-sm font-bold border border-white/15" data-copy="{{ $shareUrl }}">
                <span class="material-symbols-outlined text-[18px]">link</span>
                <span class="js-copy-label">نسخ الرابط</span>
            </button>
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 text-white px-4 py-2 text-sm font-bold">
                <span class="material-symbols-outlined text-[18px]">chat</span>
                واتساب
            </a>
        </div>
    </section>

    <section class="rounded-2xl bg-white border border-stone-200 p-4 space-y-2">
        <h2 class="text-sm font-extrabold text-stone-900">كيف تشتغل؟</h2>
        <ol class="text-sm text-stone-600 space-y-1.5 list-decimal pr-5">
            <li>ابعت كودك أو رابط الدعوة لصديق.</li>
            <li>عند إنشاء حسابه يختار «شخص دعاك» ويحط الكود.</li>
            <li>تنضاف النقاط مباشرة لحسابك وحسابه.</li>
        </ol>
        <p class="text-xs text-stone-400 pt-1">الكود يُستخدم مرة لكل حساب جديد، وما ينفع تستخدم كودك بنفسك.</p>
    </section>

    <section class="space-y-3">
        <div class="flex items-end justify-between gap-2">
            <h2 class="text-base font-extrabold text-stone-900">من سجّل بكودك</h2>
            <span class="text-xs font-bold text-stone-500">{{ $invitesCount }} صديق</span>
        </div>
        @forelse($invites as $friend)
            <div class="flex items-center justify-between rounded-2xl bg-white border border-stone-200 px-4 py-3">
                <div>
                    <p class="text-sm font-bold text-stone-900">{{ $friend->name }}</p>
                    <p class="text-[11px] text-stone-400">{{ $friend->created_at?->format('Y-m-d') }}</p>
                </div>
                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">+{{ $inviterPoints }} نقطة</span>
            </div>
        @empty
            <p class="text-sm text-stone-500 bg-white rounded-2xl border border-stone-200 px-4 py-6">ما في حدا سجّل بكودك بعد. ابدأ بأقرب صديق.</p>
        @endforelse
    </section>
</div>
@endsection
