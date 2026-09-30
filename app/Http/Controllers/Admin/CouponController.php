<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $totalCount = Coupon::count();
        $activeCount = Coupon::active()->count();
        $expiredCount = Coupon::whereNotNull('expires_at')->where('expires_at', '<=', now())->count();
        $disabledCount = Coupon::where('is_active', false)->count();
        $totalUses = (int) Coupon::sum('used_count');

        $query = Coupon::query()->with('restaurant');

        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'disabled') {
                $query->where('is_active', false);
            } elseif ($status === 'expired') {
                $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
            }
        }

        if ($request->filled('restaurant_id')) {
            $restaurantFilter = $request->query('restaurant_id');
            if ($restaurantFilter === 'general') {
                $query->whereNull('restaurant_id');
            } else {
                $query->where('restaurant_id', (int) $restaurantFilter);
            }
        }

        if ($request->filled('q')) {
            $term = trim($request->query('q'));
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('restaurant', function ($rq) use ($term) {
                        $rq->where('name', 'like', "%{$term}%");
                    });
            });
        }

        $coupons = $query->latest()->paginate(15)->withQueryString();

        $restaurants = Restaurant::query()
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('admin.coupons.index', compact(
            'coupons',
            'restaurants',
            'totalCount',
            'activeCount',
            'expiredCount',
            'disabledCount',
            'totalUses'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
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
            'restaurant_id.exists' => 'المطعم المحدد غير موجود.',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['restaurant_id'] = filled($data['restaurant_id'] ?? null) ? (int) $data['restaurant_id'] : null;
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        Coupon::create($data);

        return back()->with('success', "تم إنشاء كود الخصم {$data['code']} بنجاح.");
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code,' . $coupon->id],
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0.5', 'max:1000'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'max_discount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'code.required' => 'أدخل رمز كود الخصم.',
            'code.unique' => 'رمز كود الخصم هذا مستخدم مسبقاً لكود آخر.',
            'value.required' => 'أدخل قيمة الخصم.',
            'restaurant_id.exists' => 'المطعم المحدد غير موجود.',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['restaurant_id'] = filled($data['restaurant_id'] ?? null) ? (int) $data['restaurant_id'] : null;
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $coupon->update($data);

        return back()->with('success', "تم تحديث بيانات كود الخصم {$coupon->code} بنجاح.");
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
