<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierPayout;
use App\Models\MembershipSubscription;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTopup;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $membershipWarning = (int) Setting::value('membership_expiry_warning_days', 3);

        $pendingOrders = Order::query()->where('status', 'pending_confirmation')->count();
        $todayOrders = Order::query()->whereDate('created_at', today())->count();
        $todaySales = Order::query()
            ->where('status', 'delivered')
            ->whereDate('created_at', today())
            ->sum('total');
        $monthSales = Order::query()
            ->where('status', 'delivered')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $activeRestaurants = Restaurant::query()
            ->where('is_active', true)
            ->where('verification_status', Restaurant::VERIFICATION_APPROVED)
            ->count();
        $activeCouriers = User::query()->where('role', 'courier')->where('is_active', true)->count();
        $customers = User::query()->where('role', 'customer')->count();

        $expiringMemberships = MembershipSubscription::query()
            ->with(['user', 'membership'])
            ->where('status', 'approved')
            ->where('ends_at', '>=', now())
            ->where('ends_at', '<=', now()->addDays($membershipWarning))
            ->orderBy('ends_at')
            ->get();

        $pendingSubscriptions = MembershipSubscription::query()->where('status', 'pending')->count();
        $pendingSubscriptionsList = MembershipSubscription::query()
            ->with(['user', 'membership'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $pendingRestaurants = Restaurant::query()->pendingVerification()->with('owner')->latest()->take(6)->get();

        $pendingPayouts = CourierPayout::query()
            ->where('status', CourierPayout::STATUS_PENDING)
            ->with('user')
            ->latest()
            ->take(6)
            ->get();
        $pendingPayoutsCount = CourierPayout::query()->where('status', CourierPayout::STATUS_PENDING)->count();

        $pendingTopups = WalletTopup::query()
            ->where('status', WalletTopup::STATUS_PENDING)
            ->with('user')
            ->latest()
            ->take(6)
            ->get();
        $pendingTopupsCount = WalletTopup::query()->where('status', WalletTopup::STATUS_PENDING)->count();

        $activeOrders = Order::query()
            ->with(['user', 'restaurant', 'courier'])
            ->latest()
            ->take(50)
            ->get();

        $ordersByStatus = Order::groupForBoard($activeOrders);
        $ordersByStatus['delivered'] = $ordersByStatus['delivered']->take(8);

        $latestOrders = $activeOrders->take(10);

        return view('admin.dashboard', compact(
            'pendingOrders',
            'todayOrders',
            'todaySales',
            'monthSales',
            'activeRestaurants',
            'activeCouriers',
            'expiringMemberships',
            'pendingSubscriptions',
            'pendingSubscriptionsList',
            'pendingRestaurants',
            'pendingPayouts',
            'pendingPayoutsCount',
            'pendingTopups',
            'pendingTopupsCount',
            'customers',
            'latestOrders',
            'ordersByStatus',
            'membershipWarning',
        ));
    }
}
