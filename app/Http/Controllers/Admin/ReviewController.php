<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status'); // 'all', 'approved', 'hidden'
        $rating = $request->query('rating');
        $restaurantId = $request->query('restaurant_id');
        $search = $request->query('q');

        $query = Review::query()->with(['user', 'restaurant', 'order'])->latest();

        if ($status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($status === 'hidden') {
            $query->where('is_approved', false);
        }

        if ($rating) {
            $query->where('rating', $rating);
        }

        if ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                  ->orWhereHas('restaurant', fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        // Summary Statistics
        $totalCount = Review::count();
        $approvedCount = Review::where('is_approved', true)->count();
        $hiddenCount = Review::where('is_approved', false)->count();
        $avgRating = (float) (Review::avg('rating') ?: 5.0);

        $restaurants = Restaurant::query()->orderBy('name')->get(['id', 'name']);

        $reviews = $query->paginate(15)->withQueryString();

        return view('admin.reviews.index', compact(
            'reviews',
            'restaurants',
            'status',
            'rating',
            'restaurantId',
            'search',
            'totalCount',
            'approvedCount',
            'hiddenCount',
            'avgRating'
        ));
    }

    public function toggle(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => ! $review->is_approved]);

        $status = $review->is_approved ? 'تم اعتماد ونشر التقييم.' : 'تم إخفاء التقييم.';

        return back()->with('success', $status);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'تم حذف التقييم بنجاح.');
    }
}
