<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PalestinianPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => PalestinianPhone::digits($request->input('phone')),
        ]);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'phone' => PalestinianPhone::rules(uniqueUser: true),
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'first_name.required' => 'الاسم الأول مطلوب.',
            'last_name.required' => 'اسم العائلة مطلوب.',
            ...PalestinianPhone::messages(),
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        $user = User::create([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'role' => 'customer',
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'تم إنشاء حسابك بنجاح. أهلاً بك في سفرة غزة.');
    }
}
