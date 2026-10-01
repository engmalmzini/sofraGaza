<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_boosts', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->after('notes');
            $table->unsignedSmallInteger('days')->default(1)->after('daily_rate');
            $table->decimal('amount', 10, 2)->default(0)->after('days');
            $table->string('transfer_receipt_path')->nullable()->after('amount');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
        });

        $boosts = DB::table('restaurant_boosts')->get();
        foreach ($boosts as $boost) {
            $start = \Carbon\Carbon::parse($boost->starts_on)->startOfDay();
            $end = \Carbon\Carbon::parse($boost->ends_on)->startOfDay();
            $days = max(1, (int) round($start->diffInDays($end, true)) + 1);
            $rate = (float) $boost->daily_rate;
            DB::table('restaurant_boosts')->where('id', $boost->id)->update([
                'days' => $days,
                'amount' => round($days * $rate, 2),
                'status' => $boost->status ?: 'approved',
                'approved_at' => $boost->approved_at ?: $boost->created_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('restaurant_boosts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'status',
                'days',
                'amount',
                'transfer_receipt_path',
                'rejection_reason',
                'approved_at',
            ]);
        });
    }
};
