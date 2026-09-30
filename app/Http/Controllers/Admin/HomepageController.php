<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePartner;
use App\Support\HomeContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function index(): View
    {
        return view('admin.homepage.index', [
            'schema' => HomeContent::schema(),
            'values' => HomeContent::values(),
            'partners' => HomePartner::query()->ordered()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $textRules = [];
        foreach (HomeContent::defaults() as $key => $default) {
            $textRules['content.'.$key] = ['nullable', 'string', 'max:4000'];
        }

        $request->validate(array_merge($textRules, [
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'max:6144'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['nullable', 'boolean'],
        ]));

        $incoming = $request->input('content', []);
        $payload = HomeContent::stored();
        foreach (HomeContent::defaults() as $key => $default) {
            if (array_key_exists($key, $incoming)) {
                $payload[$key] = (string) $incoming[$key];
            }
        }

        foreach (HomeContent::fieldKeysByType('image') as $key) {
            if ($request->boolean('remove_images.'.$key)) {
                $this->deleteStoredImage($payload[$key] ?? '');
                $payload[$key] = '';
            }
            if ($request->hasFile('images.'.$key)) {
                $this->deleteStoredImage($payload[$key] ?? '');
                $payload[$key] = $request->file('images.'.$key)->store('homepage', 'public');
            }
        }

        HomeContent::save($payload);

        return back()->with('success', 'تم حفظ محتوى الصفحة الرئيسية.');
    }

    public function storePartner(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'url', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        HomePartner::query()->create([
            'name' => $data['name'],
            'url' => $data['url'] ?? null,
            'sort_order' => $data['sort_order'] ?? ((int) HomePartner::query()->max('sort_order') + 1),
            'image_path' => $request->file('image')->store('partners', 'public'),
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة شعار الشريك.');
    }

    public function updatePartner(Request $request, HomePartner $partner): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'url', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $partner->name = $data['name'];
        $partner->url = $data['url'] ?? null;
        $partner->sort_order = $data['sort_order'] ?? $partner->sort_order;
        $partner->is_active = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($partner->image_path);
            $partner->image_path = $request->file('image')->store('partners', 'public');
        }

        $partner->save();

        return back()->with('success', 'تم تحديث شعار الشريك.');
    }

    public function destroyPartner(HomePartner $partner): RedirectResponse
    {
        $this->deleteStoredImage($partner->image_path);
        $partner->delete();

        return back()->with('success', 'تم حذف شعار الشريك.');
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! $path) {
            return;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'images/')) {
            return;
        }
        Storage::disk('public')->delete($path);
    }
}
