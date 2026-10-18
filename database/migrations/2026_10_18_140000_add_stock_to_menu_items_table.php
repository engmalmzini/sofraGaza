<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('stock_unlimited')->default(true)->after('is_available');
            $table->unsignedInteger('stock_qty')->nullable()->after('stock_unlimited');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['stock_unlimited', 'stock_qty']);
        });
    }
};
