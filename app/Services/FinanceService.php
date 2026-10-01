<?php

namespace App\Services;

use App\Models\CourierPayout;
use App\Models\FinanceExpense;
use App\Models\FinanceIncome;
use App\Models\Membership;
use App\Models\MembershipSubscription;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantBoost;
use App\Models\RestaurantSettlement;
use App\Models\User;
use App\Support\Finance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class FinanceService
{
    public function resolvePeriod(
        string $period = 'month',
        ?string $date = null,
        ?string $month = null,
        ?string $from = null,
        ?string $to = null,
    ): array {
        $period = in_array($period, ['day', 'week', 'month', 'custom'], true) ? $period : 'month';
        $now = now();

        if ($period === 'custom') {
            $start = $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth();
            $end = $to ? Carbon::parse($to)->endOfDay() : $now->copy()->endOfDay();

            if ($end->lt($start)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return $this->periodPayload(
                'custom',
                $start->format('Y/m/d').' — '.$end->format('Y/m/d'),
                $start,
                $end,
            );
        }

        if ($period === 'day') {
            $start = $date ? Carbon::parse($date)->startOfDay() : $now->copy()->startOfDay();

            return $this->periodPayload(
                'day',
                'يومي · '.$start->format('Y/m/d'),
                $start,
                $start->copy()->endOfDay(),
            );
        }

        if ($period === 'week') {
            $anchor = $date ? Carbon::parse($date) : $now;
            $start = $anchor->copy()->startOfWeek();
            $end = $anchor->copy()->endOfWeek();

            return $this->periodPayload(
                'week',
                'أسبوعي · '.$start->format('Y/m/d').' — '.$end->format('Y/m/d'),
                $start,
                $end,
            );
        }

        $anchor = $month && preg_match('/^\d{4}-\d{2}$/', $month)
            ? Carbon::parse($month.'-01')->startOfMonth()
            : $now->copy()->startOfMonth();

        return $this->periodPayload(
            'month',
            'شهري · '.$anchor->format('Y/m'),
            $anchor->copy()->startOfMonth(),
            $anchor->copy()->endOfMonth(),
        );
    }

    public function previousPeriod(array $period): array
    {
        $key = $period['key'] ?? 'month';
        $start = $period['start'];

        if ($key === 'month') {
            $prev = $start->copy()->subMonthNoOverflow()->startOfMonth();

            return $this->periodPayload(
                'month',
                'الشهر السابق · '.$prev->format('Y/m'),
                $prev,
                $prev->copy()->endOfMonth(),
            );
        }

        if ($key === 'day') {
            $prev = $start->copy()->subDay()->startOfDay();

            return $this->periodPayload(
                'day',
                'اليوم السابق · '.$prev->format('Y/m/d'),
                $prev,
                $prev->copy()->endOfDay(),
            );
        }

        if ($key === 'week') {
            $prevStart = $start->copy()->subWeek()->startOfWeek();

            return $this->periodPayload(
                'week',
                'الأسبوع السابق · '.$prevStart->format('Y/m/d').' — '.$prevStart->copy()->endOfWeek()->format('Y/m/d'),
                $prevStart,
                $prevStart->copy()->endOfWeek(),
            );
        }

        $seconds = max(1, $start->diffInSeconds($period['end']));
        $prevEnd = $start->copy()->subSecond();
        $prevStart = $prevEnd->copy()->subSeconds($seconds);

        return $this->periodPayload(
            'custom',
            'الفترة السابقة · '.$prevStart->format('Y/m/d').' — '.$prevEnd->format('Y/m/d'),
            $prevStart,
            $prevEnd,
        );
    }

    public function queryFromPeriod(array $period): array
    {
        return array_filter($period['query'] ?? [], fn ($value) => filled($value));
    }

    public function completedOrdersQuery(Carbon $start, Carbon $end): Builder
    {
        return Order::query()
            ->with(['user', 'restaurant', 'courier'])
            ->purchase()
            ->deliveredIn($start, $end)
            ->latest('id');
    }

    public function incompleteOrdersQuery(Carbon $start, Carbon $end): Builder
    {
        return Order::query()
            ->with(['user', 'restaurant'])
            ->incompleteIn($start, $end)
            ->latest('id');
    }

    public function orderTotals(Collection $orders): array
    {
        $sales = round($orders->sum(fn (Order $order) => $order->foodTotal()), 2);
        $commission = round($orders->sum(fn (Order $order) => $order->platformCommission()), 2);
        $delivery = round($orders->sum(fn (Order $order) => (float) $order->delivery_fee), 2);
        $restaurantNet = round($orders->sum(fn (Order $order) => $order->restaurantNet()), 2);
        $courierShare = round($orders->sum(fn (Order $order) => $order->courierFinanceShare()), 2);

        return [
            'count' => $orders->count(),
            'sales' => $sales,
            'commission' => $commission,
            'delivery' => $delivery,
            'restaurant_net' => $restaurantNet,
            'courier_share' => $courierShare,
        ];
    }

    public function restaurantRows(Carbon $start, Carbon $end): Collection
    {
        $orders = $this->completedOrdersQuery($start, $end)->get();
        $grouped = $orders->groupBy('restaurant_id');
        $ids = $grouped->keys()->filter()->all();

        $history = RestaurantSettlement::query()
            ->whereIn('restaurant_id', $ids)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('restaurant_id');

        $current = RestaurantSettlement::query()
            ->whereIn('restaurant_id', $ids)
            ->whereDate('period_start', $start->toDateString())
            ->whereDate('period_end', $end->toDateString())
            ->get()
            ->keyBy('restaurant_id');

        return $grouped->map(function (Collection $list) use ($history, $current) {
            $restaurant = $list->first()->restaurant;
            $sales = round($list->sum(fn (Order $order) => $order->foodTotal()), 2);
            $commission = round($list->sum(fn (Order $order) => $order->platformCommission()), 2);
            $delivery = round($list->sum(fn (Order $order) => (float) $order->delivery_fee), 2);
            $net = round($sales - $commission, 2);
            $periodSettlement = $current->get($restaurant->id);
            $archive = $history->get($restaurant->id, collect());

            return [
                'restaurant' => $restaurant,
                'orders_count' => $list->count(),
                'sales' => $sales,
                'commission' => $commission,
                'delivery' => $delivery,
                'net' => $net,
                'settlement' => $periodSettlement,
                'status' => $periodSettlement ? 'paid' : 'pending',
                'last_settled_at' => $archive->first()?->paid_at,
                'history' => $archive,
            ];
        })->sortByDesc('sales')->values();
    }

    public function restaurantRow(Restaurant $restaurant, Carbon $start, Carbon $end): ?array
    {
        return $this->restaurantRows($start, $end)
            ->first(fn (array $row) => $row['restaurant']->id === $restaurant->id);
    }

    public function moneyIn(Carbon $start, Carbon $end): array
    {
        $completed = $this->completedOrdersQuery($start, $end)->get();
        $commission = round($completed->sum(fn (Order $order) => $order->platformCommission()), 2);
        $memberships = $this->membershipIncome($start, $end);
        $boosts = RestaurantBoost::query()->with('restaurant')->overlapping($start, $end)->latest('starts_on')->get();
        $boostTotal = round($boosts->sum(fn (RestaurantBoost $boost) => $boost->incomeIn($start, $end)), 2);
        $extras = FinanceIncome::query()->inPeriod($start, $end)->latest('received_on')->get();
        $extraTotal = round($extras->sum(fn (FinanceIncome $income) => (float) $income->amount), 2);

        $rows = [
            [
                'key' => 'commission',
                'source' => 'دخل الطلبات',
                'detail' => '+10% عمولة المنصة من قيمة كل طلب مكتمل',
                'amount' => $commission,
            ],
        ];

        foreach ($memberships['rows'] as $row) {
            $rows[] = $row;
        }

        $rows[] = [
            'key' => 'boost',
            'source' => 'دخل الإعلانات',
            'detail' => 'حملات Boost النشطة والمنتهية ('.number_format(Finance::BOOST_DAILY_RATE, 0).' ₪ لليوم)',
            'amount' => $boostTotal,
        ];

        foreach ($extras as $income) {
            $rows[] = [
                'key' => 'extra-'.$income->id,
                'source' => $income->title,
                'detail' => $income->categoryLabel().($income->notes ? ' — '.$income->notes : ''),
                'amount' => (float) $income->amount,
            ];
        }

        return [
            'rows' => $rows,
            'total' => round(collect($rows)->sum('amount'), 2),
            'commission' => $commission,
            'memberships' => $memberships,
            'boosts' => $boosts,
            'boost_total' => $boostTotal,
            'extras' => $extras,
            'extra_total' => $extraTotal,
        ];
    }

    public function moneyOut(Carbon $start, Carbon $end): array
    {
        $completed = $this->completedOrdersQuery($start, $end)->get();
        $courier = round($completed->sum(fn (Order $order) => $order->courierFinanceShare()), 2);
        $gifts = $this->giftOrders($start, $end);
        $giftFromOrders = round($gifts->sum(fn (Order $order) => $order->giftCost()), 2);
        $expenses = FinanceExpense::query()->inPeriod($start, $end)->latest('spent_on')->get();
        $giftManual = round($expenses->where('category', 'gift')->sum(fn (FinanceExpense $expense) => (float) $expense->amount), 2);
        $operating = round($expenses->where('category', '!=', 'gift')->sum(fn (FinanceExpense $expense) => (float) $expense->amount), 2);
        $giftTotal = round($giftFromOrders + $giftManual, 2);

        $rows = [
            [
                'key' => 'couriers',
                'source' => 'مصروف الكباتن',
                'detail' => '−15% من قيمة كل طلب مكتمل',
                'amount' => $courier,
            ],
            [
                'key' => 'gifts',
                'source' => 'مصروف مزايا الأعضاء',
                'detail' => 'تكلفة الهدايا الشهرية لفئة 100',
                'amount' => $giftTotal,
            ],
            [
                'key' => 'operating',
                'source' => 'مصاريف تشغيلية أخرى',
                'detail' => 'استضافة، تصميم، طباعة بطاقات، وغيرها (تُدخل يدوياً)',
                'amount' => $operating,
            ],
        ];

        return [
            'rows' => $rows,
            'total' => round(collect($rows)->sum('amount'), 2),
            'courier' => $courier,
            'gifts' => $gifts,
            'gift_orders_total' => $giftFromOrders,
            'gift_manual_total' => $giftManual,
            'gift_total' => $giftTotal,
            'expenses' => $expenses,
            'operating' => $operating,
        ];
    }

    public function overview(Carbon $start, Carbon $end, ?array $previousPeriod = null): array
    {
        $in = $this->moneyIn($start, $end);
        $out = $this->moneyOut($start, $end);
        $completed = $this->completedOrdersQuery($start, $end)->get();
        $incomplete = $this->incompleteOrdersQuery($start, $end)->get();
        $net = round($in['total'] - $out['total'], 2);
        $comparison = null;

        if ($previousPeriod) {
            $prevIn = $this->moneyIn($previousPeriod['start'], $previousPeriod['end']);
            $prevOut = $this->moneyOut($previousPeriod['start'], $previousPeriod['end']);
            $prevNet = round($prevIn['total'] - $prevOut['total'], 2);
            $comparison = [
                'label' => $previousPeriod['label'],
                'kind' => ($previousPeriod['key'] ?? '') === 'month' ? 'month' : 'period',
                'in' => $this->delta($in['total'], $prevIn['total']),
                'out' => $this->delta($out['total'], $prevOut['total']),
                'net' => $this->delta($net, $prevNet),
            ];
        }

        return [
            'in' => $in,
            'out' => $out,
            'net' => $net,
            'completed_count' => $completed->count(),
            'completed_sales' => round($completed->sum(fn (Order $order) => $order->foodTotal()), 2),
            'completed_orders' => $completed,
            'incomplete_count' => $incomplete->count(),
            'incomplete_value' => round($incomplete->sum(fn (Order $order) => (float) $order->total), 2),
            'couriers' => $this->courierRows($completed, $start, $end),
            'comparison' => $comparison,
        ];
    }

    public function courierRows(Collection $orders, Carbon $start, Carbon $end): Collection
    {
        $grouped = $orders->groupBy(fn (Order $order) => $order->courier_id ?: 0);
        $courierIds = $grouped->keys()->filter()->all();
        $couriers = $courierIds
            ? User::query()->whereIn('id', $courierIds)->get()->keyBy('id')
            : collect();

        $payouts = CourierPayout::query()
            ->whereIn('user_id', $courierIds ?: [0])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end])
                    ->orWhereBetween('processed_at', [$start, $end]);
            })
            ->get()
            ->groupBy('user_id');

        $pendingAll = CourierPayout::query()
            ->pending()
            ->whereIn('user_id', $courierIds ?: [0])
            ->get()
            ->groupBy('user_id');

        return $grouped->map(function (Collection $list, $courierId) use ($couriers, $payouts, $pendingAll) {
            $dues = round($list->sum(fn (Order $order) => $order->courierFinanceShare()), 2);
            $courierPayouts = $payouts->get((int) $courierId, collect());
            $paid = round($courierPayouts->where('status', CourierPayout::STATUS_COMPLETED)->sum('amount'), 2);
            $pending = round($pendingAll->get((int) $courierId, collect())->sum('amount'), 2);
            $remaining = round(max(0, $dues - $paid), 2);

            if ($pending > 0) {
                $status = 'قيد المراجعة';
                $status_key = 'pending';
            } elseif ($dues <= 0) {
                $status = 'لا مستحقات';
                $status_key = 'none';
            } elseif ($paid >= $dues) {
                $status = 'تم التحويل';
                $status_key = 'paid';
            } elseif ($paid > 0) {
                $status = 'مدفوع جزئياً';
                $status_key = 'partial';
            } else {
                $status = 'مستحق غير مدفوع';
                $status_key = 'unpaid';
            }

            $courier = $couriers->get((int) $courierId);

            return [
                'courier' => $courier,
                'name' => $courier?->name ?: 'بدون مندوب معيّن',
                'orders_count' => $list->count(),
                'dues' => $dues,
                'paid' => $paid,
                'pending' => $pending,
                'remaining' => $remaining,
                'status' => $status,
                'status_key' => $status_key,
            ];
        })->sortByDesc('dues')->values();
    }

    public function delta(float $current, float $previous): array
    {
        $diff = round($current - $previous, 2);
        $percent = abs($previous) < 0.001
            ? ($current == 0.0 ? 0.0 : 100.0)
            : round(($diff / abs($previous)) * 100, 1);

        return [
            'previous' => round($previous, 2),
            'diff' => $diff,
            'percent' => $percent,
            'up' => $diff >= 0,
        ];
    }

    public function settleRestaurant(Restaurant $restaurant, array $period, User $admin, ?string $notes = null): RestaurantSettlement
    {
        $existing = RestaurantSettlement::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('period_start', $period['start']->toDateString())
            ->whereDate('period_end', $period['end']->toDateString())
            ->first();

        if ($existing) {
            return $existing;
        }

        $row = $this->restaurantRow($restaurant, $period['start'], $period['end']);

        if (! $row) {
            throw new RuntimeException('لا توجد مبيعات مكتملة لهذا المطعم في الفترة المحددة.');
        }

        return RestaurantSettlement::query()->create([
            'restaurant_id' => $restaurant->id,
            'settled_by' => $admin->id,
            'period_start' => $period['start']->toDateString(),
            'period_end' => $period['end']->toDateString(),
            'sales_total' => $row['sales'],
            'commission_total' => $row['commission'],
            'net_total' => $row['net'],
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function membershipIncome(Carbon $start, Carbon $end): array
    {
        $subscriptions = MembershipSubscription::query()
            ->with('membership')
            ->where('status', 'approved')
            ->where('starts_at', '<=', $end)
            ->where(function ($query) use ($start) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $start);
            })
            ->get();

        $plans = Membership::query()->orderBy('sort_order')->orderBy('monthly_price')->get();
        $rows = [];
        $total = 0.0;

        foreach ($plans as $plan) {
            $count = $subscriptions->where('membership_id', $plan->id)->unique('user_id')->count();
            $price = (float) $plan->monthly_price;
            $amount = round($count * $price, 2);
            $total += $amount;
            $rows[] = [
                'key' => 'membership-'.$plan->id,
                'source' => 'اشتراكات فئة '.(int) $price,
                'detail' => $count.' مشترك × '.number_format($price, 0).' ₪',
                'amount' => $amount,
                'count' => $count,
                'price' => $price,
                'plan' => $plan->name,
            ];
        }

        return [
            'rows' => $rows,
            'total' => round($total, 2),
            'subscriptions' => $subscriptions,
        ];
    }

    public function giftOrders(Carbon $start, Carbon $end): Collection
    {
        return Order::query()
            ->with(['items.menuItem', 'membership', 'user', 'restaurant'])
            ->where(function ($query) {
                $query->where('type', 'redemption')->orWhere('points_spent', '>', 0);
            })
            ->deliveredIn($start, $end)
            ->get()
            ->filter(fn (Order $order) => $order->isPremiumGift())
            ->values();
    }

    private function periodPayload(string $key, string $label, Carbon $start, Carbon $end): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'start' => $start,
            'end' => $end,
            'query' => [
                'period' => $key,
                'date' => in_array($key, ['day', 'week'], true) ? $start->toDateString() : null,
                'month' => $key === 'month' ? $start->format('Y-m') : null,
                'from' => $key === 'custom' ? $start->toDateString() : null,
                'to' => $key === 'custom' ? $end->toDateString() : null,
            ],
        ];
    }
}
