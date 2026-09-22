<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->toString();
        if (! in_array($tab, ['ready', 'mine', 'done'], true)) {
            $tab = 'ready';
        }

        $user = $request->user();
        $ready = Order::query()
            ->with(['restaurant', 'user', 'items'])
            ->whereNull('courier_id')
            ->whereIn('status', ['preparing', 'delivering'])
            ->latest()
            ->get();
        $mine = $user->deliveries()
            ->with(['restaurant', 'user', 'items'])
            ->where('status', 'delivering')
            ->get();
        $done = $user->deliveries()
            ->with(['restaurant', 'user', 'items'])
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->get();

        $orders = match ($tab) {
            'mine' => $mine,
            'done' => $done,
            default => $ready,
        };

        return view('courier.dashboard', [
            'tab' => $tab,
            'orders' => $orders,
            'readyCount' => $ready->count(),
            'mineCount' => $mine->count(),
            'doneCount' => $done->count(),
            'courierRefresh' => $tab === 'ready',
        ]);
    }
}
