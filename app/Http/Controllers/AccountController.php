<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAppNotifications;
use App\Models\Address;
use App\Models\AppNotification;
use App\Models\GroupOrderMember;
use App\Models\Order;
use App\Services\ReferralService;
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

        if (! $user->canShopAsCustomer()) {
            return redirect()->to($user->staffHomeRoute());
        }

        return view('account.show', [
            'user' => $user,
            'membership' => $user->activeMembership(),
            'subscription' => $user->activeSubscription(),
            'tier' => $user->tier(),
            'recentOrders' => $user->orders()->with('restaurant')->latest()->take(5)->get(),
            'ordersCount' => $user->orders()->count(),
            'addressesCount' => $user->addresses()->count(),
            'favoritesCount' => $user->favorites()->count(),
            'referredCount' => $user->referredUsers()->count(),
            'unreadNotifications' => $user->unreadNotificationsCount(),
        ]);
    }

    public function invite(ReferralService $referrals): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isCourier() || ! $user->canShopAsCustomer()) {
            return redirect()->to($user->staffHomeRoute());
        }

        $code = $user->ensureReferralCode();
        $invites = $user->referredUsers()->latest()->take(30)->get();

        return view('account.invite', [
            'user' => $user,
            'code' => $code,
            'shareUrl' => $referrals->shareUrl($user),
            'shareText' => $referrals->shareText($user),
            'whatsappUrl' => $referrals->whatsappShareUrl($user),
            'inviterPoints' => $referrals->inviterPoints(),
            'inviteePoints' => $referrals->inviteePoints(),
            'invites' => $invites,
            'invitesCount' => $user->referredUsers()->count(),
        ]);
    }

    public function orders(): View
    {
        $orders = $this->customerOrdersQuery()->paginate(10);
        $ordersByStatus = Order::groupForBoard(
            $this->customerOrdersQuery()->take(40)->get()
        );

        return view('account.orders', compact('orders', 'ordersByStatus'));
    }

    public function showOrder(Order $order): View
    {
        $this->authorizeCustomerOrder($order);
        $order->load(['items', 'restaurant', 'review', 'groupOrder.members.user']);

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
            'area' => ['nullable', 'string', 'max:50'],
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

    private function customerOrdersQuery()
    {
        $userId = auth()->id();

        return Order::query()
            ->with('restaurant')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('groupOrder.members', function ($members) use ($userId) {
                        $members->where('user_id', $userId)
                            ->where('status', '!=', GroupOrderMember::STATUS_DECLINED);
                    });
            })
            ->latest();
    }

    private function authorizeCustomerOrder(Order $order): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ((int) $order->user_id === (int) $user->id) {
            return;
        }

        $order->loadMissing('groupOrder.members');
        abort_unless($order->groupOrder?->memberFor($user), 403);
    }
}
