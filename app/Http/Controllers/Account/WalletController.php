<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class WalletController extends Controller
{
    public function __construct(
        private WalletService $wallet,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $transactions = $user->walletTransactions()->paginate(15);
        $pendingTopups = $user->walletTopups()->where('status', 'pending')->get();

        return view('account.wallet.index', [
            'user' => $user,
            'transactions' => $transactions,
            'pendingTopups' => $pendingTopups,
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        $paymentAccounts = Setting::paymentAccounts();

        return view('account.wallet.topup', [
            'user' => $user,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:5', 'max:5000'],
            'payment_method' => ['required', 'string', 'in:jawwal_pay,palpay,bank'],
            'receipt' => ['required', 'image', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.required' => 'أدخل المبلغ المراد شحنه.',
            'amount.min' => 'الحد الأدنى لشحن الرصيد هو 5 شواكل.',
            'receipt.required' => 'أرفق صورة إشعار الحوالة لتأكيد الدفع.',
            'receipt.image' => 'ملف إشعار الحوالة يجب أن يكون صورة.',
        ]);

        try {
            $this->wallet->requestTopup(
                $request->user(),
                (float) $data['amount'],
                $request->file('receipt'),
                $data['payment_method'],
                $data['notes'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('account.wallet')
            ->with('success', 'تم إرسال إشعار الحوالة بنجاح. سيتم تدقيق العملية وإضافة الرصيد إلى محفظتك في أقرب وقت.');
    }
}
