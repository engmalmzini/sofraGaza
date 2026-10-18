@if(auth()->user()?->isSuperAdmin())
<section class="admin-card mt-6 border border-emerald-200 bg-emerald-50/40">
    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-emerald-700 text-[28px]">database</span>
        <div class="min-w-0 flex-1">
            <h2 class="text-base font-black text-emerald-950">نسخة احتياطية من قاعدة البيانات</h2>
            <p class="mt-1 text-sm leading-7 text-emerald-900/80">
                ينزّل ملف SQL فيه جداول المنصة وبياناتها الحالية (الزبائن، المطاعم، الطلبات، الحسابات الإدارية، والإعدادات)
                لاستعادتها لاحقًا عند الحاجة.
            </p>
            <form method="POST" action="{{ route('admin.settings.backup') }}" class="mt-4">
                @csrf
                <button type="submit" class="admin-btn admin-btn--primary w-fit">
                    <span class="material-symbols-outlined">download</span>
                    <span>تنزيل نسخة احتياطية SQL</span>
                </button>
            </form>
        </div>
    </div>
</section>
@endif
