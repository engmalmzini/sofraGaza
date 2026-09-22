<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function __construct(private PointsService $points) {}
    public function index(Request $request): View
    {
        $restrictToDeliveryArea = true;

        if ($request->exists('area')) {
            $areaKey = $request->string('area')->toString();
            if ($areaKey !== '') {
                $area = collect(config('brand.areas', []))->firstWhere('key', $areaKey);
                if ($area) {
                    session(['delivery_area' => $area]);
                }
            } else {
                $restrictToDeliveryArea = false;
            }
        }

        $query = Restaurant::query()->visible()->withCount('menuItems');

        if ($restrictToDeliveryArea) {
            $query->inDeliveryArea();
        }

        if ($request->filled('type') && in_array($request->type, ['restaurant', 'cafe'], true)) {
            $query->where('type', $request->type);
        }

        if ($request->filled('cuisine') && array_key_exists($request->string('cuisine')->toString(), config('brand.cuisines', []))) {
            $query->matchingCuisine($request->string('cuisine')->toString());
        }

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', $term)
                    ->orWhereHas('menuItems', fn ($items) => $items->where('name', 'like', $term));
            });
        }

        $restaurants = $query->latest()->paginate(12)->withQueryString();

        return view('restaurants.index', compact('restaurants'));
    }

    public function show(Restaurant $restaurant): View
    {
        abort_unless($restaurant->isVisible(), 404);

        $restaurant->load(['menuItems' => fn ($q) => $q->orderBy('category')->orderBy('name')]);
        $grouped = $restaurant->menuItems->groupBy('category');

        $menuSections = $grouped->map(function ($items, $category) {
            $name = $category ?: 'قائمة الطعام';

            return [
                'key' => 'c-'.substr(md5((string) $category), 0, 10),
                'name' => $name,
                'icon' => $this->categoryIcon($name),
                'hint' => $this->categoryHint($name),
                'items' => $items,
            ];
        })->values();

        $cart = app(\App\Services\CartService::class)->quote(auth()->user());
        $restaurantCart = ($cart['restaurant']?->id === $restaurant->id && $cart['lines']) ? $cart : null;

        return view('restaurants.show', [
            'restaurant' => $restaurant,
            'menuSections' => $menuSections,
            'restaurantCart' => $restaurantCart,
            'rewards' => $this->points->rewardsForRestaurant($restaurant, 4),
            'membership' => auth()->user()?->activeMembership(),
            'pointsBalance' => (int) (auth()->user()->points_balance ?? 0),
            'rating' => number_format(4.6 + ($restaurant->id % 4) * 0.1, 1),
            'reviews' => 180 + ($restaurant->id * 47),
            'eta' => $restaurant->type === 'cafe' ? ['15', '25'] : ['25', '40'],
        ]);
    }

    private function categoryIcon(string $name): string
    {
        return match (true) {
            str_contains($name, 'كاف') || str_contains($name, 'مشروب') => 'local_cafe',
            str_contains($name, 'ساندو') || str_contains($name, 'برجر') || str_contains($name, 'شاورما') => 'lunch_dining',
            str_contains($name, 'مشوي') || str_contains($name, 'كباب') || str_contains($name, 'طاجن') => 'outdoor_grill',
            str_contains($name, 'حلويات') || str_contains($name, 'كنافة') => 'icecream',
            str_contains($name, 'عائلي') || str_contains($name, 'سدر') => 'groups',
            str_contains($name, 'سمك') || str_contains($name, 'بحر') => 'set_meal',
            default => 'restaurant_menu',
        };
    }

    private function categoryHint(string $name): string
    {
        return match (true) {
            str_contains($name, 'عائلي') => 'تكفي 3-6 أفراد',
            str_contains($name, 'مشوي') => 'على الفحم الطازج',
            str_contains($name, 'ساندو') => 'خبز صاج وطابون طازج',
            str_contains($name, 'كاف') => 'تحضير فوري',
            default => 'طازج يومياً',
        };
    }
}
