<?php

namespace App\Http\Controllers\Partner;

use App\Services\RestaurantBoostService;
use App\Support\Finance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class BoostController extends Controller
{
    public function __construct(private RestaurantBoostService $boosts) {}

    public function index(): View
    {
        $restaurant = $this->restaurant();
        $active = $restaurant->activeBoost();
        $pending = $restaurant->pendingBoost();
        $history = $restaurant->boosts()->latest('id')->limit(12)->get();
        $rate = Finance::BOOST_DAILY_RATE;

        return view('partner.boosts.index', [
            'restaurant' => $restaurant,
            'active' => $active,
            'pending' => $pending,
            'history' => $history,
            'rate' => $rate,
            'canRequest' => $restaurant->isApproved() && ! $restaurant->panel_suspended && ! $pending,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:30'],
            'receipt' => ['required', 'image', 'max:5120'],
        ], [
            'days.required' => 'حدد عدد أيام الإعلان.',
            'days.min' => 'أقل مدة يوم واحد.',
            'days.max' => 'أقصى مدة 30 يوماً.',
            'receipt.required' => 'أرفق صورة إشعار الحوالة.',
            'receipt.image' => 'إشعار الحوالة يجب أن يكون صورة.',
        ]);

        try {
            $this->boosts->request($this->restaurant(), (int) $data['days'], $request->file('receipt'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        return redirect()
            ->route('partner.boosts.index')
            ->with('success', 'تم إرسال طلب الإعلان. بعد تأكيد الإدارة يظهر '.$this->restaurant()->venueNounYours().' في المقدمة.');
    }
}
