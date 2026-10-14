<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceExpense;
use App\Models\FinanceIncome;
use App\Models\Restaurant;
use App\Models\RestaurantBoost;
use App\Models\RestaurantPayout;
use App\Models\RestaurantSettlement;
use App\Services\FinanceService;
use App\Services\NotificationService;
use App\Support\Finance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FinanceController extends Controller
{
    public function __construct(private FinanceService $finance) {}

    public function index(Request $request): View
    {
        $period = $this->period($request);
        $previous = $this->finance->previousPeriod($period);
        $overview = $this->finance->overview($period['start'], $period['end'], $previous);
        $restaurants = Restaurant::query()->orderBy('name')->get(['id', 'name', 'type']);

        return view('admin.finance.index', [
            'period' => $period,
            'periodQuery' => $this->finance->queryFromPeriod($period),
            'overview' => $overview,
            'in' => $overview['in'],
            'out' => $overview['out'],
            'restaurants' => $restaurants,
            'boostRate' => Finance::BOOST_DAILY_RATE,
        ]);
    }

    public function orders(Request $request): View
    {
        $period = $this->period($request);
        $completed = $this->finance->completedOrdersQuery($period['start'], $period['end'])->paginate(30)->withQueryString();
        $completedTotals = $this->finance->orderTotals(
            $this->finance->completedOrdersQuery($period['start'], $period['end'])->get()
        );
        $incomplete = $this->finance->incompleteOrdersQuery($period['start'], $period['end'])->paginate(20, ['*'], 'lost')->withQueryString();
        $incompleteCollection = $this->finance->incompleteOrdersQuery($period['start'], $period['end'])->get();

        return view('admin.finance.orders', [
            'period' => $period,
            'periodQuery' => $this->finance->queryFromPeriod($period),
            'completed' => $completed,
            'totals' => $completedTotals,
            'incomplete' => $incomplete,
            'incompleteCount' => $incompleteCollection->count(),
            'incompleteValue' => round($incompleteCollection->sum(fn ($order) => (float) $order->total), 2),
        ]);
    }

    public function restaurants(Request $request): View
    {
        $period = $this->period($request);
        $rows = $this->finance->restaurantRows($period['start'], $period['end']);

        return view('admin.finance.restaurants', [
            'period' => $period,
            'periodQuery' => $this->finance->queryFromPeriod($period),
            'rows' => $rows,
            'pendingCount' => $rows->where('status', 'pending')->count(),
            'paidCount' => $rows->where('status', 'paid')->count(),
            'salesTotal' => round($rows->sum('sales'), 2),
            'commissionTotal' => round($rows->sum('commission'), 2),
            'netTotal' => round($rows->sum('net'), 2),
            'availableTotal' => round($rows->sum('available'), 2),
            'pendingPayoutTotal' => round($rows->sum('pending_payout'), 2),
        ]);
    }

    public function restaurant(Request $request, Restaurant $restaurant): View
    {
        $period = $this->period($request);
        $row = $this->finance->restaurantRow($restaurant, $period['start'], $period['end']);
        $orders = $this->finance->completedOrdersQuery($period['start'], $period['end'])
            ->where('restaurant_id', $restaurant->id)
            ->get();
        $history = $restaurant->settlements()->with('settler')->get();

        return view('admin.finance.restaurant', [
            'period' => $period,
            'periodQuery' => $this->finance->queryFromPeriod($period),
            'restaurant' => $restaurant,
            'row' => $row,
            'orders' => $orders,
            'history' => $history,
        ]);
    }

    public function settle(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $period = $this->period($request);
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->finance->settleRestaurant($restaurant, $period, $request->user(), $data['notes'] ?? null);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.finance.restaurants.show', array_merge(['restaurant' => $restaurant], $this->finance->queryFromPeriod($period)))
            ->with('success', 'تم إقفال حساب الفترة لـ '.$restaurant->name.' محاسبياً. هذا لا يحوّل فلوس؛ التحويل الفعلي من صفحة سحب المطاعم.');
    }

    public function destroySettlement(Request $request, RestaurantSettlement $settlement): RedirectResponse
    {
        $restaurant = $settlement->restaurant;
        $period = $this->period($request);
        $settlement->delete();

        return redirect()
            ->route('admin.finance.restaurants.show', array_merge(['restaurant' => $restaurant], $this->finance->queryFromPeriod($period)))
            ->with('success', 'تم حذف إقفال الفترة من الأرشيف.');
    }

    public function storeBoost(Request $request): RedirectResponse
    {
        $period = $this->period($request);
        $data = $request->validate([
            'restaurant_id' => ['required', 'exists:restaurants,id'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'daily_rate' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'title' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'banner' => ['nullable', 'image', 'max:6144'],
        ]);

        $start = \Carbon\Carbon::parse($data['starts_on'])->startOfDay();
        $end = \Carbon\Carbon::parse($data['ends_on'])->startOfDay();
        $rate = (float) ($data['daily_rate'] ?? Finance::BOOST_DAILY_RATE);
        $days = max(1, (int) round($start->diffInDays($end, true)) + 1);
        $bannerPath = $request->hasFile('banner')
            ? $request->file('banner')->store('boosts/banners', 'public')
            : null;

        RestaurantBoost::query()->create([
            'restaurant_id' => $data['restaurant_id'],
            'starts_on' => $start->toDateString(),
            'ends_on' => $end->toDateString(),
            'daily_rate' => $rate,
            'days' => $days,
            'amount' => round($days * $rate, 2),
            'title' => $data['title'] ?? 'حملة إعلان',
            'notes' => $data['notes'] ?? null,
            'banner_path' => $bannerPath,
            'status' => RestaurantBoost::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.finance.index', $this->finance->queryFromPeriod($period))
            ->with('success', 'تم تسجيل حملة الإعلان.');
    }

    public function destroyBoost(Request $request, RestaurantBoost $boost): RedirectResponse
    {
        $period = $this->period($request);
        $boost->delete();

        return redirect()
            ->route('admin.finance.index', $this->finance->queryFromPeriod($period))
            ->with('success', 'تم حذف حملة الإعلان.');
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $period = $this->period($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'spent_on' => ['required', 'date'],
            'category' => ['required', 'in:operating,gift,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        FinanceExpense::query()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.finance.index', $this->finance->queryFromPeriod($period))
            ->with('success', 'تم تسجيل المصروف.');
    }

    public function destroyExpense(Request $request, FinanceExpense $expense): RedirectResponse
    {
        $period = $this->period($request);
        $expense->delete();

        return redirect()
            ->route('admin.finance.index', $this->finance->queryFromPeriod($period))
            ->with('success', 'تم حذف المصروف.');
    }

    public function storeIncome(Request $request): RedirectResponse
    {
        $period = $this->period($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'received_on' => ['required', 'date'],
            'category' => ['required', 'in:setup,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        FinanceIncome::query()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.finance.index', $this->finance->queryFromPeriod($period))
            ->with('success', 'تم تسجيل الدخل الإضافي.');
    }

    public function destroyIncome(Request $request, FinanceIncome $income): RedirectResponse
    {
        $period = $this->period($request);
        $income->delete();

        return redirect()
            ->route('admin.finance.index', $this->finance->queryFromPeriod($period))
            ->with('success', 'تم حذف الدخل الإضافي.');
    }

    public function payouts(Request $request): View
    {
        $status = $request->string('payout_status')->toString();
        $query = RestaurantPayout::query()->with(['restaurant', 'processor'])->latest();

        if (in_array($status, ['pending', 'completed', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $payouts = $query->paginate(20)->withQueryString();
        $pendingCount = RestaurantPayout::query()->pending()->count();

        return view('admin.finance.payouts', [
            'payouts' => $payouts,
            'pendingCount' => $pendingCount,
            'status' => $status,
            'commissionLabel' => Finance::restaurantCommissionLabel(),
        ]);
    }

    public function completeRestaurantPayout(Request $request, RestaurantPayout $payout, NotificationService $notifications): RedirectResponse
    {
        if (! $payout->isPending()) {
            return back()->with('error', 'تمت معالجة هذا الطلب مسبقاً.');
        }

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
            'receipt' => ['required', 'image', 'max:6144'],
        ], [
            'receipt.required' => 'ارفع صورة إشعار التحويل قبل تأكيد الصرف.',
            'receipt.image' => 'إشعار التحويل يجب أن يكون صورة.',
            'receipt.max' => 'حجم صورة الإشعار يجب ألا يتجاوز 6 ميغابايت.',
        ]);

        $path = $request->file('receipt')->store('payouts/restaurants', 'public');

        $payout->update([
            'status' => RestaurantPayout::STATUS_COMPLETED,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'admin_notes' => $data['admin_notes'] ?? null,
            'transfer_receipt_path' => $path,
        ]);

        $restaurant = $payout->restaurant;
        if ($restaurant?->owner) {
            $formatted = number_format((float) $payout->amount, 2);
            $notes = $data['admin_notes'] ?? null;
            $notifications->notify(
                $restaurant->owner,
                'تم تحويل مستحقات المطعم',
                "تم تحويل مبلغ {$formatted} ₪ إلى حساب {$restaurant->name} ({$payout->methodLabel()}). يمكنك مراجعة إشعار التحويل من الرصيد والسحب.".($notes ? " ملاحظات: {$notes}" : ''),
                route('partner.wallet')
            );
        }

        return back()->with('success', "تم تأكيد تحويل {$payout->amount} ₪ للمطعم مع إشعار التحويل. خُصم المبلغ من الرصيد المتاح.");
    }

    public function restaurantPayoutReceipt(RestaurantPayout $payout): BinaryFileResponse
    {
        return $payout->receiptResponse();
    }

    public function rejectRestaurantPayout(Request $request, RestaurantPayout $payout, NotificationService $notifications): RedirectResponse
    {
        if (! $payout->isPending()) {
            return back()->with('error', 'تمت معالجة هذا الطلب مسبقاً.');
        }

        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'admin_notes.required' => 'يرجى كتابة سبب رفض طلب السحب.',
            'admin_notes.min' => 'سبب الرفض قصير جداً.',
        ]);

        $payout->update([
            'status' => RestaurantPayout::STATUS_REJECTED,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'admin_notes' => $data['admin_notes'],
        ]);

        $restaurant = $payout->restaurant;
        if ($restaurant?->owner) {
            $formatted = number_format((float) $payout->amount, 2);
            $notifications->notify(
                $restaurant->owner,
                'تم رفض طلب سحب المستحقات',
                "تم رفض طلب سحب {$formatted} ₪. السبب: {$data['admin_notes']}. المبلغ ما زال في رصيد المطعم.",
                route('partner.wallet')
            );
        }

        return back()->with('success', 'تم رفض طلب السحب. الرصيد ما زال متاحاً لأنه لم يُخصم إلا عند تأكيد التحويل.');
    }

    private function period(Request $request): array
    {
        return $this->finance->resolvePeriod(
            $request->string('period')->toString() ?: 'month',
            $request->input('date'),
            $request->input('month'),
            $request->input('from'),
            $request->input('to'),
        );
    }
}
