<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('sales_total', 10, 2)->default(0);
            $table->decimal('commission_total', 10, 2)->default(0);
            $table->decimal('net_total', 10, 2)->default(0);
            $table->string('status', 20)->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['restaurant_id', 'period_start', 'period_end'], 'restaurant_period_settlement');
        });

        Schema::create('restaurant_boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->decimal('daily_rate', 8, 2)->default(20);
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->date('spent_on');
            $table->string('category', 30)->default('operating');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('finance_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->date('received_on');
            $table->string('category', 30)->default('other');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_incomes');
        Schema::dropIfExists('finance_expenses');
        Schema::dropIfExists('restaurant_boosts');
        Schema::dropIfExists('restaurant_settlements');
    }
};
