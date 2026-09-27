<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeliveryAreaController extends Controller
{
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $key = $request->string('area')->toString();
        $area = collect(Setting::allAreas())->firstWhere('key', $key);

        if (! $area) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => 'المنطقة غير موجودة'], 422);
            }

            return back();
        }

        $area['delivery_fee'] = Setting::deliveryFeeForArea($key);
        session(['delivery_area' => $area]);

        if ($request->wantsJson() || $request->ajax()) {
            $quote = app(CartService::class)->quote(auth()->user(), $key);

            return response()->json([
                'ok' => true,
                'area' => $area,
                'delivery_fee' => $quote['delivery_fee'],
                'total' => $quote['total'],
                'items_total' => $quote['items_total'],
            ]);
        }

        return back();
    }
}
