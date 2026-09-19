<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipSubscription;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $warningDays = (int) Setting::value('restaurant_expiry_warning_days', 7);
        $membershipWarning = (int) Setting::value('membership_expiry_warning_days', 3);

        $pendingOrders = Order::query()->where('status', 'pending_confirmation')->count();
        $todayOrders = Order::query()->whereDate('created_at', today())->count();
        $monthSales = Order::query()
            ->where('status', 'delivered')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $expiringRestaurants = Restaurant::query()
            ->where('is_active', true)
            ->where('verification_status', Restaurant::VERIFICATION_APPROVED)
            ->whereDate('expires_at', '>=', now())
            ->whereDate('expires_at', '<=', now()->addDays($warningDays))
            ->orderBy('expires_at')
            ->get();

        $expiredRestaurants = Restaurant::query()
            ->where('verification_status', Restaurant::VERIFICATION_APPROVED)
            ->whereDate('expires_at', '<', now())
            ->orderByDesc('expires_at')
            ->take(8)
            ->get();

        $expiringMemberships = MembershipSubscription::query()
            ->with(['user', 'membership'])
            ->where('status', 'approved')
            ->where('ends_at', '>=', now())
            ->where('ends_at', '<=', now()->addDays($membershipWarning))
            ->orderBy('ends_at')
            ->get();

        $pendingSubscriptions = MembershipSubscription::query()->where('status', 'pending')->count();
        $pendingRestaurants = Restaurant::query()->pendingVerification()->with('owner')->latest()->take(8)->get();
        $customers = User::query()->where('role', 'customer')->count();

        $latestOrders = Order::query()->with(['user', 'restaurant'])->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'pendingOrders',
            'todayOrders',
            'monthSales',
            'expiringRestaurants',
            'expiredRestaurants',
            'expiringMemberships',
            'pendingSubscriptions',
            'pendingRestaurants',
            'customers',
            'latestOrders',
            'warningDays',
        ));
    }
}
