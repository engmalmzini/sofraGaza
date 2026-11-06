<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\MenuOffer;
use App\Services\CartService;
use App\Support\MenuOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        $areaKey = session('delivery_area')['key'] ?? null;

        return view('cart.index', [
            'quote' => $this->cart->quote(auth()->user(), $areaKey),
        ]);
    }

    public function store(Request $request, MenuItem $item): JsonResponse|RedirectResponse
    {
        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'option_groups' => ['nullable', 'array'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['string', 'max:100'],
        ]);

        $notes = $request->input('notes');
        $addons = $request->input('addons', []);
        if (! empty($addons) && is_array($addons)) {
            $addonText = implode('، ', array_filter($addons));
            if ($addonText !== '') {
                $notes = filled($notes) ? "{$notes} (مع: {$addonText})" : "مع: {$addonText}";
            }
        }

        try {
            $resolved = MenuOptions::resolve($item, $request->input('option_groups', []));
            $this->cart->add($item, (int) $request->input('quantity', 1), $notes, $resolved['choices']);
        } catch (RuntimeException $e) {
            return $this->respond($request, $e->getMessage(), false);
        }

        return $this->respond($request, "تمت إضافة {$item->name} إلى السلة.");
    }

    public function storeOffer(Request $request, MenuOffer $offer): JsonResponse|RedirectResponse
    {
        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->cart->addOffer($offer, (int) $request->input('quantity', 1), $request->input('notes'));
        } catch (RuntimeException $e) {
            return $this->respond($request, $e->getMessage(), false);
        }

        return $this->respond($request, "تمت إضافة {$offer->cartName()} إلى السلة.");
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'item_id' => ['required'],
            'line_key' => ['nullable', 'string', 'max:80'],
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $hasNotesField = $request->has('notes');
        $itemId = (int) $request->input('item_id');
        $lineKey = (string) ($request->input('line_key') ?: $request->input('item_id'));

        try {
            $this->cart->update(
                $itemId,
                (int) $request->quantity,
                $request->input('notes'),
                $hasNotesField,
                $lineKey
            );
        } catch (RuntimeException $e) {
            return $this->respond($request, $e->getMessage(), false);
        }

        return $this->respond($request, 'تم تحديث السلة.');
    }

    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        $this->cart->clear();

        return $this->respond($request, 'تم إفراغ السلة.');
    }

    private function respond(Request $request, string $message, bool $ok = true): JsonResponse|RedirectResponse
    {
        $areaKey = session('delivery_area')['key'] ?? null;
        $payload = [
            'ok' => $ok,
            'message' => $message,
            'cart' => $this->cart->payload(auth()->user(), $areaKey),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, $ok ? 200 : 422);
        }

        return $ok
            ? back()->with('success', $message)
            : back()->with('error', $message);
    }
}
