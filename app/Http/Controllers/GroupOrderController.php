<?php

namespace App\Http\Controllers;

use App\Models\GroupOrder;
use App\Models\GroupOrderMember;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\GroupOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class GroupOrderController extends Controller
{
    public function __construct(
        private GroupOrderService $groups,
        private CartService $cart,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $restaurant = $this->resolveRestaurant($request);

        if (! $restaurant) {
            return redirect()->route('restaurants.index')->with('error', 'اختر مطعماً أولاً ثم ابدأ طلباً جماعياً.');
        }

        return view('group-orders.create', [
            'restaurant' => $restaurant,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'restaurant_id' => ['required', 'exists:restaurants,id'],
            'phones' => ['required', 'array', 'min:1', 'max:8'],
            'phones.*' => ['nullable', 'string', 'max:20'],
        ], [
            'phones.required' => 'أضف أرقام اللي بدهم يطلبوا معك.',
            'phones.min' => 'أضف رقم واحد على الأقل.',
        ]);

        $restaurant = Restaurant::query()->findOrFail($data['restaurant_id']);

        try {
            $group = $this->groups->create($request->user(), $restaurant, $data['phones']);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('group-orders.show', $group)
            ->with('success', 'وصل إشعار لكل واحد تدعوه. اطلب اللي بدك إياه من المنيو، واستنى لحتى يخلّصوا.');
    }

    public function show(Request $request, GroupOrder $groupOrder): View
    {
        $this->authorizeMember($groupOrder, $request->user());
        $groupOrder->load(['members.user', 'restaurant', 'host', 'order']);
        $this->groups->bindSession($groupOrder);

        $member = $groupOrder->memberFor($request->user());
        $quote = $this->cart->quote($request->user());
        $cartForGroup = ($quote['restaurant']?->id === $groupOrder->restaurant_id) ? $quote : null;

        return view('group-orders.show', [
            'group' => $groupOrder,
            'member' => $member,
            'quote' => $cartForGroup,
            'isHost' => $member?->is_host,
        ]);
    }

    public function pay(Request $request, GroupOrder $groupOrder): View|RedirectResponse
    {
        $this->authorizeMember($groupOrder, $request->user());
        $groupOrder->load(['members.user', 'restaurant']);
        $member = $groupOrder->memberFor($request->user());

        if ($member?->is_host) {
            return redirect()->route('group-orders.checkout', $groupOrder);
        }

        if ($member?->isPaid()) {
            return redirect()->route('group-orders.show', $groupOrder)->with('success', 'نصيبك مدفوع.');
        }

        $quote = $this->cart->quote($request->user());
        if ($quote['lines'] === [] || (int) $quote['restaurant']?->id !== (int) $groupOrder->restaurant_id) {
            return redirect()
                ->route('restaurants.show', $groupOrder->restaurant)
                ->with('error', 'أضف أصنافك من المنيو أولاً، بعدين ادفع نصيبك.');
        }

        return view('group-orders.pay', [
            'group' => $groupOrder,
            'member' => $member,
            'quote' => $quote,
            'forHost' => false,
            'charge' => (float) $quote['items_total'],
            'deliveryFee' => 0,
            'paymentAccounts' => Setting::paymentAccounts(),
        ]);
    }

    public function checkout(Request $request, GroupOrder $groupOrder): View|RedirectResponse
    {
        $this->authorizeHost($groupOrder, $request->user());
        $groupOrder->load(['members.user', 'restaurant', 'host']);

        if (! $groupOrder->allGuestsPaid()) {
            return redirect()->route('group-orders.show', $groupOrder)
                ->with('error', 'استنى لحتى كل المدعوين يخلّصوا ويدفعوا نصيبهم.');
        }

        $user = $request->user();
        $areaKey = session('delivery_area')['key'] ?? (config('brand.areas.0.key') ?? 'الرمال');
        $quote = $this->cart->quote($user, $areaKey);
        $hostFood = ((int) $quote['restaurant']?->id === (int) $groupOrder->restaurant_id)
            ? (float) $quote['items_total']
            : 0.0;
        $deliveryFee = (float) ($quote['delivery_fee'] ?? Setting::deliveryFeeForArea($areaKey));

        return view('group-orders.pay', [
            'group' => $groupOrder,
            'member' => $groupOrder->hostMember(),
            'quote' => $quote,
            'forHost' => true,
            'charge' => round($hostFood + $deliveryFee, 2),
            'hostFood' => $hostFood,
            'deliveryFee' => $deliveryFee,
            'user' => $user,
            'addresses' => $user->addresses,
            'deliveryAreas' => Setting::areasWithFees(),
            'selectedAreaKey' => $areaKey,
            'paymentAccounts' => Setting::paymentAccounts(),
        ]);
    }

    public function storePayment(Request $request, GroupOrder $groupOrder): RedirectResponse
    {
        $this->authorizeMember($groupOrder, $request->user());
        $isHost = $groupOrder->host_user_id === $request->user()->id;
        $paymentMethod = $request->input('payment_method', 'receipt');
        $isWallet = $paymentMethod === 'wallet';

        $rules = [
            'payment_method' => ['nullable', 'string', 'in:receipt,wallet'],
            'receipt' => [$isWallet ? 'nullable' : 'required', 'image', 'max:4096'],
        ];

        if ($isHost) {
            $rules['area'] = ['nullable', 'string', 'max:50'];
            $rules['address_details'] = ['required', 'string', 'min:10'];
            $rules['phone'] = ['required', 'string', 'max:20'];
            $rules['notes'] = ['nullable', 'string', 'max:500'];
        }

        $data = $request->validate($rules, [
            'address_details.required' => 'أدخل عنوان التوصيل بالتفصيل.',
            'address_details.min' => 'العنوان قصير جداً.',
            'phone.required' => 'رقم الهاتف مطلوب للتواصل.',
            'receipt.required' => 'أرفق صورة إشعار الحوالة لنصيبك.',
        ]);
        $data['payment_method'] = $paymentMethod;

        try {
            if ($isHost) {
                $order = $this->groups->place($groupOrder, $request->user(), $data, $request->file('receipt'));

                return redirect()->route('account.orders.show', $order)
                    ->with('success', 'تم إرسال الطلب الجماعي. فاتورة واحدة وتوصيل واحد، وكل واحد دفع نصيبه.');
            }

            $this->groups->payGuestShare($groupOrder, $request->user(), $data, $request->file('receipt'));
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('group-orders.show', $groupOrder)
            ->with('success', 'تم دفع نصيبك. صاحب الطلب رح يرسل الفاتورة لما يخلّص الكل.');
    }

    public function destroy(Request $request, GroupOrder $groupOrder): RedirectResponse
    {
        try {
            $this->groups->cancel($groupOrder, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('home')->with('success', 'تم إلغاء الطلب الجماعي.');
    }

    public function decline(Request $request, GroupOrder $groupOrder): RedirectResponse
    {
        try {
            $this->groups->decline($groupOrder, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('home')->with('success', 'تم الاعتذار عن الطلب الجماعي.');
    }

    public function memberReceipt(GroupOrder $groupOrder, GroupOrderMember $member)
    {
        abort_unless($member->group_order_id === $groupOrder->id, 404);

        $user = auth()->user();
        $allowed = $user && (
            $user->isAdmin()
            || $groupOrder->host_user_id === $user->id
            || $member->user_id === $user->id
            || ($user->isPartner() && (int) $user->ownedRestaurant?->id === (int) $groupOrder->restaurant_id)
        );
        abort_unless($allowed, 403);

        return $member->receiptResponse();
    }

    private function resolveRestaurant(Request $request): ?Restaurant
    {
        if ($request->filled('restaurant')) {
            return Restaurant::query()->visible()->find($request->integer('restaurant'));
        }

        $cartRestaurantId = $this->cart->restaurantId();
        if ($cartRestaurantId) {
            return Restaurant::query()->visible()->find($cartRestaurantId);
        }

        return null;
    }

    private function authorizeMember(GroupOrder $group, $user): void
    {
        abort_unless($user && $group->memberFor($user), 403, 'هذا الطلب الجماعي مش موجه إلك.');
    }

    private function authorizeHost(GroupOrder $group, $user): void
    {
        abort_unless($user && $group->host_user_id === $user->id, 403);
    }
}
