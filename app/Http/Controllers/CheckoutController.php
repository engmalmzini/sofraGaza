<?php

namespace App\Http\Controllers;

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
        $quote = $this->cart->quote(auth()->user());

        if ($quote['lines'] === []) {
            return redirect()->route('cart.index')->with('error', 'السلة فارغة.');
        }

        return view('checkout.create', [
            'quote' => $quote,
            'addresses' => auth()->user()->addresses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address_details' => ['required', 'string', 'min:10'],
            'phone' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'receipt' => ['required', 'image', 'max:4096'],
        ], [
            'address_details.required' => 'أدخل عنوان التوصيل بالتفصيل.',
            'address_details.min' => 'العنوان قصير جداً، أضف الحي والشارع ومَعلماً قريباً.',
            'phone.required' => 'رقم الهاتف مطلوب للتواصل.',
            'receipt.required' => 'أرفق صورة إشعار الحوالة لتأكيد الدفع.',
            'receipt.image' => 'ملف الحوالة يجب أن يكون صورة.',
        ]);

        try {
            $order = $this->orders->placePurchase($request->user(), $data, $request->file('receipt'));
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('account.orders.show', $order)
            ->with('success', 'تم إرسال طلبك وهو بانتظار مراجعة الحوالة.');
    }
}
