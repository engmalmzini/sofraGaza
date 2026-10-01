<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminAccess;
use App\Support\PalestinianPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->orderByDesc('is_super_admin')
            ->orderBy('name')
            ->get();

        return view('admin.team.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.team.form', [
            'adminUser' => null,
            'modules' => AdminAccess::MODULES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        User::query()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'role' => 'admin',
            'is_super_admin' => false,
            'admin_active' => true,
            'admin_permissions' => $data['permissions'],
        ]);

        return redirect()->route('admin.team.index')->with('success', 'تمت إضافة '.$data['name'].' لفريق الإدارة.');
    }

    public function edit(User $adminUser): View
    {
        abort_unless($adminUser->isAdmin(), 404);

        return view('admin.team.form', [
            'adminUser' => $adminUser,
            'modules' => AdminAccess::MODULES,
        ]);
    }

    public function update(Request $request, User $adminUser): RedirectResponse
    {
        abort_unless($adminUser->isAdmin(), 404);

        $data = $this->validated($request, $adminUser);
        $payload = [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'admin_active' => $request->boolean('admin_active'),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        if ($adminUser->isSuperAdmin()) {
            if (! $payload['admin_active']) {
                if ($adminUser->id === auth()->id()) {
                    return back()->with('error', 'ما بتقدر توقف حسابك أنت.');
                }

                if (User::query()->where('role', 'admin')->where('is_super_admin', true)->where('admin_active', true)->where('id', '!=', $adminUser->id)->doesntExist()) {
                    return back()->with('error', 'لازم يبقى مدير أعلى واحد على الأقل.');
                }
            }
        } else {
            $payload['admin_permissions'] = $data['permissions'];
            $payload['is_super_admin'] = false;
        }

        if ($request->boolean('make_super') && $adminUser->id !== auth()->id()) {
            $payload['is_super_admin'] = true;
            $payload['admin_permissions'] = null;
            $payload['admin_active'] = true;
        }

        $adminUser->update($payload);

        return redirect()->route('admin.team.index')->with('success', 'تم حفظ صلاحيات '.$adminUser->name.'.');
    }

    public function destroy(User $adminUser): RedirectResponse
    {
        abort_unless($adminUser->isAdmin(), 404);

        if ($adminUser->id === auth()->id()) {
            return back()->with('error', 'ما بتقدر توقف حسابك أنت.');
        }

        if ($adminUser->isSuperAdmin() && User::query()->where('role', 'admin')->where('is_super_admin', true)->where('admin_active', true)->count() <= 1) {
            return back()->with('error', 'لازم يبقى مدير أعلى واحد على الأقل.');
        }

        $adminUser->update(['admin_active' => false]);

        return back()->with('success', 'تم إيقاف صلاحيات '.$adminUser->name.'.');
    }

    private function validated(Request $request, ?User $adminUser): array
    {
        $request->merge([
            'phone' => PalestinianPhone::local($request->input('phone')),
        ]);
        $moduleKeys = array_keys(AdminAccess::MODULES);
        $phoneRules = PalestinianPhone::rules();
        $phoneRules[] = Rule::unique('users', 'phone')->ignore($adminUser?->id);

        $rules = [
            'name' => ['required', 'string', 'max:80'],
            'phone' => $phoneRules,
            'email' => ['nullable', 'email', 'max:120', Rule::unique('users', 'email')->ignore($adminUser?->id)],
            'password' => [$adminUser ? 'nullable' : 'required', 'string', 'min:6'],
            'permissions' => [$adminUser?->isSuperAdmin() ? 'nullable' : 'required', 'array', 'min:1'],
            'permissions.*' => ['in:'.implode(',', $moduleKeys)],
        ];

        $data = $request->validate($rules, [
            'name.required' => 'اسم المدير مطلوب.',
            'password.required' => 'عيّن كلمة مرور للحساب.',
            'permissions.required' => 'حدّد الأقسام اللي يقدر يتابعها.',
            'permissions.min' => 'حدّد قسم واحد على الأقل (مثلاً المطاعم).',
            ...PalestinianPhone::messages(),
        ]);

        $data['phone'] = PalestinianPhone::local($data['phone']);
        $data['permissions'] = array_values(array_unique($data['permissions'] ?? []));

        return $data;
    }
}
