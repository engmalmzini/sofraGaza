<?php

namespace App\Http\Controllers\Courier;

use App\Models\User;
use App\Services\NotificationService;
use App\Support\PalestinianPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController
{
    public function create(): View
    {
        return view('courier.register');
    }

    public function store(Request $request, NotificationService $notifications): RedirectResponse
    {
        $this->rememberRegistrationPassword($request);

        $request->merge([
            'phone' => PalestinianPhone::digits($request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => PalestinianPhone::rules(uniqueUser: true),
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'bike_type' => ['required', 'in:bicycle,electric'],
            'photo' => ['required', 'image', 'max:4096'],
            'bike_photo' => ['required', 'image', 'max:4096'],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'الاسم مطلوب.',
            ...PalestinianPhone::messages(),
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'bike_type.required' => 'حدد نوع الدراجة.',
            'photo.required' => 'صورة شخصية مطلوبة.',
            'bike_photo.required' => 'صورة الدراجة مطلوبة.',
            'terms.accepted' => 'يجب الموافقة على مراجعة الإدارة قبل تفعيل الحساب.',
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'courier',
            'bike_type' => $data['bike_type'],
            'photo_path' => $request->file('photo')->store('couriers', 'public'),
            'bike_photo_path' => $request->file('bike_photo')->store('couriers', 'public'),
            'courier_status' => User::COURIER_PENDING,
        ]);

        $this->forgetRegistrationPassword($request);
        Auth::login($user);
        $request->session()->regenerate();

        $notifications->notifyAdmins(
            'طلب انضمام مندوب توصيل',
            "{$user->name} أرسل طلب انضمام كمندوب ({$user->bikeTypeLabel()}). بانتظار القبول أو الرفض.",
            route('admin.delivery.show', $user)
        );

        $notifications->notify(
            $user,
            'تم استلام طلبك',
            'طلب انضمامك كمندوب توصيل قيد المراجعة. بعد الموافقة يصلك إشعار برابط لوحة التحكم.',
            route('courier.dashboard')
        );

        return redirect()->route('courier.dashboard')
            ->with('success', 'تم إرسال طلبك. حالتك الآن: جاري التحقق.');
    }

    private function rememberRegistrationPassword(Request $request): void
    {
        if ($request->filled('password')) {
            $request->session()->put('courier_register.password', $request->input('password'));
            $request->session()->put(
                'courier_register.password_confirmation',
                $request->input('password_confirmation', $request->input('password'))
            );
        }

        if (! $request->filled('password') && $request->session()->has('courier_register.password')) {
            $request->merge([
                'password' => $request->session()->get('courier_register.password'),
                'password_confirmation' => $request->session()->get('courier_register.password_confirmation'),
            ]);
        }
    }

    private function forgetRegistrationPassword(Request $request): void
    {
        $request->session()->forget([
            'courier_register.password',
            'courier_register.password_confirmation',
        ]);
    }
}
