@extends('layouts.admin')

@section('kicker', 'المحفظة والرصيد')
@section('title', 'طلبات شحن رصيد المحفظة')

@section('content')
<div class="space-y-6">
    {{-- Status Tabs --}}
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.wallet-topups.index') }}" 
           class="admin-btn text-xs {{ ! $status ? 'admin-btn--primary' : 'admin-btn--ghost' }}">
            الكل ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.wallet-topups.index', ['status' => 'pending']) }}" 
           class="admin-btn text-xs {{ $status === 'pending' ? 'admin-btn--primary' : 'admin-btn--ghost' }}">
            قيد المراجعة ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('admin.wallet-topups.index', ['status' => 'approved']) }}" 
           class="admin-btn text-xs {{ $status === 'approved' ? 'admin-btn--primary' : 'admin-btn--ghost' }}">
            تم الشحن ({{ $counts['approved'] }})
        </a>
        <a href="{{ route('admin.wallet-topups.index', ['status' => 'rejected']) }}" 
           class="admin-btn text-xs {{ $status === 'rejected' ? 'admin-btn--primary' : 'admin-btn--ghost' }}">
            مرفوضة ({{ $counts['rejected'] }})
        </a>
    </div>

    {{-- Topups Table --}}
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant border-b border-slate-200">
                        <th class="py-3 px-4 font-bold">#</th>
                        <th class="py-3 px-4 font-bold">الزبون</th>
                        <th class="py-3 px-4 font-bold">المبلغ المطلوب</th>
                        <th class="py-3 px-4 font-bold">طريقة الدفع</th>
                        <th class="py-3 px-4 font-bold">إشعار الحوالة</th>
                        <th class="py-3 px-4 font-bold">الحالة</th>
                        <th class="py-3 px-4 font-bold">التاريخ</th>
                        <th class="py-3 px-4 font-bold">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($topups as $topup)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold">{{ $topup->id }}</td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.users.show', $topup->user) }}" class="font-bold text-on-surface hover:text-primary">
                                    {{ $topup->user->name }}
                                </a>
                                <div class="text-[11px] text-on-surface-variant font-mono">{{ $topup->user->phone }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-sm text-emerald-700">
                                {{ number_format($topup->amount, 2) }} ₪
                            </td>
                            <td class="py-3.5 px-4 text-on-surface">{{ $topup->paymentMethodLabel() }}</td>
                            <td class="py-3.5 px-4">
                                @if($topup->receiptUrl())
                                    <a href="{{ route('admin.wallet-topups.receipt', $topup) }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-primary hover:underline">
                                        <span class="material-symbols-outlined text-[15px]">receipt</span>
                                        <span>عرض الإشعار</span>
                                    </a>
                                @else
                                    <span class="text-on-surface-variant">لا يوجد</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($topup->isPending())
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 font-semibold text-[11px]">
                                        قيد المراجعة
                                    </span>
                                @elseif($topup->isApproved())
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-semibold text-[11px]">
                                        تمت الإضافة
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-rose-100 text-rose-900 font-semibold text-[11px]">
                                        مرفوض
                                    </span>
                                    @if($topup->rejection_reason)
                                        <div class="text-[10px] text-rose-700 mt-0.5 max-w-[150px] truncate" title="{{ $topup->rejection_reason }}">
                                            {{ $topup->rejection_reason }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-on-surface-variant whitespace-nowrap">{{ $topup->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($topup->isPending())
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('admin.wallet-topups.approve', $topup) }}" onsubmit="return confirm('تأكيد استلام الحوالة وإضافة {{ $topup->amount }} ₪ لرصيد {{ $topup->user->name }}؟')">
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn--primary !py-1 !px-2.5 !text-xs">
                                                تأكيد وإضافة الرصيد
                                            </button>
                                        </form>

                                        <button type="button" 
                                                class="admin-btn admin-btn--err !py-1 !px-2.5 !text-xs"
                                                onclick="openRejectModal({{ $topup->id }}, '{{ $topup->user->name }}', {{ $topup->amount }})">
                                            رفض
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[11px] text-on-surface-variant">
                                        {{ $topup->reviewer ? 'راجعها: '.$topup->reviewer->name : 'مكتمل' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-on-surface-variant">
                                لا توجد طلبات شحن مطابقة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($topups->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $topups->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Reject Modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl text-stone-900">
        <h3 class="font-bold text-base text-stone-900">رفض طلب شحن الرصيد</h3>
        <p id="reject-modal-desc" class="text-xs text-stone-500"></p>

        <form id="reject-form" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold mb-1">سبب الرفض (يظهر للزبون)</label>
                <textarea name="rejection_reason" rows="3" required placeholder="مثال: إشعار الحوالة غير واضح، أو لم تصل الحوالة لحساب البنك..." class="w-full text-xs rounded-xl border border-slate-200 p-2.5"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeRejectModal()" class="admin-btn admin-btn--ghost text-xs">إلغاء</button>
                <button type="submit" class="admin-btn admin-btn--err text-xs">تأكيد الرفض</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id, userName, amount) {
    document.getElementById('reject-form').action = `/admin/wallet-topups/${id}/reject`;
    document.getElementById('reject-modal-desc').textContent = `أنت بصدد رفض طلب شحن بقيمة ${amount} ₪ للزبون ${userName}.`;
    document.getElementById('reject-modal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
}
</script>
@endsection
