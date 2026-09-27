@extends('layouts.admin')

@section('kicker', 'المطاعم والزبائن')
@section('title', 'تقييمات وآراء الزبائن')

@section('content')
<div class="space-y-6">
    <div class="admin-card overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-on-surface">إدارة تقييمات الزبائن</h2>
                <p class="text-xs text-on-surface-variant">مراجعة تقييمات الزبائن للمطاعم واعتمادها أو إخفائها</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant border-b border-slate-200">
                        <th class="py-3 px-4 font-bold">#</th>
                        <th class="py-3 px-4 font-bold">الزبون</th>
                        <th class="py-3 px-4 font-bold">المطعم</th>
                        <th class="py-3 px-4 font-bold">التقييم</th>
                        <th class="py-3 px-4 font-bold">التعليق</th>
                        <th class="py-3 px-4 font-bold">الحالة</th>
                        <th class="py-3 px-4 font-bold">التاريخ</th>
                        <th class="py-3 px-4 font-bold">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reviews as $rev)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold">{{ $rev->id }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-on-surface">{{ $rev->user->name }}</span>
                                <div class="text-[11px] text-on-surface-variant">{{ $rev->user->phone }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('restaurants.show', $rev->restaurant) }}" target="_blank" class="font-bold text-primary hover:underline">
                                    {{ $rev->restaurant->name }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-0.5 text-amber-500">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="material-symbols-outlined text-[16px] {{ $i <= $rev->rating ? 'fill-1' : 'opacity-30' }}">star</span>
                                    @endfor
                                    <span class="text-xs font-bold text-stone-800 mr-1">({{ $rev->rating }})</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-sm text-stone-700">
                                {{ $rev->comment ?: 'بدون تعليق مكتوب' }}
                                @if($rev->isVerifiedPurchase())
                                    <span class="inline-block mr-1 px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 text-[10px] font-semibold">طلب مؤكد</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($rev->is_approved)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-semibold text-[11px]">
                                        معتمد وظاهر
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-stone-100 text-stone-700 font-semibold text-[11px]">
                                        مخفي
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-on-surface-variant whitespace-nowrap">{{ $rev->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.reviews.toggle', $rev) }}">
                                        @csrf
                                        <button type="submit" class="admin-btn text-xs !py-1 !px-2.5 {{ $rev->is_approved ? 'admin-btn--ghost text-amber-700' : 'admin-btn--primary' }}">
                                            {{ $rev->is_approved ? 'إخفاء' : 'اعتماد' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.reviews.destroy', $rev) }}" onsubmit="return confirm('حذف هذا التقييم نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--err text-xs !py-1 !px-2.5">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-on-surface-variant">
                                لا توجد تقييمات مسجلة بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
