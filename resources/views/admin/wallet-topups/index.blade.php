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
    {{-- Topups Table --}}
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الزبون</th>
                    <th>المبلغ المطلوب</th>
                    <th>طريقة الدفع</th>
                    <th>إشعار الحوالة</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th class="text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topups as $topup)
                    <tr>
                        <td class="font-mono font-bold text-slate-500">#{{ $topup->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $topup->user) }}" class="font-bold text-slate-900 hover:text-primary">
                                {{ $topup->user->name }}
                            </a>
                            <div class="text-xs text-slate-500 font-mono" dir="ltr">{{ $topup->user->phone }}</div>
                        </td>
                        <td>
                            <span class="font-mono font-bold text-sm text-emerald-700">{{ number_format($topup->amount, 2) }}</span>
                            <span class="ils">₪</span>
                        </td>
                        <td class="font-semibold text-slate-800">{{ $topup->paymentMethodLabel() }}</td>
                        <td>
                            @if($topup->receiptUrl())
                                <a href="{{ route('admin.wallet-topups.receipt', $topup) }}" target="_blank" class="admin-action-btn admin-action-btn--outline admin-action-btn--sm">
                                    <span class="material-symbols-outlined">receipt_long</span>
                                    <span>عرض الإشعار</span>
                                </a>
                            @else
                                <span class="text-xs text-slate-400">لا يوجد إشعار</span>
                            @endif
                        </td>
                        <td>
                            @if($topup->isPending())
                                <span class="inline-flex px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-bold text-[11px]">
                                    قيد المراجعة
                                </span>
                            @elseif($topup->isApproved())
                                <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px]">
                                    تمت الإضافة
                                </span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full bg-rose-100 text-rose-900 font-bold text-[11px]">
                                    مرفوض
                                </span>
                                @if($topup->rejection_reason)
                                    <div class="text-[10px] text-rose-700 mt-0.5 max-w-[150px] truncate" title="{{ $topup->rejection_reason }}">
                                        {{ $topup->rejection_reason }}
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td class="text-slate-500 text-xs whitespace-nowrap">{{ $topup->created_at->format('Y-m-d H:i') }}</td>
                        <td class="whitespace-nowrap">
                            @if($topup->isPending())
                                <div class="admin-table-actions">
                                    <form method="POST" action="{{ route('admin.wallet-topups.approve', $topup) }}" onsubmit="return confirm('تأكيد استلام الحوالة وإضافة {{ $topup->amount }} ₪ لرصيد {{ $topup->user->name }}؟')">
                                        @csrf
                                        <button type="submit" class="admin-action-btn admin-action-btn--primary">
                                            <span class="material-symbols-outlined">check_circle</span>
                                            <span>تأكيد الشحن</span>
                                        </button>
                                    </form>

                                    <button type="button" 
                                            class="admin-action-btn admin-action-btn--danger"
                                            onclick="openRejectModal({{ $topup->id }}, '{{ $topup->user->name }}', {{ $topup->amount }})">
                                        <span class="material-symbols-outlined">cancel</span>
                                        <span>رفض</span>
                                    </button>
                                </div>
                            @else
                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $topup->reviewer ? 'راجعها: '.$topup->reviewer->name : 'مكتمل' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">
                            لا توجد طلبات شحن مطابقة.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($topups->hasPages())
        <div class="mt-4">
            {{ $topups->links() }}
        </div>
    @endif
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
                <button type="button" onclick="closeRejectModal()" class="admin-action-btn admin-action-btn--outline">إلغاء</button>
                <button type="submit" class="admin-action-btn admin-action-btn--danger">تأكيد الرفض</button>
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
