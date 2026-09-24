<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Restaurant;
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
            ->take(6)
            ->get();

        $restaurantCount = Restaurant::query()->visible()->count();
        $memberships = Membership::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('home', [
            'restaurants' => $restaurants,
            'restaurantCount' => $restaurantCount,
            'memberships' => $memberships,
            'categories' => Restaurant::homeCategories(),
            'rewards' => $this->points->featuredRewards(3),
            'heroImage' => config('brand.hero'),
            'pointsEarnLabel' => $this->points->earnRateLabel(),
            'pointsRedeemLabel' => $this->points->redeemRateLabel(),
        ]);
    }
}
