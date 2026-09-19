<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->orderBy('id')->get();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $values = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:50'],
        ]);

        foreach ($values['settings'] as $key => $value) {
            Setting::query()->where('key', $key)->update(['value' => $value]);
        }

        Setting::forgetCache();

        return back()->with('success', 'تم حفظ إعدادات المنصة.');
    }
}
