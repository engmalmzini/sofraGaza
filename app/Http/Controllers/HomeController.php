<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Restaurant;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $restaurants = Restaurant::query()
            ->visible()
            ->inDeliveryArea()
            ->withCount('menuItems')
            ->latest()
            ->take(6)
            ->get();

        $restaurantCount = Restaurant::query()->visible()->inDeliveryArea()->count();
        $memberships = Membership::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('home', [
            'restaurants' => $restaurants,
            'restaurantCount' => $restaurantCount,
            'memberships' => $memberships,
            'categories' => config('brand.categories'),
            'rewards' => config('brand.rewards'),
            'heroImage' => config('brand.hero'),
        ]);
    }
}
