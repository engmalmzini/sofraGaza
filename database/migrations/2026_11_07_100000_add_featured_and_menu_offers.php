<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false);
        });

        Schema::create('menu_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('first_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('second_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_offers');

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
