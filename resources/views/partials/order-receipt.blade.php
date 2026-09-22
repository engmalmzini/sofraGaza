@if($order->type !== 'redemption')
<section class="admin-card sg-receipt-card">
    <h2>إشعار الحوالة</h2>
    @if($order->hasReceiptFile())
        <a href="{{ $receiptRoute }}" target="_blank" rel="noopener" class="sg-receipt-frame">
            <img src="{{ $order->receiptDataUri() }}" alt="صورة إشعار الحوالة لطلب #{{ $order->id }}">
        </a>
        <a href="{{ $receiptRoute }}" target="_blank" rel="noopener" class="sg-receipt-open">
            فتح الصورة بحجم كامل
            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
        </a>
    @elseif($order->transfer_receipt_path)
        <p class="sg-receipt-empty">تم رفع الإشعار لكن تعذر عرض الملف من التخزين. أعد ربط مجلد التخزين أو اطلب من الزبون إعادة الإرسال.</p>
        <a href="{{ $receiptRoute }}" class="sg-receipt-open" target="_blank" rel="noopener">محاولة فتح الملف</a>
    @else
        <p class="sg-receipt-empty">لا يوجد إشعار حوالة مرفوع على هذا الطلب.</p>
    @endif
</section>
@endif
