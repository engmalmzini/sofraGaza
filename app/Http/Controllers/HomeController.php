<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Restaurant;
use App\Support\HomeContent;
use App\Models\HomePartner;
use App\Services\PointsService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private PointsService $points) {}

    public function index(): View
    {
        $restaurants = Restaurant::query()
            ->visible()
            ->inDeliveryArea()
            ->withCount('menuItems')
            ->latest()
            ->take(24)
            ->get();

        $restaurantCount = Restaurant::query()->visible()->count();
        $memberships = Membership::query()->where('is_active', true)->orderBy('sort_order')->get();
        $latestDishes = \App\Models\MenuItem::query()
            ->where('is_available', true)
            ->whereHas('restaurant', fn($q) => $q->visible())
            ->with('restaurant')
            ->latest()
            ->take(8)
            ->get();

        return view('home', [
            'restaurants' => $restaurants,
            'restaurantCount' => $restaurantCount,
            'latestDishes' => $latestDishes,
            'memberships' => $memberships,
            'categories' => Restaurant::homeCategories(),
            'rewards' => $this->points->featuredRewards(3),
            'heroImage' => HomeContent::imageUrl('hero_image'),
            'joinImage' => HomeContent::imageUrl('join_image'),
            'home' => HomeContent::values(),
            'partners' => HomePartner::query()->active()->ordered()->get(),
            'pointsEarnLabel' => $this->points->earnRateLabel(),
            'pointsRedeemLabel' => $this->points->redeemRateLabel(),
        ]);
    }
}
