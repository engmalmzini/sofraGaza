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
            $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('verification_status', 20)->default('approved')->after('is_featured');
            $table->text('rejection_reason')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('rejection_reason');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->string('owner_national_id', 20)->nullable()->after('phone');
            $table->string('license_number', 50)->nullable()->after('owner_national_id');
            $table->string('cuisine', 50)->nullable()->after('type');
            $table->string('area', 50)->nullable()->after('address');
            $table->time('opens_at')->nullable()->after('area');
            $table->time('closes_at')->nullable()->after('opens_at');
            $table->index('verification_status');
        });

        if (Schema::hasTable('settings')) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'restaurant_listing_days'],
                ['value' => '90', 'label' => 'مدة عرض المطعم بعد الموافقة (أيام)', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
            $table->dropConstrainedForeignId('verified_by');
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'verification_status',
                'rejection_reason',
                'verified_at',
                'owner_national_id',
                'license_number',
                'cuisine',
                'area',
                'opens_at',
                'closes_at',
            ]);
        });
    }
};
