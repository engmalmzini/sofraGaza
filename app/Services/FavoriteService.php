<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FavoriteService
{
    /** @var array<int, array{restaurants: array<int, int>, menu_items: array<int, int>}> */
    private array $idCache = [];

    public function toggle(User $user, Model $model): bool
    {
        $existing = Favorite::query()
            ->where('user_id', $user->id)
            ->where('favorable_type', $model::class)
            ->where('favorable_id', $model->getKey())
            ->first();

        if ($existing) {
            $existing->delete();
            unset($this->idCache[$user->id]);

            return false;
        }

        Favorite::query()->create([
            'user_id' => $user->id,
            'favorable_type' => $model::class,
            'favorable_id' => $model->getKey(),
        ]);

        unset($this->idCache[$user->id]);

        return true;
    }

    /**
     * @return array{restaurants: array<int, int>, menu_items: array<int, int>}
     */
    public function idsFor(User $user): array
    {
        if (isset($this->idCache[$user->id])) {
            return $this->idCache[$user->id];
        }

        $rows = Favorite::query()
            ->where('user_id', $user->id)
            ->get(['favorable_type', 'favorable_id']);

        $restaurants = [];
        $menuItems = [];

        foreach ($rows as $row) {
            $id = (int) $row->favorable_id;
            if ($row->favorable_type === Restaurant::class) {
                $restaurants[] = $id;
            } elseif ($row->favorable_type === MenuItem::class) {
                $menuItems[] = $id;
            }
        }

        return $this->idCache[$user->id] = [
            'restaurants' => $restaurants,
            'menu_items' => $menuItems,
        ];
    }

    public function isFavorited(User $user, Model $model): bool
    {
        $ids = $this->idsFor($user);
        $id = (int) $model->getKey();

        return match ($model::class) {
            Restaurant::class => in_array($id, $ids['restaurants'], true),
            MenuItem::class => in_array($id, $ids['menu_items'], true),
            default => false,
        };
    }

    /**
     * @return array{dishes: Collection<int, MenuItem>, restaurants: Collection<int, Restaurant>}
     */
    public function suggestionsFor(User $user): array
    {
        $orders = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending_confirmation', 'confirmed', 'preparing', 'delivering', 'delivered'])
            ->with(['items.menuItem.restaurant', 'restaurant'])
            ->latest()
            ->take(40)
            ->get();

        $dishQty = [];
        $restaurantSeen = [];

        foreach ($orders as $order) {
            if ($order->restaurant) {
                $restaurantSeen[$order->restaurant_id] = $order->restaurant;
            }

            foreach ($order->items as $line) {
                $item = $line->menuItem;
                if (! $item) {
                    continue;
                }

                $dishQty[$item->id] = ($dishQty[$item->id] ?? 0) + max(1, (int) $line->quantity);
            }
        }

        arsort($dishQty);

        $dishes = MenuItem::query()
            ->whereIn('id', array_keys($dishQty))
            ->where('is_available', true)
            ->whereHas('restaurant', fn ($query) => $query->visible())
            ->with('restaurant')
            ->get()
            ->sortByDesc(fn (MenuItem $item) => $dishQty[$item->id] ?? 0)
            ->values()
            ->take(6);

        $restaurants = collect($restaurantSeen)
            ->filter(fn (Restaurant $restaurant) => $restaurant->isVisible())
            ->unique('id')
            ->values()
            ->take(6);

        return [
            'dishes' => $dishes,
            'restaurants' => $restaurants,
        ];
    }

    /**
     * @return array{restaurants: Collection<int, Restaurant>, dishes: Collection<int, MenuItem>}
     */
    public function savedFor(User $user): array
    {
        $ids = $this->idsFor($user);

        $restaurants = $ids['restaurants'] === []
            ? collect()
            : Restaurant::query()->visible()->whereIn('id', $ids['restaurants'])->latest()->get();

        $dishes = $ids['menu_items'] === []
            ? collect()
            : MenuItem::query()
                ->whereIn('id', $ids['menu_items'])
                ->whereHas('restaurant', fn ($query) => $query->visible())
                ->with('restaurant')
                ->latest()
                ->get();

        return compact('restaurants', 'dishes');
    }
}
