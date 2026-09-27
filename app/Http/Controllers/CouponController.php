<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        private CartService $cart,
    ) {}

    public function apply(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ], [
            'coupon_code.required' => 'أدخل رمز كود الخصم أولاً.',
        ]);

        $user = $request->user();
        $quote = $this->cart->quote($user);

        if ($quote['lines'] === []) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'السلة فارغة.'], 422);
            }

            return back()->with('error', 'السلة فارغة.');
        }

        $result = $this->cart->applyCoupon($request->input('coupon_code'), (float) $quote['subtotal']);

        if (! $result['success']) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], 422);
            }

            return back()->withInput()->with('error', $result['message']);
        }

        $newQuote = $this->cart->quote($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'coupon' => [
                    'code' => $result['coupon']->code,
                    'discount_label' => $result['coupon']->formatDiscountLabel(),
                    'discount_amount' => $newQuote['coupon_discount'],
                ],
                'quote' => [
                    'subtotal' => $newQuote['subtotal'],
                    'discount_amount' => $newQuote['discount_amount'],
                    'discount_percent' => $newQuote['discount_percent'],
                    'delivery_fee' => $newQuote['delivery_fee'],
                    'total' => $newQuote['total'],
                ],
            ]);
        }

        return back()->with('success', $result['message']);
    }

    public function remove(Request $request): JsonResponse|RedirectResponse
    {
        $this->cart->removeCoupon();
        $newQuote = $this->cart->quote($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء كود الخصم.',
                'quote' => [
                    'subtotal' => $newQuote['subtotal'],
                    'discount_amount' => $newQuote['discount_amount'],
                    'discount_percent' => $newQuote['discount_percent'],
                    'delivery_fee' => $newQuote['delivery_fee'],
                    'total' => $newQuote['total'],
                ],
            ]);
        }

        return back()->with('success', 'تم إلغاء كود الخصم.');
    }
}
