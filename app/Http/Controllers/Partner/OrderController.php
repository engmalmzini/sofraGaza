<?php

namespace App\Http\Controllers\Partner;

use App\Models\Order;
use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        $restaurant = $this->restaurant();
        $query = $restaurant->orders()->with(['user', 'items', 'courier'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('id', $q)
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        $orders = $query->paginate(15)->withQueryString();
        $activeCount = $restaurant->orders()->whereIn('status', ['pending_confirmation', 'confirmed', 'preparing', 'delivering'])->count();
        $boardOrders = $restaurant->orders()
            ->with(['user', 'items', 'courier'])
            ->latest()
            ->take(60)
            ->get();
        $ordersByStatus = Order::groupForBoard($boardOrders);

        return view('partner.orders.index', compact('restaurant', 'orders', 'activeCount', 'ordersByStatus'));
    }

    public function show(Order $order): View
    {
        $restaurant = $this->restaurant();
        abort_unless($order->restaurant_id === $restaurant->id, 403, 'غير مصرح بالوصول لهذا الطلب.');

        $order->load(['user', 'items', 'courier', 'membership', 'groupOrder.members.user']);

        return view('partner.orders.show', compact('restaurant', 'order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $restaurant = $this->restaurant();
        abort_unless($order->restaurant_id === $restaurant->id, 403);

        $request->validate([
            'status' => ['required', 'string', 'in:preparing,confirmed'],
        ]);

        try {
            $this->orders->changeStatus($order, $request->status);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }

    public function move(Request $request, Order $order): JsonResponse
    {
        $restaurant = $this->restaurant();
        abort_unless($order->restaurant_id === $restaurant->id, 403);

        $data = $request->validate([
            'column' => ['required', 'string', 'in:pending_confirmation,preparing,delivering,delivered'],
        ]);

        try {
            $this->orders->moveToColumn($order, $data['column'], ['preparing']);
        } catch (RuntimeException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        $order->refresh();

        return response()->json([
            'ok' => true,
            'status' => $order->status,
            'column' => $order->boardColumn(),
            'status_label' => $order->statusLabel(),
        ]);
    }

    public function markPrepared(Order $order, NotificationService $notifications): RedirectResponse
    {
        $restaurant = $this->restaurant();
        abort_unless($order->restaurant_id === $restaurant->id, 403);

        if (! in_array($order->status, ['confirmed', 'preparing'], true)) {
            return back()->with('error', 'لا يمكن تعديل حالة هذا الطلب حالياً.');
        }

        $order->update([
            'status' => 'preparing',
            'prepared_at' => now(),
        ]);

        $restaurantName = $restaurant->name;

        // Notify Admins that the order is ready for courier delivery
        $notifications->notifyAdmins(
            "الطلب #{$order->id} جاهز للاستلام بالمطعم 🍳",
            "أنهى مطعم \"{$restaurantName}\" تحضير الطلب #{$order->id} بالكامل وهو جاهز الآن في المطبخ لتسليمه لمندوب التوصيل.",
            route('admin.delivery.index', ['tab' => 'waiting'])
        );

        // If a courier is already assigned, notify the courier directly
        if ($order->courier) {
            $notifications->notify(
                $order->courier,
                "الطلب #{$order->id} جاهز للاستلام 🍳",
                "أنهى مطعم \"{$restaurantName}\" تحضير الطلب #{$order->id}. تفضل بالتوجه للمطعم لاستلام الوجبات.",
                route('courier.orders.show', $order)
            );
        }

        return back()->with('success', 'تم تأكيد تجهيز الطلب بنجاح! تم إشعار الإدارة والمندوب ليتوجه للمطعم واستلام الوجبات.');
    }

    public function live(Request $request): JsonResponse
    {
        $restaurant = $this->restaurant();
        $hasLastId = $request->has('last_id');
        $lastId = (int) $request->input('last_id', 0);

        $latest = $restaurant->orders()->latest('id')->first();
        $latestId = $latest?->id ?? 0;

        $activeOrders = $restaurant->orders()
            ->with(['user', 'items', 'courier'])
            ->whereIn('status', ['pending_confirmation', 'confirmed', 'preparing', 'delivering'])
            ->latest('id')
            ->get();

        $deliveredOrders = $restaurant->orders()
            ->with(['user', 'items', 'courier'])
            ->where('status', 'delivered')
            ->latest('id')
            ->take(12)
            ->get();

        $boardOrders = $activeOrders->concat($deliveredOrders);
        $activeCount = $activeOrders->count();
        $hasNew = $hasLastId && $latestId > $lastId;

        $html = view('partner.orders.partials.order-board', [
            'ordersByStatus' => Order::groupForBoard($boardOrders),
            'restaurant' => $restaurant,
        ])->render();
        $boardSignature = $boardOrders->map(fn (Order $order) => $order->id.':'.$order->status)->implode('|');

        return response()->json([
            'latest_id' => $latestId,
            'active_count' => $activeCount,
            'has_new' => $hasNew,
            'latest_order' => $latest ? [
                'id' => $latest->id,
                'customer' => $latest->user?->name,
                'total' => (float) $latest->foodTotal(),
                'status' => $latest->status,
                'status_label' => $latest->statusLabel(),
                'items_count' => $latest->items->count(),
            ] : null,
            'html' => $html,
            'board_html' => $html,
            'board_signature' => $boardSignature,
        ]);
    }
}
