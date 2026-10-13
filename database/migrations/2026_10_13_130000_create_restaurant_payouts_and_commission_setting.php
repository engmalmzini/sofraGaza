<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->string('period', 20)->default('all');
            $table->string('payout_method', 50)->default('jawwal_pay');
            $table->text('transfer_details');
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
        });

        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'payout_method')) {
                $table->string('payout_method', 50)->nullable();
            }
            if (! Schema::hasColumn('restaurants', 'payout_details')) {
                $table->text('payout_details')->nullable();
            }
        });

        Setting::query()->updateOrCreate(
            ['key' => 'restaurant_commission_percent'],
            [
                'value' => '10',
                'label' => 'نسبة عمولة المنصة من مبيعات المطعم (%)',
            ]
        );
        Setting::forgetCache();
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'payout_details')) {
                $table->dropColumn('payout_details');
            }
            if (Schema::hasColumn('restaurants', 'payout_method')) {
                $table->dropColumn('payout_method');
            }
        });

        Schema::dropIfExists('restaurant_payouts');

        Setting::query()->where('key', 'restaurant_commission_percent')->delete();
        Setting::forgetCache();
    }
};
