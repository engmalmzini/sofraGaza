<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('role');
            $table->string('bike_photo_path')->nullable()->after('photo_path');
            $table->string('bike_type', 20)->nullable()->after('bike_photo_path');
            $table->string('courier_status', 20)->nullable()->after('bike_type');
            $table->text('courier_rejection_reason')->nullable()->after('courier_status');
            $table->timestamp('courier_verified_at')->nullable()->after('courier_rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'photo_path',
                'bike_photo_path',
                'bike_type',
                'courier_status',
                'courier_rejection_reason',
                'courier_verified_at',
            ]);
        });
    }
};
