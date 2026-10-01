@include('admin.orders.partials.order-board', [
    'ordersByStatus' => $ordersByStatus,
    'showRouteName' => 'partner.orders.show',
    'moveUrl' => url('/partner/orders/__ID__/move'),
    'allowedColumns' => 'preparing',
    'cardContext' => 'partner',
])
