<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function index(Request $request): View
    {
        $query = Restaurant::query()->with('owner')->withCount('menuItems')->latest();

        if ($request->filled('status') && in_array($request->status, array_keys(Restaurant::VERIFICATION_STATUSES), true)) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")
                    ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
            });
        }

        $restaurants = $query->paginate(15)->withQueryString();
        $pendingCount = Restaurant::query()->pendingVerification()->count();

        return view('admin.restaurants.index', compact('restaurants', 'pendingCount'));
    }

    public function create(): View
    {
        return view('admin.restaurants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('restaurants', 'public');
        }

        $data['verification_status'] = Restaurant::VERIFICATION_APPROVED;
        $data['verified_at'] = now();
        $data['verified_by'] = $request->user()->id;

        Restaurant::create($data);

        return redirect()->route('admin.restaurants.index')->with('success', 'تمت إضافة المطعم وتحديد مدة عرضه.');
    }

    public function show(Restaurant $restaurant): View
    {
        $restaurant->load(['owner', 'verifier'])->loadCount('menuItems');

        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function edit(Restaurant $restaurant): View
    {
        return view('admin.restaurants.edit', compact('restaurant'));
    }

    public function update(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('restaurants', 'public');
        }

        $restaurant->update($data);

        return redirect()->route('admin.restaurants.index')->with('success', 'تم تحديث بيانات المطعم.');
    }

    public function approve(Request $request, Restaurant $restaurant, NotificationService $notifications): RedirectResponse
    {
        abort_unless($restaurant->isPending(), 403);

        $days = (int) ($request->integer('listing_days') ?: Setting::value('restaurant_listing_days', 90));
        $days = max(7, min(365, $days));

        $restaurant->update([
            'verification_status' => Restaurant::VERIFICATION_APPROVED,
            'rejection_reason' => null,
            'is_active' => true,
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays($days)->toDateString(),
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        if ($restaurant->owner) {
            $notifications->notify(
                $restaurant->owner,
                'تم قبول مطعمك',
                "تمت الموافقة على {$restaurant->name}. أصبح مطعمك ظاهراً للزبائن في الصفحة الرئيسية.",
                route('partner.dashboard')
            );
        }

        return redirect()->route('admin.restaurants.index')
            ->with('success', "تمت الموافقة على {$restaurant->name} ونُشر على المنصة.");
    }

    public function reject(Request $request, Restaurant $restaurant, NotificationService $notifications): RedirectResponse
    {
        abort_unless($restaurant->isPending(), 403);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:8', 'max:500'],
        ], [
            'rejection_reason.required' => 'اكتب سبب الرفض ليتمكن صاحب المطعم من التصحيح.',
            'rejection_reason.min' => 'سبب الرفض قصير جداً.',
        ]);

        $restaurant->update([
            'verification_status' => Restaurant::VERIFICATION_REJECTED,
            'rejection_reason' => $data['rejection_reason'],
            'is_active' => false,
        ]);

        if ($restaurant->owner) {
            $notifications->notify(
                $restaurant->owner,
                'لم يتم قبول مطعمك بعد',
                $data['rejection_reason'].' يمكنك تعديل البيانات وإعادة الإرسال للمراجعة.',
                route('partner.restaurant.edit')
            );
        }

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'تم رفض الطلب مع إرسال السبب لصاحب المطعم.');
    }

    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->delete();

        return back()->with('success', 'تم حذف المطعم.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:restaurant,cafe'],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['nullable'],
            'is_featured' => ['nullable'],
            'points_per_amount' => ['nullable', 'numeric', 'min:0.01'],
            'points_redeem_per_amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['points_per_amount'] = $request->filled('points_per_amount') ? $data['points_per_amount'] : null;
        $data['points_redeem_per_amount'] = $request->filled('points_redeem_per_amount') ? $data['points_redeem_per_amount'] : null;
        unset($data['image']);

        return $data;
    }
}
