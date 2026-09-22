<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MembershipController extends Controller
{
    public function __construct(private MembershipService $memberships) {}

    public function index(): View
    {
        return view('memberships.index', [
            'memberships' => Membership::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'current' => auth()->user()?->activeSubscription(),
            'pending' => auth()->user()?->subscriptions()->where('status', 'pending')->with('membership')->first(),
        ]);
    }

    public function subscribe(Request $request, Membership $membership): RedirectResponse
    {
        abort_unless($membership->is_active, 404);

        $request->validate([
            'receipt' => ['required', 'image', 'max:4096'],
        ], [
            'receipt.required' => 'أرفق صورة إشعار حوالة الاشتراك.',
        ]);

        try {
            $this->memberships->request($request->user(), $membership, $request->file('receipt'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('memberships.index')
            ->with('success', 'تم إرسال طلب العضوية وهو بانتظار مراجعة الحوالة.');
    }

    public function requestCard(Request $request): RedirectResponse
    {
        $request->validate([
            'card_note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $message = $this->memberships->requestCard($request->user(), $request->input('card_note'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }
}
