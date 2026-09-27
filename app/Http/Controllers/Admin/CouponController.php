<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::query()->latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0.5', 'max:1000'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'max_discount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'code.required' => 'أدخل رمز كود الخصم.',
            'code.unique' => 'رمز كود الخصم هذا مستخدم مسبقاً.',
            'value.required' => 'أدخل قيمة الخصم.',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = true;

        Coupon::create($data);

        return back()->with('success', "تم إنشاء كود الخصم {$data['code']} بنجاح.");
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);
        $status = $coupon->is_active ? 'تفعيل' : 'تعطيل';

        return back()->with('success', "تم {$status} كود الخصم {$coupon->code}.");
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $code = $coupon->code;
        $coupon->delete();

        return back()->with('success', "تم حذف كود الخصم {$code}.");
    }
}
