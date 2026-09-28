@forelse($orders as $order)
    @include('courier.partials.order-card', ['order' => $order])
@empty
    <div class="courier-empty">
        <span class="material-symbols-outlined">{{ ($tab ?? 'mine') === 'done' ? 'check_circle' : 'delivery_dining' }}</span>
        <strong>{{ ($tab ?? 'mine') === 'done' ? 'لا تسليمات اليوم بعد' : 'لا طلبات مرسلة لك الآن' }}</strong>
        <p>{{ ($tab ?? 'mine') === 'done' ? 'بعد أول تسليم ستظهر الطلبات هنا.' : 'عندما تعينك الإدارة على طلب يظهر هنا فوراً وتلقائياً دون تحديث الصفحة.' }}</p>
    </div>
@endforelse
