<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuOffer;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuOfferController extends Controller
{
    public function create(Restaurant $restaurant): View
    {
        return view('admin.menu-offers.create', [
            'restaurant' => $restaurant,
            'items' => $restaurant->menuItems()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $this->validated($request, $restaurant);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('offers', 'public');
        }

        $restaurant->menuOffers()->create($data);

        return redirect()->route('admin.restaurants.menu-items.index', $restaurant)
            ->with('success', 'تم إنشاء العرض ويظهر في أول صفحة المكان.');
    }

    public function edit(Restaurant $restaurant, MenuOffer $menuOffer): View
    {
        abort_unless($menuOffer->restaurant_id === $restaurant->id, 404);

        return view('admin.menu-offers.edit', [
            'restaurant' => $restaurant,
            'offer' => $menuOffer,
            'items' => $restaurant->menuItems()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Restaurant $restaurant, MenuOffer $menuOffer): RedirectResponse
    {
        abort_unless($menuOffer->restaurant_id === $restaurant->id, 404);
        $data = $this->validated($request, $restaurant);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('offers', 'public');
        }

        $menuOffer->update($data);

        return redirect()->route('admin.restaurants.menu-items.index', $restaurant)
            ->with('success', 'تم تحديث العرض.');
    }

    public function destroy(Restaurant $restaurant, MenuOffer $menuOffer): RedirectResponse
    {
        abort_unless($menuOffer->restaurant_id === $restaurant->id, 404);
        $menuOffer->delete();

        return back()->with('success', 'تم حذف العرض.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Restaurant $restaurant): array
    {
        $data = $request->validate(MenuOffer::inputRules($restaurant), [
            'second_item_id.different' => 'اختَر صنفين مختلفين للعرض.',
        ]);

        $data['second_item_id'] = ! empty($data['second_item_id']) ? $data['second_item_id'] : null;
        $data['is_active'] = $request->boolean('is_active');

        $first = $restaurant->menuItems()->find($data['first_item_id']);
        $second = $data['second_item_id']
            ? $restaurant->menuItems()->find($data['second_item_id'])
            : null;
        $original = (float) ($first?->price ?? 0) + (float) ($second?->price ?? 0);

        if ((float) $data['price'] >= $original) {
            throw ValidationException::withMessages([
                'price' => 'سعر العرض لازم يكون أقل من مجموع الأصناف ('.$original.' ₪).',
            ]);
        }

        unset($data['image']);

        return $data;
    }
}
