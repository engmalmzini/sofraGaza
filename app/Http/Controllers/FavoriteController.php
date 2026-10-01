<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function __construct(private FavoriteService $favorites) {}

    public function index(): View
    {
        $saved = $this->favorites->savedFor(auth()->user());

        return view('account.favorites', [
            'favoriteRestaurants' => $saved['restaurants'],
            'favoriteDishes' => $saved['dishes'],
        ]);
    }

    public function toggle(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:restaurant,menu_item'],
            'id' => ['required', 'integer'],
        ]);

        $model = match ($data['type']) {
            'restaurant' => Restaurant::query()->visible()->findOrFail($data['id']),
            'menu_item' => MenuItem::query()
                ->where('is_available', true)
                ->whereHas('restaurant', fn ($query) => $query->visible())
                ->findOrFail($data['id']),
        };

        $favorited = $this->favorites->toggle($request->user(), $model);
        $label = $model->name;
        $message = $favorited
            ? 'انحفظ '.$label.' في المفضلة.'
            : 'تشال '.$label.' من المفضلة.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'favorited' => $favorited,
                'message' => $message,
                'type' => $data['type'],
                'id' => (int) $data['id'],
            ]);
        }

        return back()->with('success', $message);
    }
}
