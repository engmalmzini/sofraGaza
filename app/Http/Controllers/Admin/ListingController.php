<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantSubscription;
use App\Services\RestaurantListingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ListingController extends Controller
{
    public function __construct(private RestaurantListingService $listings) {}

    public function index(Request $request): View
    {
        $query = RestaurantSubscription::query()->with(['restaurant.owner', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->whereHas('restaurant', fn ($restaurant) => $restaurant->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('restaurant.owner', fn ($owner) => $owner->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
            });
        }

        $listings = $query->paginate(20)->withQueryString();

        return view('admin.listings.index', compact('listings'));
    }

    public function show(RestaurantSubscription $listing): View
    {
        $listing->load(['restaurant.owner', 'plan']);

        return view('admin.listings.show', compact('listing'));
    }

    public function receipt(RestaurantSubscription $listing)
    {
        return $listing->receiptResponse();
    }

    public function approve(RestaurantSubscription $listing): RedirectResponse
    {
        try {
            $message = $this->listings->approve($listing);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    public function reject(Request $request, RestaurantSubscription $listing): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:8', 'max:500'],
        ], [
            'rejection_reason.required' => 'اكتب سبب الرفض.',
        ]);

        try {
            $message = $this->listings->reject($listing, $request->rejection_reason);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }
}
