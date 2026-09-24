<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): View
    {
        $query = $restaurant->menuItems()->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            });
        }

        $items = $query->paginate(20)->withQueryString();

        return view('admin.menu-items.index', compact('restaurant', 'items'));
    }

    public function create(Restaurant $restaurant): View
    {
        return view('admin.menu-items.create', compact('restaurant'));
    }

    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu', 'public');
        }

        $restaurant->menuItems()->create($data);

        return redirect()->route('admin.restaurants.menu-items.index', $restaurant)
            ->with('success', 'تمت إضافة الصنف إلى المنيو.');
    }

    public function edit(Restaurant $restaurant, MenuItem $menuItem): View
    {
        abort_unless($menuItem->restaurant_id === $restaurant->id, 404);

        return view('admin.menu-items.edit', compact('restaurant', 'menuItem'));
    }

    public function update(Request $request, Restaurant $restaurant, MenuItem $menuItem): RedirectResponse
    {
        abort_unless($menuItem->restaurant_id === $restaurant->id, 404);
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu', 'public');
        }

        $menuItem->update($data);

        return redirect()->route('admin.restaurants.menu-items.index', $restaurant)
            ->with('success', 'تم تحديث الصنف.');
    }

    public function destroy(Restaurant $restaurant, MenuItem $menuItem): RedirectResponse
    {
        abort_unless($menuItem->restaurant_id === $restaurant->id, 404);
        $menuItem->delete();

        return back()->with('success', 'تم حذف الصنف.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'category_custom' => ['required_if:category,__custom__', 'nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_available' => ['nullable'],
            'earn_points' => ['nullable', 'integer', 'min:0'],
            'redeem_points' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($data['category'] === '__custom__') {
            $data['category'] = trim((string) $data['category_custom']);
        }

        $data['is_available'] = $request->boolean('is_available');
        $data['earn_points'] = $request->filled('earn_points') ? (int) $data['earn_points'] : null;
        $data['redeem_points'] = $request->filled('redeem_points') ? (int) $data['redeem_points'] : null;
        unset($data['image'], $data['category_custom']);

        return $data;
    }
}
