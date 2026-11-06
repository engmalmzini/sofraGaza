<?php

namespace App\Http\Controllers\Partner;

use App\Models\MenuOffer;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuOfferController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function create(): View
    {
        $restaurant = $this->restaurant();

        return view('partner.menu-offers.create', [
            'restaurant' => $restaurant,
            'items' => $restaurant->menuItems()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $data = $this->validated($request, $restaurant);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('offers', 'public');
        }

        $offer = $restaurant->menuOffers()->create($data);
        $this->notifications->notifyAdmins(
            'تعديل منيو يحتاج مراجعة',
            "{$restaurant->name}: أُضيف العرض «{$offer->name}».",
            route('admin.restaurants.show', $restaurant)
        );

        return redirect()->route('partner.menu-items.index')
            ->with('success', 'تم إنشاء العرض ويظهر في أول صفحة المكان.');
    }

    public function edit(MenuOffer $menuOffer): View
    {
        $this->authorizeOffer($menuOffer);
        $restaurant = $this->restaurant();

        return view('partner.menu-offers.edit', [
            'restaurant' => $restaurant,
            'offer' => $menuOffer,
            'items' => $restaurant->menuItems()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, MenuOffer $menuOffer): RedirectResponse
    {
        $this->authorizeOffer($menuOffer);
        $restaurant = $this->restaurant();
        $data = $this->validated($request, $restaurant);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('offers', 'public');
        }

        $menuOffer->update($data);
        $this->notifications->notifyAdmins(
            'تعديل منيو يحتاج مراجعة',
            "{$restaurant->name}: عُدّل العرض «{$menuOffer->name}».",
            route('admin.restaurants.show', $restaurant)
        );

        return redirect()->route('partner.menu-items.index')
            ->with('success', 'تم تحديث العرض.');
    }

    public function destroy(MenuOffer $menuOffer): RedirectResponse
    {
        $this->authorizeOffer($menuOffer);
        $name = $menuOffer->name;
        $menuOffer->delete();
        $this->notifications->notifyAdmins(
            'تعديل منيو يحتاج مراجعة',
            "{$this->restaurant()->name}: حُذف العرض «{$name}».",
            route('admin.restaurants.show', $this->restaurant())
        );

        return back()->with('success', 'تم حذف العرض.');
    }

    private function authorizeOffer(MenuOffer $offer): void
    {
        abort_unless($offer->restaurant_id === $this->restaurant()->id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, $restaurant): array
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
