<?php

namespace App\Http\Controllers\Partner;

use App\Models\Restaurant;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\PalestinianPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController
{
    public function create(): View
    {
        return view('partner.register', [
            'cuisines' => config('brand.cuisines', []),
            'areas' => config('brand.areas', []),
        ]);
    }

    public function store(Request $request, NotificationService $notifications): RedirectResponse
    {
        $this->rememberRegistrationPassword($request);

        $request->merge([
            'phone' => PalestinianPhone::digits($request->input('phone')),
            'restaurant_phone' => PalestinianPhone::digits($request->input('restaurant_phone')),
            'opens_at' => substr((string) $request->input('opens_at'), 0, 5),
            'closes_at' => substr((string) $request->input('closes_at'), 0, 5),
        ]);

        $data = $request->validate([
            'owner_name' => ['required', 'string', 'max:120'],
            'phone' => PalestinianPhone::rules(uniqueUser: true),
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'owner_national_id' => ['required', 'digits:9', 'unique:restaurants,owner_national_id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'restaurant_name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:restaurant,cafe'],
            'cuisine' => ['required', Rule::in(array_keys(config('brand.cuisines', [])))],
            'description' => ['required', 'string', 'min:20', 'max:1000'],
            'restaurant_phone' => PalestinianPhone::rules(),
            'license_number' => ['nullable', 'string', 'max:50'],
            'area' => ['required', Rule::in(collect(config('brand.areas', []))->pluck('key')->all())],
            'address' => ['required', 'string', 'max:255'],
            'opens_at' => ['required', 'date_format:H:i'],
            'closes_at' => ['required', 'date_format:H:i', 'different:opens_at'],
            'image' => ['nullable', 'image', 'max:4096'],
            'terms' => ['accepted'],
        ], [
            'owner_name.required' => 'اسم صاحب المطعم مطلوب.',
            ...PalestinianPhone::messages(),
            'phone.required' => 'رقم هاتف صاحب المطعم مطلوب.',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً.',
            'owner_national_id.required' => 'رقم الهوية مطلوب.',
            'owner_national_id.digits' => 'رقم الهوية يجب أن يتكون من 9 أرقام.',
            'owner_national_id.unique' => 'رقم الهوية مسجّل مسبقاً.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'restaurant_name.required' => 'اسم المطعم مطلوب.',
            'cuisine.required' => 'اختر نوع المطبخ.',
            'description.required' => 'وصف المطعم مطلوب.',
            'description.min' => 'اكتب وصفاً أوضح لمطعمك (20 حرفاً على الأقل).',
            ...PalestinianPhone::messages('restaurant_phone'),
            'restaurant_phone.required' => 'هاتف المطعم مطلوب.',
            'area.required' => 'اختر منطقة المطعم.',
            'address.required' => 'عنوان المطعم مطلوب.',
            'opens_at.required' => 'حدد ساعة الافتتاح.',
            'closes_at.required' => 'حدد ساعة الإغلاق.',
            'closes_at.different' => 'ساعة الإغلاق يجب أن تختلف عن ساعة الافتتاح.',
            'terms.accepted' => 'يجب الموافقة على شروط الانضمام للمنصة.',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('restaurants', 'public')
            : null;

        $restaurant = DB::transaction(function () use ($data, $imagePath) {
            $user = User::create([
                'name' => $data['owner_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
                'role' => 'restaurant_owner',
            ]);

            return Restaurant::create([
                'owner_id' => $user->id,
                'name' => $data['restaurant_name'],
                'type' => $data['type'],
                'cuisine' => $data['cuisine'],
                'description' => $data['description'],
                'phone' => $data['restaurant_phone'],
                'owner_national_id' => $data['owner_national_id'],
                'license_number' => $data['license_number'] ?? null,
                'address' => $data['address'],
                'area' => $data['area'],
                'opens_at' => $data['opens_at'],
                'closes_at' => $data['closes_at'],
                'image_path' => $imagePath,
                'starts_at' => now()->toDateString(),
                'expires_at' => now()->addYear()->toDateString(),
                'is_active' => false,
                'is_featured' => false,
                'verification_status' => Restaurant::VERIFICATION_PENDING,
            ]);
        });

        $this->forgetRegistrationPassword($request);
        Auth::login($restaurant->owner);
        $request->session()->regenerate();

        $notifications->notifyAdmins(
            'طلب انضمام مطعم جديد',
            "{$restaurant->name} بانتظار المراجعة والتحقق.",
            route('admin.restaurants.show', $restaurant)
        );

        $notifications->notify(
            $restaurant->owner,
            'تم استلام طلب انضمامك',
            'حسابك جاهز. لازم تشترك وتختار باقة الظهور ثم ترفق إشعار الحوالة. بعد تأكيد الإدارة تقدر تضيف المنيو.',
            route('partner.subscription.index')
        );

        return redirect()->route('partner.subscription.index')
            ->with('success', 'تم إنشاء حساب المطعم. لازم تشترك أولاً: اختر الباقة وأرفق إشعار الحوالة.');
    }

    private function rememberRegistrationPassword(Request $request): void
    {
        if ($request->filled('password')) {
            $request->session()->put('partner_register.password', $request->input('password'));
            $request->session()->put(
                'partner_register.password_confirmation',
                $request->input('password_confirmation', $request->input('password'))
            );
        }

        if (! $request->filled('password') && $request->session()->has('partner_register.password')) {
            $request->merge([
                'password' => $request->session()->get('partner_register.password'),
                'password_confirmation' => $request->session()->get('partner_register.password_confirmation'),
            ]);
        }
    }

    private function forgetRegistrationPassword(Request $request): void
    {
        $request->session()->forget([
            'partner_register.password',
            'partner_register.password_confirmation',
        ]);
    }
}
