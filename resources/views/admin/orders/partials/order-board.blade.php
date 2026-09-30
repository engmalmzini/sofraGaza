<div class="admin-board">
    <!-- 1. Pending Confirmation Column -->
    <div class="admin-board__col admin-board__col--pending">
        <div class="admin-board__head">
            <div class="admin-board__title">
                <span class="material-symbols-outlined">pending_actions</span>
                <span>بانتظار التأكيد</span>
            </div>
            <span class="admin-board__count">{{ $ordersByStatus['pending_confirmation']->count() }}</span>
        </div>
        <div class="admin-board__cards">
            @forelse($ordersByStatus['pending_confirmation'] as $order)
                @include('admin.orders.partials.order-card', ['order' => $order])
            @empty
                <div class="admin-board__empty">
                    <span class="material-symbols-outlined">done_all</span>
                    <span>لا توجد طلبات معلقة</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. Preparing / Confirmed Column -->
    <div class="admin-board__col admin-board__col--preparing">
        <div class="admin-board__head">
            <div class="admin-board__title">
                <span class="material-symbols-outlined">skillet</span>
                <span>قيد التحضير بالمطعم</span>
            </div>
            <span class="admin-board__count">{{ $ordersByStatus['preparing']->count() }}</span>
        </div>
        <div class="admin-board__cards">
            @forelse($ordersByStatus['preparing'] as $order)
                @include('admin.orders.partials.order-card', ['order' => $order])
            @empty
                <div class="admin-board__empty">
                    <span class="material-symbols-outlined">soup_kitchen</span>
                    <span>لا توجد وجبات قيد التجهيز</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 3. Delivering Column -->
    <div class="admin-board__col admin-board__col--delivering">
        <div class="admin-board__head">
            <div class="admin-board__title">
                <span class="material-symbols-outlined">two_wheeler</span>
                <span>مع المندوب للتوصيل</span>
            </div>
            <span class="admin-board__count">{{ $ordersByStatus['delivering']->count() }}</span>
        </div>
        <div class="admin-board__cards">
            @forelse($ordersByStatus['delivering'] as $order)
                @include('admin.orders.partials.order-card', ['order' => $order])
            @empty
                <div class="admin-board__empty">
                    <span class="material-symbols-outlined">moped</span>
                    <span>لا توجد طلبات قيد النقل</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 4. Delivered Column -->
    <div class="admin-board__col admin-board__col--delivered">
        <div class="admin-board__head">
            <div class="admin-board__title">
                <span class="material-symbols-outlined">task_alt</span>
                <span>تم التسليم بنجاح</span>
            </div>
            <span class="admin-board__count">{{ $ordersByStatus['delivered']->count() }}</span>
        </div>
        <div class="admin-board__cards">
            @forelse($ordersByStatus['delivered'] as $order)
                @include('admin.orders.partials.order-card', ['order' => $order])
            @empty
                <div class="admin-board__empty">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span>لا توجد طلبات مكتملة حديثاً</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
