<?php

namespace App\Http\Controllers\Partner;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $restaurant = $this->restaurant()->loadCount('menuItems');
        $checklist = $restaurant->setupChecklist();
        $readyCount = collect($checklist)->where('done', true)->count();
        $categoryStats = $restaurant->menuItems()
            ->selectRaw('category, count(*) as items_count')
            ->groupBy('category')
            ->pluck('items_count', 'category');
        $menuByCategory = $restaurant->menuItems()->orderBy('name')->get()->groupBy('category');

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
        $activeOrdersCount = $activeOrders->count();
        $ordersByStatus = \App\Models\Order::groupForBoard($activeOrders->concat($deliveredOrders));

        return view('partner.dashboard', [
            'restaurant' => $restaurant,
            'checklist' => $checklist,
            'readyCount' => $readyCount,
            'categoryStats' => $categoryStats,
            'menuByCategory' => $menuByCategory,
            'activeOrders' => $activeOrders,
            'activeOrdersCount' => $activeOrdersCount,
            'ordersByStatus' => $ordersByStatus,
        ]);
    }
}
