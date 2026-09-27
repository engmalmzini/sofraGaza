<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::query()->with('user', 'restaurant', 'order')->latest();

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->query('restaurant_id'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->query('rating'));
        }

        $reviews = $query->paginate(20)->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
        ]);
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
