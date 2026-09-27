<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Review;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'order_id' => ['nullable', 'integer'],
        ], [
            'rating.required' => 'يرجى اختيار التقييم من 1 إلى 5 نجوم.',
            'rating.between' => 'التقييم يجب أن يكون بين نجمة واحدة و 5 نجوم.',
            'comment.max' => 'التعليق يجب ألا يتجاوز 1000 حرف.',
        ]);

        $orderId = null;
        if (! empty($data['order_id'])) {
            $order = Order::query()
                ->where('id', $data['order_id'])
                ->where('user_id', $user->id)
                ->where('restaurant_id', $restaurant->id)
                ->first();

            if ($order) {
                $orderId = $order->id;
            }
        } else {
            // Check if user has an unreviewed delivered order for this restaurant
            $lastDelivered = $user->orders()
                ->where('restaurant_id', $restaurant->id)
                ->where('status', 'delivered')
                ->whereDoesntHave('review')
                ->latest()
                ->first();

            if ($lastDelivered) {
                $orderId = $lastDelivered->id;
            }
        }

        // Check if this exact order was already reviewed
        if ($orderId && Review::query()->where('order_id', $orderId)->exists()) {
            return back()->with('error', 'لقد قمت بتقييم هذا الطلب مسبقاً.');
        }

        $review = Review::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'order_id' => $orderId,
            'rating' => (int) $data['rating'],
            'comment' => ! empty($data['comment']) ? trim($data['comment']) : null,
            'is_approved' => true,
        ]);

        // Notify restaurant owner if exists
        if ($restaurant->owner) {
            $this->notifications->notify(
                $restaurant->owner,
                'تقييم جديد لمطعمك',
                "قام الزبون {$user->name} بتقييم مطعمك بـ {$review->rating} نجوم.",
                route('restaurants.show', $restaurant).'#reviews'
            );
        }

        return back()->with('success', 'شكراً لتقييمك الصادق! تم نشر رأيك ومشاركته مع زوار المنصة.');
    }
}
