<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function show(Order $order): View
    {
        abort_unless($order->courier_id === auth()->id(), 404);
        abort_unless(auth()->user()->isCourierApproved(), 403);
        $order->load(['restaurant', 'user', 'items']);

        return view('courier.orders.show', compact('order'));
    }

    public function complete(Order $order): RedirectResponse
    {
        abort_unless($order->courier_id === auth()->id(), 404);

        try {
            $this->orders->completeCourierDelivery($order, auth()->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('courier.dashboard', ['tab' => 'done'])
            ->with('success', 'تم تسليم الطلب.');
    }
}
