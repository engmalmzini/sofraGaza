<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.restaurants.index');
    }

    public function show(RestaurantSubscription $listing): RedirectResponse
    {
        return redirect()->route('admin.restaurants.show', $listing->restaurant_id);
    }

    public function receipt(RestaurantSubscription $listing): RedirectResponse
    {
        return redirect()->route('admin.restaurants.show', $listing->restaurant_id);
    }

    public function approve(RestaurantSubscription $listing): RedirectResponse
    {
        return redirect()->route('admin.restaurants.show', $listing->restaurant_id);
    }

    public function reject(Request $request, RestaurantSubscription $listing): RedirectResponse
    {
        return redirect()->route('admin.restaurants.show', $listing->restaurant_id);
    }
}
