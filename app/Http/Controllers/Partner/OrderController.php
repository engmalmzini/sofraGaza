<?php

namespace App\Http\Controllers\Partner;

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

        return view('partner.orders.index', compact('restaurant', 'orders', 'activeCount'));
    }

    public function show(Order $order): View
    {
        $restaurant = $this->restaurant();
        abort_unless($order->restaurant_id === $restaurant->id, 403, 'غير مصرح بالوصول لهذا الطلب.');

        $order->load(['user', 'items', 'courier', 'membership']);

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

        $activeCount = $activeOrders->count();
        $hasNew = $hasLastId && $latestId > $lastId;

        $html = view('partner.orders.partials.order-cards', [
            'orders' => $activeOrders,
            'restaurant' => $restaurant,
        ])->render();

        return response()->json([
            'latest_id' => $latestId,
            'active_count' => $activeCount,
            'has_new' => $hasNew,
            'latest_order' => $latest ? [
                'id' => $latest->id,
                'customer' => $latest->user?->name,
                'total' => (float) $latest->total,
                'status' => $latest->status,
                'status_label' => $latest->statusLabel(),
                'items_count' => $latest->items->count(),
            ] : null,
            'html' => $html,
        ]);
    }
}
