<?php

use App\Models\Setting;
use App\Services\ReferralService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => ReferralService::SETTING_INVITER],
            [
                'value' => (string) ReferralService::DEFAULT_INVITER,
                'label' => 'نقاط الداعي عند انضمام صديق بكوده',
            ]
        );

        Setting::query()->updateOrCreate(
            ['key' => ReferralService::SETTING_INVITEE],
            [
                'value' => (string) ReferralService::DEFAULT_INVITEE,
                'label' => 'نقاط الصديق الجديد عند إدخال كود الدعوة',
            ]
        );

        Setting::forgetCache();
    }

    public function down(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => ReferralService::SETTING_INVITER],
            ['value' => '50']
        );

        Setting::query()->updateOrCreate(
            ['key' => ReferralService::SETTING_INVITEE],
            ['value' => '50']
        );

        Setting::forgetCache();
    }
};
