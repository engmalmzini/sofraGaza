<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAppNotifications;
use App\Models\Address;
use App\Models\AppNotification;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    use ManagesAppNotifications;

    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isCourier()) {
            return redirect()->route('courier.dashboard');
        }

        return view('account.show', [
            'user' => $user,
            'membership' => $user->activeMembership(),
            'subscription' => $user->activeSubscription(),
            'recentOrders' => $user->orders()->with('restaurant')->take(5)->get(),
        ]);
    }

    public function orders(): View
    {
        $orders = auth()->user()->orders()->with('restaurant')->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function showOrder(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $order->load('items', 'restaurant');

        return view('account.order-show', compact('order'));
    }

    public function cancelOrder(Order $order): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);

        try {
            app(\App\Services\OrderService::class)->changeStatus($order, 'cancelled');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم إلغاء الطلب.');
    }

    public function points(): View
    {
        $transactions = auth()->user()->pointTransactions()->paginate(15);

        return view('account.points', compact('transactions'));
    }

    public function addresses(): View
    {
        return view('account.addresses', [
            'addresses' => auth()->user()->addresses,
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'details' => ['required', 'string', 'min:10'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $data['is_default'] = $user->addresses()->count() === 0;
        $user->addresses()->create($data);

        return back()->with('success', 'تم حفظ العنوان.');
    }

    public function destroyAddress(Address $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);
        $address->delete();

        return back()->with('success', 'تم حذف العنوان.');
    }

    public function notifications(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.notifications.index');
        }

        if ($user->isRestaurantOwner() && $user->ownedRestaurant) {
            return redirect()->route('partner.notifications.index');
        }

        if ($user->isCourier()) {
            return redirect()->route('courier.notifications.index');
        }

        return $this->notificationsInbox('account.notifications');
    }

    public function markNotifications(): RedirectResponse
    {
        return $this->markAllAppNotifications();
    }

    public function openNotification(AppNotification $notification): RedirectResponse
    {
        return $this->openAppNotification($notification);
    }

    protected function notificationsInboxPath(): string
    {
        return route('account.notifications');
    }

    protected function notificationOpenRoute(): string
    {
        return 'account.notifications.open';
    }

    protected function notificationReadRoute(): string
    {
        return 'account.notifications.read';
    }
}
