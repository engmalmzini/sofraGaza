<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        $user->load(['orders.restaurant', 'pointTransactions', 'subscriptions.membership']);

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
}
