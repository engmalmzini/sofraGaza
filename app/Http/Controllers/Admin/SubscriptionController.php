<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipSubscription;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SubscriptionController extends Controller
{
    public function __construct(private MembershipService $memberships) {}

    public function index(Request $request): View
    {
        $query = MembershipSubscription::query()->with(['user', 'membership'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"))
                    ->orWhereHas('membership', fn ($membership) => $membership->where('name', 'like', "%{$q}%"));
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function show(MembershipSubscription $subscription): View
    {
        $subscription->load(['user', 'membership']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function approve(MembershipSubscription $subscription): RedirectResponse
    {
        try {
            $this->memberships->approve($subscription);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تفعيل العضوية لمدة 30 يوماً.');
    }

    public function reject(Request $request, MembershipSubscription $subscription): RedirectResponse
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        try {
            $this->memberships->reject($subscription, $request->rejection_reason);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم رفض طلب العضوية.');
    }
}
