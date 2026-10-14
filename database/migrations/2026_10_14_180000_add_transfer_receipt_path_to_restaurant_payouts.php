<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_payouts', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurant_payouts', 'transfer_receipt_path')) {
                $table->string('transfer_receipt_path')->nullable()->after('admin_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_payouts', function (Blueprint $table) {
            if (Schema::hasColumn('restaurant_payouts', 'transfer_receipt_path')) {
                $table->dropColumn('transfer_receipt_path');
            }
        });
    }
};
