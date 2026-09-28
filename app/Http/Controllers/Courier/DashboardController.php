<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
            ->latest('id')
            ->get();
        $done = $user->deliveries()
            ->with(['restaurant', 'user', 'items'])
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->latest('delivered_at')
            ->get();

        $orders = $tab === 'done' ? $done : $mine;

        return view('courier.dashboard', [
            'tab' => $tab,
            'orders' => $orders,
            'mineCount' => $mine->count(),
            'doneCount' => $done->count(),
            'courierRefresh' => false, // replaced by smooth real-time polling
        ]);
    }

    public function live(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isCourierApproved()) {
            return response()->json(['active_count' => 0, 'has_new' => false, 'html' => '']);
        }

        $hasLastId = $request->has('last_id');
        $lastId = (int) $request->input('last_id', 0);
        $tab = $request->input('tab', 'mine');

        if ($tab === 'done') {
            $orders = $user->deliveries()
                ->with(['restaurant', 'user', 'items'])
                ->where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->latest('delivered_at')
                ->get();
        } else {
            $orders = $user->deliveries()
                ->with(['restaurant', 'user', 'items'])
                ->whereIn('status', ['preparing', 'delivering'])
                ->latest('id')
                ->get();
        }

        $latest = $orders->first();
        $latestId = $latest?->id ?? 0;
        $hasNew = $hasLastId && $latestId > $lastId;

        $html = view('courier.partials.live-cards', ['orders' => $orders, 'tab' => $tab])->render();

        return response()->json([
            'latest_id' => $latestId,
            'active_count' => $orders->count(),
            'has_new' => $hasNew,
            'latest_order' => $latest ? [
                'id' => $latest->id,
                'restaurant' => $latest->restaurant?->name,
                'total' => (float) $latest->total,
                'status' => $latest->status,
                'status_label' => $latest->statusLabel(),
            ] : null,
            'html' => $html,
        ]);
    }
}
