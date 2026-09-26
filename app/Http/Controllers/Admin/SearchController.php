<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipSubscription;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $q = trim($request->string('q')->toString());
        $scope = $request->string('scope')->toString() ?: 'global';

        if (mb_strlen($q) < 1) {
            return response()->json(['results' => []]);
        }

        $results = match ($scope) {
            'orders' => $this->orders($q, 8),
            'restaurants' => $this->restaurants($q, 8),
            'menu_items' => $this->menuItems($q, $request->integer('restaurant_id'), 8),
            'users' => $this->users($q, 8),
            'memberships' => $this->memberships($q, 8),
            'subscriptions' => $this->subscriptions($q, 8),
            'listings' => $this->restaurants($q, 8),
            'settings' => $this->settings($q, 8),
            'couriers' => $this->couriers($q, 8)->concat($this->orders($q, 4)),
            default => $this->global($q),
        };

        return response()->json(['results' => $this->finalize($results)]);
    }

    private function global(string $q): Collection
    {
        return $this->orders($q, 3)
            ->concat($this->restaurants($q, 2))
            ->concat($this->users($q, 2))
            ->concat($this->memberships($q, 1))
            ->concat($this->subscriptions($q, 1))
            ->concat($this->settings($q, 1))
            ->concat($this->couriers($q, 2));
    }

    private function orders(string $q, int $limit): Collection
    {
        return Order::query()
            ->with(['user', 'restaurant'])
            ->where(function ($builder) use ($q) {
                $builder->where('id', $q)
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"))
                    ->orWhereHas('restaurant', fn ($restaurant) => $restaurant->where('name', 'like', "%{$q}%"));
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Order $order) => $this->row(
                '#'.$order->id.' — '.$order->user->name,
                $order->restaurant->name.' • '.number_format((float) $order->total, 2).' ₪ • '.$order->statusLabel(),
                route('admin.orders.show', $order),
                'receipt_long',
                $q,
                $order->user->name.' '.$order->id,
            ));
    }

    private function restaurants(string $q, int $limit): Collection
    {
        return Restaurant::query()
            ->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Restaurant $restaurant) => $this->row(
                $restaurant->name,
                $restaurant->typeLabel().' • '.$restaurant->verificationLabel().' • '.($restaurant->address ?: 'غزة'),
                $restaurant->isPending()
                    ? route('admin.restaurants.show', $restaurant)
                    : route('admin.restaurants.edit', $restaurant),
                $restaurant->type === 'cafe' ? 'local_cafe' : 'storefront',
                $q,
                $restaurant->name,
            ));
    }

    private function menuItems(string $q, int $restaurantId, int $limit): Collection
    {
        $query = MenuItem::query()->with('restaurant')->where('name', 'like', "%{$q}%");

        if ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        }

        return $query->latest()
            ->limit($limit)
            ->get()
            ->map(fn (MenuItem $item) => $this->row(
                $item->name,
                $item->restaurant->name.' • '.($item->category ?: 'صنف').' • '.number_format((float) $item->price, 2).' ₪',
                route('admin.restaurants.menu-items.edit', [$item->restaurant, $item]),
                'fastfood',
                $q,
                $item->name,
            ));
    }

    private function users(string $q, int $limit): Collection
    {
        return User::query()
            ->where('role', 'customer')
            ->where(fn ($builder) => $builder->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"))
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (User $user) => $this->row(
                $user->name,
                $user->phone.' • '.$user->points_balance.' نقطة',
                route('admin.users.show', $user),
                'person',
                $q,
                $user->name,
            ));
    }

    private function memberships(string $q, int $limit): Collection
    {
        return Membership::query()
            ->where('name', 'like', "%{$q}%")
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(fn (Membership $membership) => $this->row(
                $membership->name,
                number_format((float) $membership->monthly_price).' ₪ • خصم '.$membership->discount_percent.'%',
                route('admin.memberships.edit', $membership),
                'workspace_premium',
                $q,
                $membership->name,
            ));
    }

    private function subscriptions(string $q, int $limit): Collection
    {
        return MembershipSubscription::query()
            ->with(['user', 'membership'])
            ->where(function ($builder) use ($q) {
                $builder->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"))
                    ->orWhereHas('membership', fn ($membership) => $membership->where('name', 'like', "%{$q}%"));
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (MembershipSubscription $subscription) => $this->row(
                $subscription->user->name,
                $subscription->membership->name.' • '.$subscription->statusLabel(),
                route('admin.subscriptions.show', $subscription),
                'verified',
                $q,
                $subscription->user->name,
            ));
    }

    private function settings(string $q, int $limit): Collection
    {
        return Setting::query()
            ->where(fn ($builder) => $builder->where('label', 'like', "%{$q}%")->orWhere('key', 'like', "%{$q}%"))
            ->limit($limit)
            ->get()
            ->map(fn (Setting $setting) => $this->row(
                $setting->label,
                $setting->key,
                route('admin.settings.index').'#setting-'.$setting->key,
                'settings',
                $q,
                $setting->label,
            ));
    }

    private function couriers(string $q, int $limit): Collection
    {
        return User::query()
            ->where('role', 'courier')
            ->where(fn ($builder) => $builder->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"))
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (User $user) => $this->row(
                $user->name,
                $user->phone.' • '.($user->isCourierBusy() ? 'مشغول' : 'فاضي'),
                route('admin.delivery.show', $user),
                'moped',
                $q,
                $user->name,
            ));
    }

    private function row(string $title, string $subtitle, string $url, string $icon, string $query, string $scoreName): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'url' => $url,
            'icon' => $icon,
            'score' => $this->matchScore($query, $scoreName),
        ];
    }

    private function finalize(Collection $results): Collection
    {
        return $results
            ->sortBy('score')
            ->take(8)
            ->values()
            ->map(fn ($row) => collect($row)->except('score')->all());
    }

    private function matchScore(string $query, string $name): float
    {
        $query = mb_strtolower($query);
        $name = mb_strtolower($name);

        if (str_starts_with($name, $query)) {
            return 0;
        }

        $position = mb_strpos($name, $query);

        return $position === false ? 9 : 1 + ($position / 100);
    }
}
