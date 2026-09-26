<?php

namespace App\Http\Controllers\Partner;

use App\Models\RestaurantPlan;
use App\Services\RestaurantListingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SubscriptionController extends Controller
{
    public function __construct(private RestaurantListingService $listings) {}

    public function index(): View
    {
        $restaurant = $this->restaurant();

        return view('partner.subscription', [
            'restaurant' => $restaurant,
            'plans' => RestaurantPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'current' => $restaurant->activeListing(),
            'pending' => $restaurant->pendingListing(),
            'locked' => ! $restaurant->hasPaidAccess(),
        ]);
    }

    public function store(Request $request, RestaurantPlan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        $request->validate([
            'receipt' => ['required', 'image', 'max:4096'],
        ], [
            'receipt.required' => 'أرفق صورة إشعار الحوالة.',
        ]);

        try {
            $this->listings->request($this->restaurant(), $plan, $request->file('receipt'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('partner.subscription.index')
            ->with('success', 'تم إرسال إشعار الحوالة. يظهر للإدارة أنك دفعت، وبعد التأكيد تُفتح اللوحة.');
    }
}
