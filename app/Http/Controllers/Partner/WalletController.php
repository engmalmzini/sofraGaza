<?php

namespace App\Http\Controllers\Partner;

use App\Models\RestaurantPayout;
use App\Services\NotificationService;
use App\Support\Finance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = $this->restaurant();
        $period = $this->resolvedPeriod($request);
        $earnings = $restaurant->earningsForPeriod($period);
        $availableBalance = $restaurant->availableBalance();
        $lifetimeNet = $restaurant->lifetimeNet();
        $totalWithdrawn = $restaurant->totalWithdrawn();
        $pendingPayouts = $restaurant->pendingPayoutsAmount();
        $payouts = $restaurant->payouts()->with('processor')->paginate(15)->withQueryString();
        $commissionLabel = Finance::restaurantCommissionLabel();
        $payoutMethods = RestaurantPayout::METHODS;
        $periodLabels = RestaurantPayout::PERIODS;
        $formPeriod = $period === 'yesterday' ? 'today' : $period;
        $withdrawAmount = min($earnings['remaining'], $availableBalance);
        if ($withdrawAmount <= 0 && $availableBalance > 0) {
            $formPeriod = 'all';
            $withdrawAmount = $availableBalance;
        }

        return view('partner.wallet', compact(
            'restaurant',
            'period',
            'earnings',
            'availableBalance',
            'lifetimeNet',
            'totalWithdrawn',
            'pendingPayouts',
            'payouts',
            'commissionLabel',
            'payoutMethods',
            'periodLabels',
            'withdrawAmount',
            'formPeriod',
        ));
    }

    public function requestPayout(Request $request, NotificationService $notifications): RedirectResponse
    {
        $restaurant = $this->restaurant();
        abort_unless($restaurant->isApproved() && ! $restaurant->panel_suspended, 403);

        if ($restaurant->hasPendingPayout()) {
            return back()->with('error', 'لديك طلب سحب قيد المراجعة. لن يُخصم الرصيد إلا بعد تأكيد الإدارة، ولا يمكن إرسال طلب جديد قبل البتّ في الطلب الحالي.');
        }

        $availableBalance = $restaurant->availableBalance();
        if ($availableBalance <= 0) {
            return back()->with('error', 'لا يوجد رصيد متاح للسحب حالياً.');
        }

        $validMethods = implode(',', array_keys(RestaurantPayout::METHODS));
        $validPeriods = implode(',', array_keys(RestaurantPayout::PERIODS));

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$availableBalance],
            'period' => ['required', 'string', 'in:'.$validPeriods],
            'payout_method' => ['required', 'string', 'in:'.$validMethods],
            'transfer_details' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'amount.required' => 'أدخل المبلغ المطلوب سحبه.',
            'amount.min' => 'الحد الأدنى لطلب السحب هو 1 ₪.',
            'amount.max' => 'المبلغ المطلوب أكبر من رصيدك المتاح ('.$availableBalance.' ₪).',
            'payout_method.required' => 'اختر طريقة التحويل.',
            'transfer_details.required' => 'أدخل بيانات التحويل (رقم المحفظة أو الحساب البنكي).',
            'transfer_details.min' => 'بيانات التحويل قصيرة جداً، يرجى كتابتها بالتفصيل.',
        ]);

        $periodRemaining = $restaurant->earningsForPeriod($data['period'])['remaining'];
        if ((float) $data['amount'] > $periodRemaining + 0.009) {
            return back()->withInput()->with('error', 'المبلغ أكبر من المتبقي لهذه الفترة ('.number_format($periodRemaining, 2).' ₪).');
        }

        $restaurant->update([
            'payout_method' => $data['payout_method'],
            'payout_details' => $data['transfer_details'],
        ]);

        $payout = RestaurantPayout::query()->create([
            'restaurant_id' => $restaurant->id,
            'amount' => $data['amount'],
            'status' => RestaurantPayout::STATUS_PENDING,
            'period' => $data['period'],
            'payout_method' => $data['payout_method'],
            'transfer_details' => $data['transfer_details'],
        ]);

        $notifications->notifyAdmins(
            'طلب سحب رصيد مطعم جديد',
            "طلب {$restaurant->name} سحب {$payout->amount} ₪ ({$payout->periodLabel()}) عبر {$payout->methodLabel()}.",
            route('admin.finance.payouts')
        );

        return back()->with('success', 'تم إرسال طلب السحب. سيبقى الرصيد ظاهراً حتى تؤكد الإدارة التحويل.');
    }

    private function resolvedPeriod(Request $request): string
    {
        $period = $request->string('period', 'today')->toString();

        return in_array($period, ['today', 'yesterday', 'week', 'month', 'all'], true)
            ? $period
            : 'today';
    }
}
