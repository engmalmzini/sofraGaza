<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('duration_days');
            $table->decimal('price', 8, 2);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('restaurant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_plan_id')->constrained('restaurant_plans')->restrictOnDelete();
            $table->decimal('amount', 8, 2);
            $table->string('status', 20)->default('pending');
            $table->string('transfer_receipt_path');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('panel_suspended')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('panel_suspended');
        });
        Schema::dropIfExists('restaurant_subscriptions');
        Schema::dropIfExists('restaurant_plans');
    }
};
