<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Support\PalestinianPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class DeliveryController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        $tab = $request->string('tab')->toString();
        if (! in_array($tab, ['waiting', 'active', 'done', 'all'], true)) {
            $tab = 'waiting';
        }

        $applications = User::query()
            ->where('role', 'courier')
            ->where('courier_status', User::COURIER_PENDING)
            ->latest()
            ->get();

        $couriers = User::query()
            ->where('role', 'courier')
            ->where(function ($query) {
                $query->where('courier_status', User::COURIER_APPROVED)
                    ->orWhereNull('courier_status');
            })
            ->with(['deliveries' => fn ($query) => $query->where('status', 'delivering')->with(['restaurant', 'user'])])
            ->withCount([
                'deliveries as active_count' => fn ($query) => $query->where('status', 'delivering'),
                'deliveries as today_count' => fn ($query) => $query->where('status', 'delivered')->whereDate('delivered_at', today()),
            ])
            ->orderBy('name')
            ->get();

        $idle = $couriers->filter(fn (User $courier) => (int) $courier->active_count === 0)->values();
        $busy = $couriers->filter(fn (User $courier) => (int) $courier->active_count > 0)->values();

        $ordersQuery = Order::query()
            ->with(['user', 'restaurant', 'courier'])
            ->whereIn('status', ['preparing', 'delivering', 'delivered']);

        if ($tab === 'waiting') {
            $ordersQuery->whereNull('courier_id')->whereIn('status', ['preparing', 'delivering']);
        } elseif ($tab === 'active') {
            $ordersQuery->where('status', 'delivering')->whereNotNull('courier_id');
        } elseif ($tab === 'done') {
            $ordersQuery->where('status', 'delivered')->whereDate('delivered_at', today());
        }

        if ($request->filled('courier_id')) {
            $ordersQuery->where('courier_id', $request->integer('courier_id'));
        }

        if ($request->filled('q') && $tab !== 'couriers') {
            $q = $request->q;
            $ordersQuery->where(function ($builder) use ($q) {
                $builder->where('id', $q)
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('restaurant', fn ($restaurant) => $restaurant->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('courier', fn ($courier) => $courier->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
            });
        }

        $orders = $ordersQuery->latest()->paginate(20)->withQueryString();

        $waitingCount = Order::query()->whereNull('courier_id')->whereIn('status', ['preparing', 'delivering'])->count();
        $activeCount = Order::query()->where('status', 'delivering')->whereNotNull('courier_id')->count();
        $doneCount = Order::query()->where('status', 'delivered')->whereDate('delivered_at', today())->count();

        return view('admin.delivery.index', [
            'applications' => $applications,
            'couriers' => $couriers,
            'idle' => $idle,
            'busy' => $busy,
            'orders' => $orders,
            'tab' => $tab,
            'waitingCount' => $waitingCount,
            'activeCount' => $activeCount,
            'doneCount' => $doneCount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['phone' => PalestinianPhone::digits($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => PalestinianPhone::rules(true),
            'password' => ['required', 'string', 'min:6', 'max:80'],
        ], PalestinianPhone::messages());

        User::query()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'courier',
            'courier_status' => User::COURIER_APPROVED,
            'courier_verified_at' => now(),
        ]);

        return back()->with('success', 'تم إضافة مندوب التوصيل. يدخل بنفس صفحة الدخول برقم هاتفه.');
    }

    public function show(User $courier): View
    {
        abort_unless($courier->isCourier(), 404);

        $courier->load(['deliveries' => fn ($query) => $query->with(['restaurant', 'user'])->limit(40)]);

        return view('admin.delivery.show', [
            'courier' => $courier,
            'active' => $courier->deliveries->where('status', 'delivering')->values(),
            'history' => $courier->deliveries->where('status', '!=', 'delivering')->values(),
        ]);
    }

    public function update(Request $request, User $courier): RedirectResponse
    {
        abort_unless($courier->isCourier(), 404);
        $request->merge(['phone' => PalestinianPhone::digits($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => array_merge(PalestinianPhone::rules(), [Rule::unique('users', 'phone')->ignore($courier->id)]),
            'password' => ['nullable', 'string', 'min:6', 'max:80'],
        ], PalestinianPhone::messages());

        $courier->name = $data['name'];
        $courier->phone = $data['phone'];
        if (! empty($data['password'])) {
            $courier->password = $data['password'];
        }
        $courier->save();

        return back()->with('success', 'تم تحديث بيانات المندوب.');
    }

    public function assign(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'courier_id' => ['required', 'exists:users,id'],
        ]);

        $courier = User::query()->findOrFail($data['courier_id']);

        try {
            $this->orders->assignCourier($order, $courier);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "تم تعيين {$courier->name} على الطلب #{$order->id}.");
    }

    public function unassign(Order $order): RedirectResponse
    {
        try {
            $this->orders->unassignCourier($order);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'عاد الطلب لقائمة الانتظار بدون مندوب.');
    }

    public function approve(User $courier, NotificationService $notifications): RedirectResponse
    {
        abort_unless($courier->isCourierPending(), 403);

        $courier->update([
            'courier_status' => User::COURIER_APPROVED,
            'courier_rejection_reason' => null,
            'courier_verified_at' => now(),
        ]);

        $notifications->notify(
            $courier,
            'تم قبولك كمندوب توصيل',
            'افتح لوحة التحكم من الرابط وتابع الطلبات التي نرسلها لك شخصياً. لن تظهر طلبات المندوبين الآخرين عندك.',
            route('courier.dashboard')
        );

        return back()->with('success', "تمت الموافقة على {$courier->name} وأُرسل له رابط لوحته.");
    }

    public function reject(Request $request, User $courier, NotificationService $notifications): RedirectResponse
    {
        abort_unless($courier->isCourierPending(), 403);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:8', 'max:500'],
        ], [
            'rejection_reason.required' => 'اكتب سبب الرفض.',
            'rejection_reason.min' => 'سبب الرفض قصير جداً.',
        ]);

        $courier->update([
            'courier_status' => User::COURIER_REJECTED,
            'courier_rejection_reason' => $data['rejection_reason'],
        ]);

        $notifications->notify(
            $courier,
            'لم يتم قبول طلب التوصيل',
            $data['rejection_reason'],
            route('courier.dashboard')
        );

        return back()->with('success', "تم رفض طلب {$courier->name}.");
    }
}
