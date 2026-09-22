<?php

namespace App\Http\Controllers\Partner;

use App\Models\Order;
use App\Services\OrderService;
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
        $query = $restaurant->orders()->with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('id', $q)
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$q}%"));
            });
        }

        return view('partner.orders.index', [
            'restaurant' => $restaurant,
            'orders' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorizeOrder($order);
        $order->load(['user', 'restaurant', 'items', 'membership']);

        return view('partner.orders.show', compact('order'));
    }

    public function receipt(Order $order)
    {
        $this->authorizeOrder($order);

        return $order->receiptResponse();
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

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

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->restaurant_id === $this->restaurant()->id, 404);
    }
}
