<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTopup;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class WalletTopupController extends Controller
{
    public function __construct(
        private WalletService $wallet,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = WalletTopup::query()->with('user', 'reviewer')->latest();

        if ($status && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $topups = $query->paginate(20)->withQueryString();
        $counts = [
            'all' => WalletTopup::query()->count(),
            'pending' => WalletTopup::query()->where('status', 'pending')->count(),
            'approved' => WalletTopup::query()->where('status', 'approved')->count(),
            'rejected' => WalletTopup::query()->where('status', 'rejected')->count(),
        ];

        return view('admin.wallet-topups.index', [
            'topups' => $topups,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function receipt(WalletTopup $topup): Response
    {
        return $topup->receiptResponse();
    }

    public function approve(WalletTopup $topup): RedirectResponse
    {
        try {
            $this->wallet->approveTopup($topup, auth()->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "تمت الموافقة على شحن {$topup->amount} ₪ لحساب {$topup->user->name} بنجاح.");
    }

    public function reject(Request $request, WalletTopup $topup): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'أدخل سبب الرفض للتوضيح للزبون.',
        ]);

        try {
            $this->wallet->rejectTopup($topup, auth()->user(), $data['rejection_reason']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم رفض طلب شحن الرصيد وإشعار الزبون.');
    }
}
