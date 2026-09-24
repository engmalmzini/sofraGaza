<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->string('tab')->toString();
        if (! in_array($tab, ['mine', 'done'], true)) {
            $tab = 'mine';
        }

        $mine = $user->deliveries()
            ->with(['restaurant', 'user', 'items'])
            ->whereIn('status', ['preparing', 'delivering'])
            ->get();
        $done = $user->deliveries()
            ->with(['restaurant', 'user', 'items'])
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->get();

        $orders = $tab === 'done' ? $done : $mine;

        return view('courier.dashboard', [
            'tab' => $tab,
            'orders' => $orders,
            'mineCount' => $mine->count(),
            'doneCount' => $done->count(),
            'courierRefresh' => $user->isCourierApproved() && $tab === 'mine',
        ]);
    }
}
