<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_orders', function (Blueprint $table) {
            $table->id();
            $table->string('token', 32)->unique();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('collecting');
            $table->string('delivery_area')->nullable();
            $table->text('address_details')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('group_order_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_order_id')->constrained('group_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone');
            $table->boolean('is_host')->default(false);
            $table->string('status', 20)->default('invited');
            $table->json('items_json')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('payment_method', 20)->nullable();
            $table->string('transfer_receipt_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['group_order_id', 'phone']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('group_order_id')->nullable()->after('restaurant_id')->constrained()->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('group_member_id')->nullable()->after('order_id')->constrained('group_order_members')->nullOnDelete();
            $table->string('ordered_by_name')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_member_id');
            $table->dropColumn('ordered_by_name');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_order_id');
        });
        Schema::dropIfExists('group_order_members');
        Schema::dropIfExists('group_orders');
    }
};
