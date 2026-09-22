<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_subscriptions', function (Blueprint $table) {
            $table->string('card_status', 20)->nullable()->after('rejection_reason');
            $table->timestamp('card_requested_at')->nullable()->after('card_status');
            $table->timestamp('card_fulfilled_at')->nullable()->after('card_requested_at');
            $table->string('card_note', 255)->nullable()->after('card_fulfilled_at');
        });
    }

    public function down(): void
    {
        Schema::table('membership_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['card_status', 'card_requested_at', 'card_fulfilled_at', 'card_note']);
        });
    }
};
