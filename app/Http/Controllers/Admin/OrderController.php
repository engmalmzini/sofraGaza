<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
        $query = Order::query()->with(['user', 'restaurant', 'courier'])->latest();

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

        $orders = $query->paginate(20)->withQueryString();

        // Also fetch active orders for board view
        $activeOrders = Order::query()
            ->with(['user', 'restaurant', 'courier'])
            ->latest()
            ->take(60)
            ->get();

        $ordersByStatus = Order::groupForBoard($activeOrders);
        $ordersByStatus['delivered'] = $ordersByStatus['delivered']->take(12);

        return view('admin.orders.index', compact('orders', 'ordersByStatus'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'restaurant', 'items', 'membership', 'courier', 'groupOrder.members.user']);

        return view('admin.orders.show', compact('order'));
    }

    public function receipt(Order $order)
    {
        return $order->receiptResponse();
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->orders->changeStatus($order, $request->status, $request->rejection_reason);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }

    public function move(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'column' => ['required', 'string', 'in:pending_confirmation,preparing,delivering,delivered'],
        ]);

        try {
            $this->orders->moveToColumn($order, $data['column']);
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

    public function live(Request $request): JsonResponse
    {
        $hasLastId = $request->has('last_id');
        $lastId = (int) $request->input('last_id', 0);
        $latest = Order::query()->latest('id')->first();
        $latestId = $latest?->id ?? 0;
        $pendingCount = Order::query()->where('status', 'pending_confirmation')->count();
        $activeCount = Order::query()->whereIn('status', ['pending_confirmation', 'confirmed', 'preparing', 'delivering'])->count();

        $hasNew = $hasLastId && $latestId > $lastId;

        $recentOrders = Order::query()
            ->with(['user', 'restaurant'])
            ->latest('id')
            ->take(20)
            ->get();

        $html = view('admin.orders.partials.order-rows', ['orders' => $recentOrders])->render();
        $boardOrders = Order::query()->with(['user', 'restaurant', 'courier'])->latest()->take(60)->get();
        $ordersByStatus = Order::groupForBoard($boardOrders);
        $ordersByStatus['delivered'] = $ordersByStatus['delivered']->take(12);
        $boardHtml = view('admin.orders.partials.order-board', [
            'ordersByStatus' => $ordersByStatus,
            'showRouteName' => 'admin.orders.show',
            'moveUrl' => url('/admin/orders/__ID__/move'),
            'allowedColumns' => 'preparing,delivering,delivered',
            'cardContext' => 'admin',
        ])->render();
        $boardSignature = $boardOrders->map(fn (Order $order) => $order->id.':'.$order->status)->implode('|');

        return response()->json([
            'latest_id' => $latestId,
            'pending_count' => $pendingCount,
            'active_count' => $activeCount,
            'has_new' => $hasNew,
            'latest_order' => $latest ? [
                'id' => $latest->id,
                'customer' => $latest->user?->name,
                'restaurant' => $latest->restaurant?->name,
                'total' => (float) $latest->total,
                'status' => $latest->status,
                'status_label' => $latest->statusLabel(),
            ] : null,
            'html' => $html,
            'board_html' => $boardHtml,
            'board_signature' => $boardSignature,
        ]);
    }
}
