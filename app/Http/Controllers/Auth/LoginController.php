<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\PalestinianPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => PalestinianPhone::digits($request->input('phone')),
        ]);

        $credentials = $request->validate([
            'phone' => PalestinianPhone::rules(),
            'password' => ['required', 'string'],
        ], [
            ...PalestinianPhone::messages(),
            'password.required' => 'أدخل كلمة المرور.',
        ]);

        if (! Auth::attempt(['phone' => $credentials['phone'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'phone' => 'رقم الهاتف أو كلمة المرور غير صحيحة.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isRestaurantOwner()) {
            return redirect()->intended(route('partner.dashboard'));
        }

        if ($user->isCourier()) {
            return redirect()->intended(route('courier.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
