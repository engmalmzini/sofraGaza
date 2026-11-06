<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $comments = [
            'الأكل واصل سخن وطازج والتوصيل سريع جداً، تجربة ممتازة وبنصح فيهم بشدة.',
            'شغل مرتب ونظافة عالية، ونكهة أصلية على أصولها. بارك الله فيكم.',
            'الطعم جداً زاكي والكمية وفيرة، التوصيل تأخر 5 دقائق فقط بس الأكل عوّض كل شي.',
            'الخبز طازج والمشاوي متبلة صح، أفضل تجربة طلب أونلاين في غزة.',
            'خدمة ممتازة وتغليف متقن حافظ على سخونة الوجبة. سفرة غزة ما قصرتوا.',
            'جودة ممتازة وسعر مناسب، بنطلب من عندهم دايماً.',
        ];

        DB::table('reviews')
            ->whereNull('order_id')
            ->whereIn('comment', $comments)
            ->delete();
    }

    public function down(): void
    {
        // Seeded placeholder reviews are not restored.
    }
};
