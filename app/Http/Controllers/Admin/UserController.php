<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PointsService;
use App\Services\SafeDeletion;
use App\Support\PalestinianPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->where('role', 'customer')->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($b) => $b->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['orders.restaurant', 'pointTransactions', 'subscriptions.membership', 'walletTransactions', 'walletTopups']);

        return view('admin.users.show', compact('user'));
    }

    public function adjustPoints(Request $request, User $user, PointsService $points): RedirectResponse
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:200'],
        ]);

        $points->adjust($user, (int) $data['points'], $data['reason']);

        return back()->with('success', 'تم تعديل رصيد النقاط.');
    }

    public function adjustWallet(Request $request, User $user, \App\Services\WalletService $wallet): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0', 'min:-10000', 'max:10000'],
            'reason' => ['required', 'string', 'max:200'],
        ], [
            'amount.required' => 'أدخل قيمة المبلغ المراد إضافته أو خصمه.',
            'reason.required' => 'أدخل سبب التعديل للتوضيح للزبون.',
        ]);

        try {
            $wallet->adjustBalance($user, (float) $data['amount'], $data['reason'], auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تعديل رصيد المحفظة بنجاح.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);

        $request->merge(['phone' => PalestinianPhone::local($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => array_merge(PalestinianPhone::customerRules(), [Rule::unique('users', 'phone')->ignore($user->id)]),
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
        ], PalestinianPhone::customerMessages());

        $data['email'] = $data['email'] ?: null;
        $user->update($data);

        return back()->with('success', 'تم تحديث بيانات الحساب.');
    }

    public function destroy(User $user, SafeDeletion $deletion): RedirectResponse
    {
        $wasCourier = $user->isCourier();

        try {
            $deletion->deleteUser($user, auth()->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route($wasCourier ? 'admin.delivery.index' : 'admin.users.index')
            ->with('success', 'تم حذف الحساب.');
    }
}
