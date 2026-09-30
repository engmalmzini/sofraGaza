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

        $ordersByStatus = [
            'pending_confirmation' => $activeOrders->where('status', 'pending_confirmation'),
            'preparing' => $activeOrders->whereIn('status', ['confirmed', 'preparing']),
            'delivering' => $activeOrders->where('status', 'delivering'),
            'delivered' => $activeOrders->where('status', 'delivered')->take(12),
        ];

        return view('admin.orders.index', compact('orders', 'ordersByStatus'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'restaurant', 'items', 'membership', 'courier']);

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
        ]);
    }
}
