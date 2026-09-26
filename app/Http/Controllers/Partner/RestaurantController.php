<?php

namespace App\Http\Controllers\Partner;

use App\Models\Restaurant;
use App\Services\NotificationService;
use App\Support\PalestinianPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function edit(): View
    {
        return view('partner.restaurant.edit', [
            'restaurant' => $this->restaurant(),
            'cuisines' => config('brand.cuisines', []),
            'areas' => config('brand.areas', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $data = $this->validated($request, $restaurant);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('restaurants', 'public');
        }

        $restaurant->update($data);

        $this->notifications->notifyAdmins(
            'تعديل بيانات مطعم يحتاج مراجعة',
            auth()->user()->name." عدّل بيانات {$restaurant->name}. راجع الصفحة وأيّد التعديل.",
            route('admin.restaurants.show', $restaurant)
        );

        return back()->with('success', 'تم حفظ التفاصيل. وصل إشعار للإدارة لمراجعة التعديل.');
    }

    public function resubmit(): RedirectResponse
    {
        $restaurant = $this->restaurant();

        abort_unless($restaurant->isRejected(), 403);

        $restaurant->update([
            'verification_status' => Restaurant::VERIFICATION_PENDING,
            'rejection_reason' => null,
            'is_active' => false,
        ]);

        $this->notifications->notifyAdmins(
            'إعادة إرسال مطعم للمراجعة',
            "{$restaurant->name} أعاد إرسال بياناته بعد الرفض وهو بانتظار التحقق.",
            route('admin.restaurants.show', $restaurant)
        );

        return redirect()->route('partner.dashboard')
            ->with('success', 'أُعيد إرسال مطعمك للمراجعة. الحالة الآن: جاري التحقق.');
    }

    private function validated(Request $request, Restaurant $restaurant): array
    {
        $request->merge([
            'phone' => PalestinianPhone::digits($request->input('phone')),
            'opens_at' => substr((string) $request->input('opens_at'), 0, 5),
            'closes_at' => substr((string) $request->input('closes_at'), 0, 5),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:restaurant,cafe'],
            'cuisine' => ['required', Rule::in(array_keys(config('brand.cuisines', [])))],
            'description' => ['required', 'string', 'min:20', 'max:1000'],
            'phone' => PalestinianPhone::rules(),
            'owner_national_id' => ['required', 'digits:9', Rule::unique('restaurants', 'owner_national_id')->ignore($restaurant->id)],
            'license_number' => ['nullable', 'string', 'max:50'],
            'area' => ['required', Rule::in(collect(config('brand.areas', []))->pluck('key')->all())],
            'address' => ['required', 'string', 'max:255'],
            'opens_at' => ['required', 'date_format:H:i'],
            'closes_at' => ['required', 'date_format:H:i', 'different:opens_at'],
            'image' => ['nullable', 'image', 'max:4096'],
        ], [
            'description.min' => 'اكتب وصفاً أوضح لمطعمك (20 حرفاً على الأقل).',
            ...PalestinianPhone::messages(),
            'owner_national_id.digits' => 'رقم الهوية يجب أن يتكون من 9 أرقام.',
            'owner_national_id.unique' => 'رقم الهوية مسجّل مسبقاً.',
            'closes_at.different' => 'ساعة الإغلاق يجب أن تختلف عن ساعة الافتتاح.',
        ]);

        unset($data['image']);

        return $data;
    }
}
