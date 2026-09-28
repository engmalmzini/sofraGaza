<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\PhoneVerificationException;
use App\Exceptions\SmsDeliveryException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomerPhoneVerification;
use App\Support\PalestinianPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create(CustomerPhoneVerification $verification)
    {
        return view('auth.register', [
            'step' => $verification->step(),
            'verifiedPhone' => $verification->phone(),
        ]);
    }

    public function sendCode(Request $request, CustomerPhoneVerification $verification)
    {
        $request->merge([
            'phone' => PalestinianPhone::digits($request->input('phone')),
        ]);

        $data = $request->validate([
            'phone' => PalestinianPhone::customerRules(uniqueUser: true),
        ], PalestinianPhone::customerMessages());

        return $this->deliverCode($verification, $data['phone'], $request->ip());
    }

    public function resendCode(Request $request, CustomerPhoneVerification $verification)
    {
        $phone = $verification->phone();

        if ($phone === null || $verification->verifiedPhone() !== null) {
            return redirect()->route('register');
        }

        return $this->deliverCode($verification, $phone, $request->ip());
    }

    public function resetPhone(CustomerPhoneVerification $verification)
    {
        $verification->clear();

        return redirect()->route('register');
    }

    public function verifyCode(Request $request, CustomerPhoneVerification $verification)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'أدخل رمز التحقق.',
            'code.digits' => 'رمز التحقق يجب أن يتكون من 6 أرقام.',
        ]);

        try {
            $verification->confirm($data['code']);
        } catch (PhoneVerificationException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('register')->with('success', 'تم التحقق من رقم الهاتف. أكمل بيانات الحساب.');
    }

    public function store(Request $request, CustomerPhoneVerification $verification)
    {
        $phone = $verification->verifiedPhone();

        if ($phone === null) {
            return redirect()->route('register')->with('error', 'تحقق من رقم الهاتف أولاً.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'الاسم مطلوب.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        if (User::query()->where('phone', $phone)->exists()) {
            $verification->clear();

            return redirect()->route('register')->with('error', 'رقم الهاتف مسجّل مسبقاً.');
        }

        $user = User::create([
            'name' => trim($data['name']),
            'phone' => $phone,
            'email' => null,
            'password' => $data['password'],
            'role' => 'customer',
        ]);

        $verification->clear();

        Auth::login($user);

        return redirect()->route('home')->with('success', 'تم إنشاء حسابك بنجاح. أهلاً بك في سفرة غزة.');
    }

    private function deliverCode(CustomerPhoneVerification $verification, string $phone, ?string $ip)
    {
        try {
            $verification->issue($phone, $ip);
        } catch (PhoneVerificationException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (SmsDeliveryException) {
            return back()->withInput()->with('error', 'تعذر إرسال رسالة التحقق الآن. حاول مرة أخرى بعد قليل.');
        }

        return redirect()->route('register')->with('success', 'أرسلنا رمز التحقق المكوّن من 6 أرقام إلى جوالك.');
    }
}
