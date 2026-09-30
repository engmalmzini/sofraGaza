<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path');
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['name' => 'JRAZZA', 'image_path' => 'images/partners/jrazza.png', 'sort_order' => 1],
            ['name' => 'Kimbo Chicken', 'image_path' => 'images/partners/kimbo.png', 'sort_order' => 2],
            ['name' => 'RAKO', 'image_path' => 'images/partners/rako.png', 'sort_order' => 3],
            ['name' => 'CRISP', 'image_path' => 'images/partners/crisp.png', 'sort_order' => 4],
            ['name' => 'Raja88 Casa', 'image_path' => 'images/partners/raja88.png', 'sort_order' => 5],
            ['name' => 'CHEESY', 'image_path' => 'images/partners/cheesy.png', 'sort_order' => 6],
            ['name' => 'sushi', 'image_path' => 'images/partners/sushi.png', 'sort_order' => 7],
            ['name' => 'tomato', 'image_path' => 'images/partners/tomato.png', 'sort_order' => 8],
            ['name' => 'food', 'image_path' => 'images/partners/food.png', 'sort_order' => 9],
        ];
        foreach ($rows as $row) {
            \Illuminate\Support\Facades\DB::table('home_partners')->insert(array_merge($row, [
                'url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_partners');
    }
};
