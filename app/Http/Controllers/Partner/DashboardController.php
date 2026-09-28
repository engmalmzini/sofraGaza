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
        $listing = $restaurant->activeListing();

        $activeOrders = $restaurant->orders()
            ->with(['user', 'items', 'courier'])
            ->whereIn('status', ['pending_confirmation', 'confirmed', 'preparing', 'delivering'])
            ->latest('id')
            ->get();
        $activeOrdersCount = $activeOrders->count();

        return view('partner.dashboard', [
            'restaurant' => $restaurant,
            'checklist' => $checklist,
            'readyCount' => $readyCount,
            'categoryStats' => $categoryStats,
            'menuByCategory' => $menuByCategory,
            'listing' => $listing,
            'activeOrders' => $activeOrders,
            'activeOrdersCount' => $activeOrdersCount,
        ]);
    }
}
