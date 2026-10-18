<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\GroupOrder;
use App\Models\Membership;
use App\Models\MembershipSubscription;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantBoost;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SafeDeletion
{
    public function deleteRestaurant(Restaurant $restaurant): void
    {
        DB::transaction(function () use ($restaurant) {
            $name = $restaurant->name;

            Order::query()
                ->where('restaurant_id', $restaurant->id)
                ->update([
                    'restaurant_name' => $name,
                    'restaurant_id' => null,
                ]);

            GroupOrder::query()
                ->where('restaurant_id', $restaurant->id)
                ->where('status', '!=', GroupOrder::STATUS_PLACED)
                ->get()
                ->each(fn (GroupOrder $group) => $group->delete());

            GroupOrder::query()->where('restaurant_id', $restaurant->id)->update([
                'restaurant_id' => null,
            ]);

            Favorite::query()
                ->where('favorable_type', Restaurant::class)
                ->where('favorable_id', $restaurant->id)
                ->delete();

            Favorite::query()
                ->where('favorable_type', MenuItem::class)
                ->whereIn('favorable_id', $restaurant->menuItems()->select('id'))
                ->delete();

            $restaurant->delete();
        });
    }

    public function deleteOrder(Order $order): void
    {
        $order->delete();
    }

    public function deleteMenuItem(MenuItem $item): void
    {
        Favorite::query()
            ->where('favorable_type', MenuItem::class)
            ->where('favorable_id', $item->id)
            ->delete();

        $item->delete();
    }

    public function deleteMenuCategory(Restaurant $restaurant, string $category): int
    {
        $category = trim($category);
        if ($category === '') {
            throw new RuntimeException('حدد التصنيف المراد حذفه.');
        }

        $items = $restaurant->menuItems()->where('category', $category)->get();
        foreach ($items as $item) {
            $this->deleteMenuItem($item);
        }

        return $items->count();
    }

    public function deleteUser(User $user, ?User $actor = null): void
    {
        if ($actor && (int) $actor->id === (int) $user->id) {
            throw new RuntimeException('لا يمكنك حذف حسابك وأنت داخل اللوحة.');
        }

        if ($user->isAdmin()) {
            throw new RuntimeException('حسابات الإدارة تُدار من صفحة الفريق وليس بالحذف من هنا.');
        }

        DB::transaction(function () use ($user) {
            $restaurant = $user->ownedRestaurant;
            if ($restaurant) {
                $this->deleteRestaurant($restaurant);
            }

            $user->delete();
        });
    }

    public function deleteMembership(Membership $membership): void
    {
        DB::transaction(function () use ($membership) {
            $membership->subscriptions()->delete();
            $membership->delete();
        });
    }

    public function deleteSubscription(MembershipSubscription $subscription): void
    {
        $subscription->delete();
    }

    public function deleteBoost(RestaurantBoost $boost): void
    {
        $boost->delete();
    }
}
