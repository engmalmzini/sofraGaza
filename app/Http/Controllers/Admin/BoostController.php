<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantBoost;
use App\Services\RestaurantBoostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class BoostController extends Controller
{
    public function __construct(private RestaurantBoostService $boosts) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = RestaurantBoost::query()->with('restaurant.owner')->latest('id');

        if ($status && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $boosts = $query->paginate(20)->withQueryString();

        return view('admin.boosts.index', [
            'boosts' => $boosts,
            'status' => $status,
            'counts' => [
                'all' => RestaurantBoost::query()->count(),
                'pending' => RestaurantBoost::query()->pending()->count(),
                'approved' => RestaurantBoost::query()->approved()->count(),
                'rejected' => RestaurantBoost::query()->where('status', RestaurantBoost::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    public function show(RestaurantBoost $boost): View
    {
        $boost->load(['restaurant.owner', 'approver']);

        return view('admin.boosts.show', compact('boost'));
    }

    public function receipt(RestaurantBoost $boost): Response
    {
        return $boost->receiptResponse();
    }

    public function approve(RestaurantBoost $boost): RedirectResponse
    {
        try {
            $message = $this->boosts->approve($boost, auth()->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.boosts.show', $boost)->with('success', $message);
    }

    public function reject(Request $request, RestaurantBoost $boost): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'أدخل سبب الرفض لصاحب المطعم.',
        ]);

        try {
            $message = $this->boosts->reject($boost, $data['rejection_reason']);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.boosts.show', $boost)->with('success', $message);
    }
}
