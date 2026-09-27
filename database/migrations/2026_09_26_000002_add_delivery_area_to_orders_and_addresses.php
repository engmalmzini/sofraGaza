<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_area', 50)->nullable()->after('address_details');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('area', 50)->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_area');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
