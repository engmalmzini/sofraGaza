<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orders,
    ) {}

    public function create(): View|RedirectResponse
    {
        $user = auth()->user();
        $areaKey = session('delivery_area')['key'] ?? (config('brand.areas.0.key') ?? 'الرمال');
        $quote = $this->cart->quote($user, $areaKey);

        if ($quote['lines'] === []) {
            return redirect()->route('cart.index')->with('error', 'السلة فارغة.');
        }

        return view('checkout.create', [
            'quote' => $quote,
            'user' => $user,
            'addresses' => $user->addresses,
            'deliveryAreas' => Setting::areasWithFees(),
            'selectedAreaKey' => $areaKey,
            'paymentAccounts' => Setting::paymentAccounts(),
            'appliedCoupon' => $this->cart->appliedCoupon(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $paymentMethod = $request->input('payment_method', 'receipt');
        $isWallet = $paymentMethod === 'wallet';

        $rules = [
            'payment_method' => ['nullable', 'string', 'in:receipt,wallet'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'area' => ['nullable', 'string', 'max:50'],
            'address_details' => ['required', 'string', 'min:10'],
            'phone' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'receipt' => [$isWallet ? 'nullable' : 'required', 'image', 'max:4096'],
        ];

        $messages = [
            'address_details.required' => 'أدخل عنوان التوصيل بالتفصيل.',
            'address_details.min' => 'العنوان قصير جداً، أضف الحي والشارع ومَعلماً قريباً.',
            'phone.required' => 'رقم الهاتف مطلوب للتواصل.',
            'receipt.required' => 'أرفق صورة إشعار الحوالة لتأكيد الدفع.',
            'receipt.image' => 'ملف الحوالة يجب أن يكون صورة.',
        ];

        $data = $request->validate($rules, $messages);
        $data['payment_method'] = $paymentMethod;

        if ($request->filled('coupon_code')) {
            $quote = $this->cart->quote($request->user());
            $this->cart->applyCoupon($request->input('coupon_code'), (float) $quote['subtotal']);
        }

        if (! empty($data['area'])) {
            $selected = collect(Setting::allAreas())->firstWhere('key', $data['area']);
            if ($selected) {
                $selected['delivery_fee'] = Setting::deliveryFeeForArea($data['area']);
                session(['delivery_area' => $selected]);
            }
        }

        try {
            $order = $this->orders->placePurchase($request->user(), $data, $request->file('receipt'));
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = $order->isPaidWithWallet()
            ? 'تم تأكيد طلبك فوراً وخصم القيمة من رصيد محفظتك بنجاح.'
            : 'تم إرسال طلبك وهو بانتظار مراجعة الحوالة.';

        return redirect()->route('account.orders.show', $order)->with('success', $message);
    }
}
