<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('points_per_amount', 8, 2)->nullable()->after('is_featured');
            $table->decimal('points_redeem_per_amount', 8, 2)->nullable()->after('points_per_amount');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedInteger('earn_points')->nullable()->after('is_available');
            $table->unsignedInteger('redeem_points')->nullable()->after('earn_points');
        });

        $now = now();
        $rows = [
            [
                'key' => 'points_include_delivery',
                'value' => '1',
                'label' => 'احتساب التوصيل في اكتساب النقاط (1 نعم / 0 لا)',
            ],
            [
                'key' => 'points_redeem_per_amount',
                'value' => '1',
                'label' => 'كل كم شيكل = نقطة استبدال واحدة (عام)',
            ],
        ];

        foreach ($rows as $row) {
            if (DB::table('settings')->where('key', $row['key'])->exists()) {
                DB::table('settings')->where('key', $row['key'])->update([
                    'label' => $row['label'],
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('settings')->insert([
                'key' => $row['key'],
                'value' => $row['value'],
                'label' => $row['label'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('settings')->where('key', 'points_per_amount')->update([
            'label' => 'كل كم شيكل = نقطة اكتساب واحدة (عام لكل المطاعم)',
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['points_per_amount', 'points_redeem_per_amount']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['earn_points', 'redeem_points']);
        });
    }
};
