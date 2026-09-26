<?php

namespace App\Http\Controllers\Partner;

use App\Models\MenuItem;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $restaurant = $this->restaurant();
        $query = $restaurant->menuItems()->orderBy('category')->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $items = $query->get();

        return view('partner.menu-items.index', [
            'restaurant' => $restaurant,
            'items' => $items,
            'grouped' => $items->groupBy('category'),
            'categories' => $restaurant->menuCategories(),
        ]);
    }

    public function create(): View
    {
        return view('partner.menu-items.create', [
            'restaurant' => $this->restaurant(),
            'categories' => $this->restaurant()->menuCategories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu', 'public');
        }

        $item = $restaurant->menuItems()->create($data);

        $this->notifyMenuChange($restaurant->name, "أُضيف الصنف «{$item->name}» إلى المنيو.");

        return redirect()->route('partner.menu-items.index')
            ->with('success', 'تمت إضافة الصنف. وصل إشعار للإدارة لمراجعة التعديل.');
    }

    public function edit(MenuItem $menuItem): View
    {
        $this->authorizeItem($menuItem);

        return view('partner.menu-items.edit', [
            'restaurant' => $this->restaurant(),
            'menuItem' => $menuItem,
            'categories' => $this->restaurant()->menuCategories(),
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeItem($menuItem);
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu', 'public');
        }

        $menuItem->update($data);

        $this->notifyMenuChange($this->restaurant()->name, "عُدّل الصنف «{$menuItem->name}» في المنيو.");

        return redirect()->route('partner.menu-items.index')
            ->with('success', 'تم تحديث الصنف. وصل إشعار للإدارة لمراجعة التعديل.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeItem($menuItem);
        $name = $menuItem->name;
        $menuItem->delete();

        $this->notifyMenuChange($this->restaurant()->name, "حُذف الصنف «{$name}» من المنيو.");

        return back()->with('success', 'تم حذف الصنف. وصل إشعار للإدارة.');
    }

    private function authorizeItem(MenuItem $menuItem): void
    {
        abort_unless($menuItem->restaurant_id === $this->restaurant()->id, 404);
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
        ]);

        if ($data['category'] === '__custom__') {
            $data['category'] = trim((string) $data['category_custom']);
        }

        $data['is_available'] = $request->boolean('is_available');
        unset($data['image'], $data['category_custom']);

        return $data;
    }

    private function notifyMenuChange(string $restaurantName, string $body): void
    {
        $this->notifications->notifyAdmins(
            'تعديل منيو يحتاج مراجعة',
            "{$restaurantName}: {$body}",
            route('admin.restaurants.show', $this->restaurant())
        );
    }
}
