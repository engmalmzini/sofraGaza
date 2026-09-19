<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\CartService;
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
        return view('cart.index', [
            'quote' => $this->cart->quote(auth()->user()),
        ]);
    }

    public function store(Request $request, MenuItem $item): JsonResponse|RedirectResponse
    {
        $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:20']]);

        try {
            $this->cart->add($item, (int) $request->input('quantity', 1));
        } catch (RuntimeException $e) {
            return $this->respond($request, $e->getMessage(), false);
        }

        return $this->respond($request, "تمت إضافة {$item->name} إلى السلة.");
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $this->cart->update((int) $request->item_id, (int) $request->quantity);

        return $this->respond($request, 'تم تحديث السلة.');
    }

    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        $this->cart->clear();

        return $this->respond($request, 'تم إفراغ السلة.');
    }

    private function respond(Request $request, string $message, bool $ok = true): JsonResponse|RedirectResponse
    {
        $payload = [
            'ok' => $ok,
            'message' => $message,
            'cart' => $this->cart->payload(auth()->user()),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, $ok ? 200 : 422);
        }

        return $ok
            ? back()->with('success', $message)
            : back()->with('error', $message);
    }
}
