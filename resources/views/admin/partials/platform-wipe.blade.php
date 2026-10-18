@if(auth()->user()?->isSuperAdmin())
<section class="admin-card mt-6 border border-rose-200 bg-rose-50/40">
    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-rose-600 text-[28px]">warning</span>
        <div class="min-w-0 flex-1">
            <h2 class="text-base font-black text-rose-950">حذف كل بيانات المنصة</h2>
            <p class="mt-1 text-sm leading-7 text-rose-900/80">
                يحذف الزبائن، المطاعم، الطلبات، المناديب، العضويات، المالية، والإشعارات دفعة واحدة.
                <strong>حسابات الإدارة والمدير تبقى في قاعدة البيانات</strong>، وكذلك إعدادات المنصة.
            </p>
            <form method="POST" action="{{ route('admin.settings.wipe') }}" class="mt-4 grid gap-3 max-w-xl" onsubmit="return confirm('هذا الإجراء لا يمكن التراجع عنه. حذف كل البيانات مع الإبقاء على حسابات الإدارة؟');">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-rose-950 mb-1">اكتب «حذف الكل» للتأكيد</label>
                    <input type="text" name="confirmation" required autocomplete="off" class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2.5 text-sm" placeholder="حذف الكل">
                </div>
                <div>
                    <label class="block text-xs font-bold text-rose-950 mb-1">كلمة مرور حسابك</label>
                    <input type="password" name="password" required class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2.5 text-sm">
                </div>
                <button type="submit" class="admin-btn admin-btn--danger w-fit">
                    <span class="material-symbols-outlined">delete_forever</span>
                    <span>حذف كل البيانات والإبقاء على حسابات الإدارة</span>
                </button>
            </form>
        </div>
    </div>
</section>
@endif
