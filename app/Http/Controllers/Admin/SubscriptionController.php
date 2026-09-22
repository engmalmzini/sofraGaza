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

    public function receipt(MembershipSubscription $subscription)
    {
        return $subscription->receiptResponse();
    }

    public function approve(MembershipSubscription $subscription): RedirectResponse
    {
        try {
            $message = $this->memberships->approve($subscription);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    public function reject(Request $request, MembershipSubscription $subscription): RedirectResponse
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        try {
            $message = $this->memberships->reject($subscription, $request->rejection_reason);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    public function markCard(Request $request, MembershipSubscription $subscription): RedirectResponse
    {
        $request->validate([
            'card_status' => ['required', 'in:pending,ready,delivered'],
        ]);

        try {
            $message = $this->memberships->markCard($subscription, $request->string('card_status')->toString());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }
}
