<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\CourierPayout;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isCourierApproved(), 403);

        $period = $request->string('period', 'today')->toString();
        if (! in_array($period, ['today', 'yesterday', 'week', 'month', 'all'], true)) {
            $period = 'today';
        }

        $earnings = $user->courierEarningsForPeriod($period);
        $availableBalance = $user->courierAvailableBalance();
        $lifetimeNet = $user->courierLifetimeNetEarnings();
        $totalGross = $user->courierTotalGrossDeliveryFees();
        $lifetimePlatformFee = $user->courierLifetimePlatformFee();
        $totalWithdrawn = $user->courierTotalWithdrawn();
        $pendingPayouts = $user->courierPendingPayoutsAmount();
        $payouts = $user->courierPayouts()->latest()->paginate(15)->withQueryString();
        $payoutMethods = CourierPayout::METHODS;

        return view('courier.wallet', compact(
            'user',
            'period',
            'earnings',
            'availableBalance',
            'lifetimeNet',
            'totalGross',
            'lifetimePlatformFee',
            'totalWithdrawn',
            'pendingPayouts',
            'payouts',
            'payoutMethods'
        ));
    }

    public function requestPayout(Request $request, NotificationService $notifications): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isCourierApproved(), 403);

        $availableBalance = $user->courierAvailableBalance();

        if ($availableBalance <= 0) {
            return back()->with('error', 'لا يوجد رصيد متاح للسحب حالياً.');
        }

        $validMethods = implode(',', array_keys(CourierPayout::METHODS));

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$availableBalance],
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

        // Save default details for courier convenience
        $user->update([
            'payout_method' => $data['payout_method'],
            'payout_details' => $data['transfer_details'],
        ]);

        $payout = CourierPayout::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'status' => CourierPayout::STATUS_PENDING,
            'payout_method' => $data['payout_method'],
            'transfer_details' => $data['transfer_details'],
        ]);

        $notifications->notifyAdmins(
            'طلب سحب أرباح مندوب توصيل جديد 💰',
            "طلب المندوب {$user->name} سحب أرباح بقيمة {$payout->amount} ₪ عبر {$payout->methodLabel()} ({$payout->transfer_details}).",
            route('admin.delivery.index', ['tab' => 'payouts'])
        );

        return back()->with('success', 'تم إرسال طلب سحب الأرباح بنجاح. سيتم تحويل المبلغ وتأكيده من الإدارة قريباً.');
    }
}
