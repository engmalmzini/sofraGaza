<?php

use App\Models\Setting;
use App\Services\ReferralService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 12)->nullable()->unique()->after('points_balance');
            }
            if (! Schema::hasColumn('users', 'referred_by_id')) {
                $table->foreignId('referred_by_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            }
        });

        $service = app(ReferralService::class);
        foreach (DB::table('users')->whereNull('referral_code')->orderBy('id')->pluck('id') as $id) {
            DB::table('users')->where('id', $id)->update([
                'referral_code' => $service->generateUniqueCode(),
                'updated_at' => now(),
            ]);
        }

        foreach ([
            [
                'key' => ReferralService::SETTING_INVITER,
                'value' => (string) ReferralService::DEFAULT_INVITER,
                'label' => 'نقاط الداعي عند انضمام صديق بكوده',
            ],
            [
                'key' => ReferralService::SETTING_INVITEE,
                'value' => (string) ReferralService::DEFAULT_INVITEE,
                'label' => 'نقاط الصديق الجديد عند إدخال كود الدعوة',
            ],
        ] as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'label' => $setting['label']]
            );
        }

        Setting::forgetCache();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referred_by_id')) {
                $table->dropConstrainedForeignId('referred_by_id');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropUnique(['referral_code']);
                $table->dropColumn('referral_code');
            }
        });

        Setting::query()->whereIn('key', [
            ReferralService::SETTING_INVITER,
            ReferralService::SETTING_INVITEE,
        ])->delete();
        Setting::forgetCache();
    }
};
