<?php

use App\Models\HomePartner;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        HomePartner::query()->where('image_path', 'like', '%.svg')->delete();

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
            HomePartner::query()->updateOrCreate(
                ['name' => $row['name']],
                array_merge($row, [
                    'url' => null,
                    'is_active' => true,
                ])
            );
        }
    }

    public function down(): void
    {
        //
    }
};
