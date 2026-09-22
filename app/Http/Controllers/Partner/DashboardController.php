<?php

namespace App\Http\Controllers\Partner;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $restaurant = $this->restaurant()->loadCount(['menuItems', 'orders']);
        $pendingOrders = $restaurant->orders()->where('status', 'pending_confirmation')->count();
        $todayOrders = $restaurant->orders()->whereDate('created_at', today())->count();
        $latestOrders = $restaurant->orders()->with('user')->latest()->take(6)->get();
        $checklist = $restaurant->setupChecklist();
        $readyCount = collect($checklist)->where('done', true)->count();
        $categoryStats = $restaurant->menuItems()
            ->selectRaw('category, count(*) as items_count')
            ->groupBy('category')
            ->pluck('items_count', 'category');
        $menuByCategory = $restaurant->menuItems()->orderBy('name')->get()->groupBy('category');

        return view('partner.dashboard', [
            'restaurant' => $restaurant,
            'pendingOrders' => $pendingOrders,
            'todayOrders' => $todayOrders,
            'latestOrders' => $latestOrders,
            'checklist' => $checklist,
            'readyCount' => $readyCount,
            'categoryStats' => $categoryStats,
            'menuByCategory' => $menuByCategory,
        ]);
    }
}
