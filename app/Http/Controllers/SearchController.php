<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $q = trim($request->string('q')->toString());

        if (mb_strlen($q) < 1) {
            return response()->json(['results' => []]);
        }

        $like = '%'.$q.'%';

        $restaurants = Restaurant::query()
            ->visible()
            ->inDeliveryArea()
            ->where('name', 'like', $like)
            ->limit(5)
            ->get();

        $dishes = MenuItem::query()
            ->where('is_available', true)
            ->where('name', 'like', $like)
            ->whereHas('restaurant', fn ($query) => $query->visible())
            ->with('restaurant')
            ->limit(8)
            ->get();

        $results = collect();

        foreach ($restaurants as $restaurant) {
            $results->push([
                'type' => 'restaurant',
                'title' => $restaurant->name,
                'subtitle' => $restaurant->typeLabel().' • '.($restaurant->address ?: 'غزة'),
                'url' => route('restaurants.show', $restaurant),
                'image' => $restaurant->coverUrl(),
                'icon' => $restaurant->type === 'cafe' ? 'local_cafe' : 'restaurant',
                'score' => $this->matchScore($q, $restaurant->name),
            ]);
        }

        foreach ($dishes as $dish) {
            $results->push([
                'type' => 'dish',
                'title' => $dish->name,
                'subtitle' => $dish->restaurant->name.' • '.number_format((float) $dish->price, 0).' ₪',
                'url' => route('restaurants.show', $dish->restaurant).'#dish-'.$dish->id,
                'image' => $dish->imageUrl() ?: $dish->restaurant->coverUrl(),
                'icon' => 'fastfood',
                'score' => $this->matchScore($q, $dish->name) + 0.15,
            ]);
        }

        return response()->json([
            'results' => $results->sortBy('score')->take(5)->values()->map(fn ($row) => collect($row)->except('score')),
        ]);
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
