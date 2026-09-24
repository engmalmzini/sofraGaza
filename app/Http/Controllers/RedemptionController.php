<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\OrderService;
use App\Services\PointsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class RedemptionController extends Controller
{
    public function __construct(
        private OrderService $orders,
        private PointsService $points,
    ) {}

    public function create(Request $request): View
    {
        $restaurants = Restaurant::query()->visible()->orderBy('name')->get();
        $selected = $request->integer('restaurant_id') ?: $restaurants->first()?->id;
        $selectedRestaurant = $restaurants->firstWhere('id', $selected);

        $items = collect();
        if ($selectedRestaurant) {
            $items = MenuItem::query()
                ->where('restaurant_id', $selectedRestaurant->id)
                ->where('is_available', true)
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->map(function (MenuItem $item) use ($selectedRestaurant) {
                    $item->setRelation('restaurant', $selectedRestaurant);
                    $item->redeem_cost = $this->points->redeemCost($item);

                    return $item;
                });
        }

        $user = $request->user();
        $selectedItemId = $request->integer('menu_item_id') ?: (int) old('menu_item_id');
        if ($selectedItemId && $items->doesntContain('id', $selectedItemId)) {
            $selectedItemId = 0;
        }
        if (! $selectedItemId) {
            $selectedItemId = $items->first(fn (MenuItem $item) => $user->points_balance >= $item->redeem_cost)?->id ?? 0;
        }

        return view('redeem.create', [
            'earnRateLabel' => $this->points->earnRateLabel($selectedRestaurant),
            'redeemRateLabel' => $this->points->redeemRateLabel($selectedRestaurant),
            'restaurants' => $restaurants,
            'selected' => $selected,
            'items' => $items,
            'selectedItemId' => $selectedItemId,
            'balance' => $user->points_balance,
            'addresses' => $user->addresses()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'address_details' => ['required', 'string', 'min:10'],
            'phone' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $item = MenuItem::with('restaurant')->findOrFail($data['menu_item_id']);

        try {
            $order = $this->orders->placeRedemption($request->user(), $item, $data);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('account.orders.show', $order)
            ->with('success', 'تم إرسال طلب الاستبدال. سيتم مراجعة التوصيل قريباً.');
    }
}
