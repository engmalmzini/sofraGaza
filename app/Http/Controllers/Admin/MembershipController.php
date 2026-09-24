<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function index(Request $request): View
    {
        $query = Membership::query()->orderBy('sort_order');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%');
        }

        $memberships = $query->get();

        return view('admin.memberships.index', compact('memberships'));
    }

    public function create(): View
    {
        return view('admin.memberships.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Membership::create($this->validated($request));

        return redirect()->route('admin.memberships.index')->with('success', 'تمت إضافة العضوية.');
    }

    public function edit(Membership $membership): View
    {
        return view('admin.memberships.edit', compact('membership'));
    }

    public function update(Request $request, Membership $membership): RedirectResponse
    {
        $membership->update($this->validated($request));

        return redirect()->route('admin.memberships.index')->with('success', 'تم تعديل العضوية.');
    }

    public function toggle(Membership $membership): RedirectResponse
    {
        $membership->update(['is_active' => ! $membership->is_active]);

        return back()->with('success', $membership->is_active
            ? 'تم تفعيل العضوية وستظهر للزبائن.'
            : 'تم إيقاف العضوية ولن تظهر للزبائن الجدد.');
    }

    public function destroy(Membership $membership): RedirectResponse
    {
        $membership->delete();

        return back()->with('success', 'تم حذف العضوية.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['required', 'integer', 'min:0', 'max:90'],
            'points_multiplier' => ['required', 'numeric', 'min:1', 'max:5'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'free_delivery' => ['nullable'],
            'is_active' => ['nullable'],
        ]);

        $data['free_delivery'] = $request->boolean('free_delivery');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
