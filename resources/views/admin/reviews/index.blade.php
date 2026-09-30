@extends('layouts.admin')

@section('kicker', 'المطاعم والزبائن')
@section('title', 'تقييمات وآراء الزبائن')

@section('content')
<div class="space-y-5">
    {{-- 1. Summary Metrics Cards --}}
    <div class="admin-metrics !grid-cols-2 lg:!grid-cols-4">
        <div class="admin-metric">
            <div class="admin-metric__top">
                <span>إجمالي التقييمات</span>
                <span class="admin-metric__icon">
                    <span class="material-symbols-outlined">reviews</span>
                </span>
            </div>
            <strong>{{ number_format($totalCount) }}</strong>
            <small class="admin-metric__note">كافة تقييمات الزبائن المسجلة</small>
        </div>

        <div class="admin-metric">
            <div class="admin-metric__top">
                <span>التقييمات المعتمدة</span>
                <span class="admin-metric__icon !bg-emerald-50 !text-emerald-700 !border-emerald-200">
                    <span class="material-symbols-outlined">verified</span>
                </span>
            </div>
            <strong class="!text-emerald-700">{{ number_format($approvedCount) }}</strong>
            <small class="admin-metric__note">معروضة ومتاحة للجميع بالمتجر</small>
        </div>

        <div class="admin-metric">
            <div class="admin-metric__top">
                <span>التقييمات المخفية</span>
                <span class="admin-metric__icon !bg-slate-100 !text-slate-600 !border-slate-200">
                    <span class="material-symbols-outlined">visibility_off</span>
                </span>
            </div>
            <strong class="!text-slate-700">{{ number_format($hiddenCount) }}</strong>
            <small class="admin-metric__note">محجوبة عن الظهور للزبائن</small>
        </div>

        <div class="admin-metric admin-metric--accent">
            <div class="admin-metric__top">
                <span>متوسط التقييم العام</span>
                <span class="admin-metric__icon">
                    <span class="material-symbols-outlined">star</span>
                </span>
            </div>
            <strong>{{ number_format($avgRating, 1) }} <span class="text-sm font-bold text-slate-400">/ 5</span></strong>
            <small class="admin-metric__note">معدل الرضا الإجمالي لكافة المطاعم</small>
        </div>
    </div>

    {{-- 2. Filters & Search Toolbar --}}
    <div class="admin-toolbar flex-col md:flex-row items-stretch md:items-center gap-3">
        <div class="admin-chips">
            <a class="admin-chip {{ !request('status') ? 'is-active' : '' }}" 
               href="{{ route('admin.reviews.index', request()->except(['status', 'page'])) }}">
                الكل ({{ $totalCount }})
            </a>
            <a class="admin-chip {{ request('status') === 'approved' ? 'is-active' : '' }}" 
               href="{{ route('admin.reviews.index', array_merge(request()->except('page'), ['status' => 'approved'])) }}">
                المعتمدة ({{ $approvedCount }})
            </a>
            <a class="admin-chip {{ request('status') === 'hidden' ? 'is-active' : '' }}" 
               href="{{ route('admin.reviews.index', array_merge(request()->except('page'), ['status' => 'hidden'])) }}">
                المخفية ({{ $hiddenCount }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex flex-wrap items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            {{-- Filter by Stars --}}
            <select name="rating" onchange="this.form.submit()" class="!w-auto !min-h-[2.3rem] !py-1 !px-3 !text-xs !rounded-full">
                <option value="">جميع النجوم</option>
                <option value="5" @selected(request('rating') == '5')>⭐⭐⭐⭐⭐ (5 نجوم)</option>
                <option value="4" @selected(request('rating') == '4')>⭐⭐⭐⭐ (4 نجوم)</option>
                <option value="3" @selected(request('rating') == '3')>⭐⭐⭐ (3 نجوم)</option>
                <option value="2" @selected(request('rating') == '2')>⭐⭐ (نجمتان)</option>
                <option value="1" @selected(request('rating') == '1')>⭐ (نجمة واحدة)</option>
            </select>

            {{-- Filter by Restaurant --}}
            <select name="restaurant_id" onchange="this.form.submit()" class="!w-auto !min-h-[2.3rem] !py-1 !px-3 !text-xs !rounded-full">
                <option value="">جميع المطاعم</option>
                @foreach($restaurants as $rest)
                    <option value="{{ $rest->id }}" @selected(request('restaurant_id') == $rest->id)>{{ $rest->name }}</option>
                @endforeach
            </select>

            {{-- Search Input --}}
            <div class="relative">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="بحث بالزبون أو التعليق..." 
                       class="!w-48 !min-h-[2.3rem] !py-1 !px-3 !text-xs !rounded-full">
            </div>

            <button type="submit" class="admin-action-btn admin-action-btn--primary admin-action-btn--sm">
                <span class="material-symbols-outlined">search</span>
                <span>بحث</span>
            </button>

            @if(request('status') || request('rating') || request('restaurant_id') || request('q'))
                <a href="{{ route('admin.reviews.index') }}" class="admin-action-btn admin-action-btn--outline admin-action-btn--sm" title="إعادة ضبط الفلاتر">
                    <span class="material-symbols-outlined">restart_alt</span>
                    <span>مسح</span>
                </a>
            @endif
        </form>
    </div>

    {{-- 3. Professional Reviews Table --}}
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="w-16">#</th>
                    <th>الزبون</th>
                    <th>المطعم</th>
                    <th>التقييم</th>
                    <th class="min-w-[18rem]">التعليق ومطابقة الطلب</th>
                    <th>الحالة</th>
                    <th>تاريخ التقييم</th>
                    <th class="text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $rev)
                    <tr>
                        <td class="font-mono font-bold text-slate-500">
                            #{{ $rev->id }}
                        </td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ mb_substr($rev->user->name ?? 'ع', 0, 1) }}
                                </span>
                                <div>
                                    <div class="font-bold text-slate-900 leading-tight">
                                        {{ $rev->user->name ?? 'عميل محذوف' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5" dir="ltr">
                                        {{ $rev->user->phone ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($rev->restaurant)
                                <a href="{{ route('restaurants.show', $rev->restaurant) }}" target="_blank" 
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-orange-50/80 hover:bg-orange-100 border border-orange-200/70 text-slate-900 font-bold text-xs transition-colors">
                                    <span class="material-symbols-outlined text-[15px] text-orange-600">storefront</span>
                                    <span>{{ $rev->restaurant->name }}</span>
                                </a>
                            @else
                                <span class="text-slate-400 text-xs italic">مطعم غير محدد</span>
                            @endif
                        </td>
                        <td>
                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-amber-50/80 border border-amber-200/80">
                                <div class="flex items-center text-amber-500">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="material-symbols-outlined text-[15px] {{ $i <= $rev->rating ? 'fill-1 text-amber-500' : 'text-slate-200' }}">star</span>
                                    @endfor
                                </div>
                                <span class="text-xs font-black text-amber-900 font-mono">{{ number_format($rev->rating, 1) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="space-y-1.5">
                                @if($rev->comment)
                                    <p class="text-xs text-slate-800 leading-relaxed font-medium bg-slate-50/80 p-2 rounded-lg border border-slate-100">
                                        "{{ $rev->comment }}"
                                    </p>
                                @else
                                    <span class="text-xs text-slate-400 italic">بدون تعليق نصي مكتوب</span>
                                @endif

                                <div>
                                    @if($rev->isVerifiedPurchase())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold">
                                            <span class="material-symbols-outlined text-[12px] text-emerald-600">verified</span>
                                            <span>طلب مؤكد</span>
                                            @if($rev->order_id)
                                                <a href="{{ route('admin.orders.show', $rev->order_id) }}" class="underline hover:text-emerald-950 font-mono font-bold">
                                                    #{{ $rev->order_id }}
                                                </a>
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-slate-400 text-[10px]">
                                            تقييم عام
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($rev->is_approved)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    <span>معتمد وظاهر</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-200 text-slate-700 font-bold text-[11px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                    <span>مخفي</span>
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-xs">
                            <div class="font-bold text-slate-800">{{ $rev->created_at->format('Y/m/d') }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $rev->created_at->format('H:i') }} • {{ $rev->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap">
                            <div class="admin-table-actions">
                                <form method="POST" action="{{ route('admin.reviews.toggle', $rev) }}">
                                    @csrf
                                    @if($rev->is_approved)
                                        <button type="submit" class="admin-action-btn admin-action-btn--outline admin-action-btn--sm" title="إخفاء هذا التقييم عن المتجر">
                                            <span class="material-symbols-outlined">visibility_off</span>
                                            <span>إخفاء</span>
                                        </button>
                                    @else
                                        <button type="submit" class="admin-action-btn admin-action-btn--primary admin-action-btn--sm" title="اعتماد ونشر التقييم">
                                            <span class="material-symbols-outlined">check_circle</span>
                                            <span>اعتماد التقييم</span>
                                        </button>
                                    @endif
                                </form>

                                <form method="POST" action="{{ route('admin.reviews.destroy', $rev) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم نهائياً؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action-btn admin-action-btn--danger admin-action-btn--sm" title="حذف التقييم نهائياً">
                                        <span class="material-symbols-outlined">delete</span>
                                        <span>حذف</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-4xl text-slate-300">rate_review</span>
                                <span class="font-bold text-slate-600">لا توجد تقييمات مطابقة للفلاتر المحددة</span>
                                <span class="text-xs text-slate-400">جرّب تغيير التصفية أو مسح كلمات البحث</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 4. Pagination --}}
    @if($reviews->hasPages())
        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection
