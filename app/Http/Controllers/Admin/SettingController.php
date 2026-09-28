<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->orderBy('id')->get();
        $areasWithFees = Setting::areasWithFees();
        $paymentAccounts = Setting::paymentAccounts();
        $homeBanners = Setting::rawHomeBanners();

        return view('admin.settings.index', compact('settings', 'areasWithFees', 'paymentAccounts', 'homeBanners'));
    }

    public function update(Request $request): RedirectResponse
    {
        $values = $request->validate([
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable', 'string', 'max:1000'],
            'delivery_fees_by_area' => ['nullable', 'array'],
            'delivery_fees_by_area.*' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'new_area_key' => ['nullable', 'string', 'max:50'],
            'new_area_label' => ['nullable', 'string', 'max:100'],
            'new_area_fee' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'remove_area_key' => ['nullable', 'string', 'max:50'],
            'jawwal_pay_qr' => ['nullable', 'image', 'max:4096'],
            'remove_jawwal_pay_qr' => ['nullable', 'boolean'],
            'palpay_qr' => ['nullable', 'image', 'max:4096'],
            'remove_palpay_qr' => ['nullable', 'boolean'],
            'new_banners' => ['nullable', 'array'],
            'new_banners.*' => ['nullable', 'image', 'max:6144'],
            'new_banner_url' => ['nullable', 'url', 'max:1000'],
            'remove_banner' => ['nullable', 'string'],
        ]);

        if (! empty($values['settings'])) {
            $defaultLabels = [
                'bank_name' => 'اسم البنك',
                'bank_account_number' => 'رقم الحساب البنكي',
                'bank_iban' => 'رقم الآيبان (IBAN)',
                'bank_beneficiary_name' => 'اسم المستفيد البنكي',
                'jawwal_pay_number' => 'رقم محفظة جوال باي',
                'jawwal_pay_name' => 'اسم صاحب محفظة جوال باي',
                'palpay_number' => 'رقم محفظة بال باي (PalPay)',
                'palpay_name' => 'اسم صاحب محفظة بال باي',
                'payment_instructions_note' => 'ملاحظات وتعليمات التحويل',
            ];

            foreach ($values['settings'] as $key => $value) {
                $setting = Setting::query()->where('key', $key)->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                } else {
                    Setting::query()->create([
                        'key' => $key,
                        'value' => $value,
                        'label' => $defaultLabels[$key] ?? $key,
                    ]);
                }
            }
        }

        if ($request->hasFile('jawwal_pay_qr')) {
            $path = $request->file('jawwal_pay_qr')->store('payment', 'public');
            Setting::query()->updateOrCreate(
                ['key' => 'jawwal_pay_qr_path'],
                ['label' => 'مسار باركود / QR جوال باي', 'value' => $path]
            );
        } elseif ($request->boolean('remove_jawwal_pay_qr')) {
            Setting::query()->where('key', 'jawwal_pay_qr_path')->update(['value' => null]);
        }

        if ($request->hasFile('palpay_qr')) {
            $path = $request->file('palpay_qr')->store('payment', 'public');
            Setting::query()->updateOrCreate(
                ['key' => 'palpay_qr_path'],
                ['label' => 'مسار باركود / QR بال باي (PalPay)', 'value' => $path]
            );
        } elseif ($request->boolean('remove_palpay_qr')) {
            Setting::query()->where('key', 'palpay_qr_path')->update(['value' => null]);
        }

        if ($request->has('delivery_fees_by_area')) {
            $fees = [];
            foreach ($request->input('delivery_fees_by_area', []) as $areaKey => $fee) {
                if ($fee !== null && $fee !== '') {
                    $fees[$areaKey] = (float) $fee;
                }
            }
            Setting::query()->updateOrCreate(
                ['key' => 'delivery_fees_by_area'],
                [
                    'label' => 'رسوم التوصيل حسب المناطق',
                    'value' => json_encode($fees, JSON_UNESCAPED_UNICODE),
                ]
            );
        }

        if (! empty($values['new_area_key']) && ! empty($values['new_area_label'])) {
            $customAreas = Setting::customAreas();
            $newKey = trim($values['new_area_key']);
            $customAreas[$newKey] = [
                'key' => $newKey,
                'label' => trim($values['new_area_label']),
                'is_custom' => true,
            ];
            Setting::query()->updateOrCreate(
                ['key' => 'custom_delivery_areas'],
                [
                    'label' => 'المناطق المخصصة للتوصيل',
                    'value' => json_encode(array_values($customAreas), JSON_UNESCAPED_UNICODE),
                ]
            );

            if (isset($values['new_area_fee']) && $values['new_area_fee'] !== '') {
                $fees = Setting::deliveryFeesByArea();
                $fees[$newKey] = (float) $values['new_area_fee'];
                Setting::query()->updateOrCreate(
                    ['key' => 'delivery_fees_by_area'],
                    [
                        'label' => 'رسوم التوصيل حسب المناطق',
                        'value' => json_encode($fees, JSON_UNESCAPED_UNICODE),
                    ]
                );
            }
        }

        if (! empty($values['remove_area_key'])) {
            $customAreas = Setting::customAreas();
            $removeKey = trim($values['remove_area_key']);
            $filtered = array_values(array_filter($customAreas, fn ($a) => ($a['key'] ?? '') !== $removeKey));
            Setting::query()->updateOrCreate(
                ['key' => 'custom_delivery_areas'],
                [
                    'label' => 'المناطق المخصصة للتوصيل',
                    'value' => json_encode($filtered, JSON_UNESCAPED_UNICODE),
                ]
            );
            $fees = Setting::deliveryFeesByArea();
            unset($fees[$removeKey]);
            Setting::query()->updateOrCreate(
                ['key' => 'delivery_fees_by_area'],
                [
                    'label' => 'رسوم التوصيل حسب المناطق',
                    'value' => json_encode($fees, JSON_UNESCAPED_UNICODE),
                ]
            );
        }

        // Handle Home Banner Ads
        $currentBanners = Setting::rawHomeBanners();
        $bannersChanged = false;

        if ($request->hasFile('new_banners')) {
            foreach ($request->file('new_banners') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('banners', 'public');
                    $currentBanners[] = $path;
                    $bannersChanged = true;
                }
            }
        }

        if ($request->filled('new_banner_url')) {
            $url = trim((string) $request->input('new_banner_url'));
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $currentBanners[] = $url;
                $bannersChanged = true;
            }
        }

        if ($request->filled('remove_banner')) {
            $removeTarget = trim((string) $request->input('remove_banner'));
            $originalCount = count($currentBanners);
            $currentBanners = array_values(array_filter($currentBanners, function ($item) use ($removeTarget) {
                $path = is_array($item) ? ($item['image'] ?? $item['url'] ?? '') : (string) $item;
                if ($path === $removeTarget) {
                    if (! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                    }
                    return false;
                }
                return true;
            }));

            if (count($currentBanners) !== $originalCount) {
                $bannersChanged = true;
            }
        }

        if ($bannersChanged) {
            Setting::query()->updateOrCreate(
                ['key' => 'home_banners'],
                [
                    'label' => 'بانرات إعلانات شاشة الهاتف',
                    'value' => json_encode(array_values($currentBanners), JSON_UNESCAPED_UNICODE),
                ]
            );
        }

        Setting::forgetCache();

        return back()->with('success', 'تم حفظ إعدادات المنصة والبانرات بنجاح.');
    }
}
