@php
    $cardContext = $cardContext ?? 'admin';
    $showRouteName = $showRouteName ?? match ($cardContext) {
        'partner' => 'partner.orders.show',
        'customer' => 'account.orders.show',
        default => 'admin.orders.show',
    };
    $moveUrl = $moveUrl ?? '';
    $allowedColumns = $allowedColumns ?? '';
    $readonly = $moveUrl === '';
    $columns = [
        'pending_confirmation' => [
            'title' => 'بانتظار التأكيد',
            'icon' => 'pending_actions',
            'empty_icon' => 'done_all',
            'empty' => 'لا توجد طلبات معلقة',
            'mod' => 'pending',
        ],
        'preparing' => [
            'title' => 'قيد التحضير بالمطعم',
            'icon' => 'skillet',
            'empty_icon' => 'soup_kitchen',
            'empty' => 'لا توجد وجبات قيد التجهيز',
            'mod' => 'preparing',
        ],
        'delivering' => [
            'title' => 'مع المندوب للتوصيل',
            'icon' => 'two_wheeler',
            'empty_icon' => 'moped',
            'empty' => 'لا توجد طلبات قيد النقل',
            'mod' => 'delivering',
        ],
        'delivered' => [
            'title' => 'تم التسليم بنجاح',
            'icon' => 'task_alt',
            'empty_icon' => 'check_circle',
            'empty' => 'لا توجد طلبات مكتملة حديثاً',
            'mod' => 'delivered',
        ],
    ];
@endphp

<div
    class="admin-board {{ $readonly ? 'admin-board--readonly' : 'admin-board--live' }}"
    data-order-board
    @unless($readonly)
        data-move-url="{{ $moveUrl }}"
        data-allowed-columns="{{ $allowedColumns }}"
    @endunless
>
    @foreach($columns as $columnKey => $column)
        @php $columnOrders = collect($ordersByStatus[$columnKey] ?? []); @endphp
        <div
            class="admin-board__col admin-board__col--{{ $column['mod'] }}"
            data-board-column="{{ $columnKey }}"
        >
            <div class="admin-board__head">
                <div class="admin-board__title">
                    <span class="material-symbols-outlined">{{ $column['icon'] }}</span>
                    <span>{{ $column['title'] }}</span>
                </div>
                <span class="admin-board__count" data-board-count>{{ $columnOrders->count() }}</span>
            </div>
            <div class="admin-board__cards" data-board-cards>
                @forelse($columnOrders as $order)
                    @include('admin.orders.partials.order-card', [
                        'order' => $order,
                        'showRouteName' => $showRouteName,
                        'cardContext' => $cardContext,
                        'readonly' => $readonly,
                    ])
                @empty
                    <div class="admin-board__empty" data-board-empty>
                        <span class="material-symbols-outlined">{{ $column['empty_icon'] }}</span>
                        <span>{{ $column['empty'] }}</span>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
