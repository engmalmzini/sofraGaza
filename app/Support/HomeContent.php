<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class HomeContent
{
    public const SETTING_KEY = 'home_page_content';

    public static function schema(): array
    {
        return [
            'hero' => [
                'label' => 'قسم الهيرو',
                'hint' => 'العنوان الرئيسي والصورة في أعلى الصفحة',
                'fields' => [
                    'hero_kicker' => ['label' => 'الشريط أعلى العنوان', 'type' => 'text', 'default' => 'نسعدكم بتوصيل أطايب غزة يومياً'],
                    'hero_title_line1' => ['label' => 'العنوان — السطر الأول', 'type' => 'text', 'default' => 'شهيتك المفضلة،'],
                    'hero_title_line2' => ['label' => 'العنوان — السطر الثاني', 'type' => 'text', 'default' => 'تصلك بأقصى سرعة.'],
                    'hero_subtitle' => ['label' => 'النص التوضيحي', 'type' => 'textarea', 'default' => 'أشهى وجبات مطاعم قطاع غزة، شاورما، بيتزا، مشاوي وحلويات طازجة من المطبخ إلى عتبة دارك في أقل من 30 دقيقة.'],
                    'hero_cta_primary' => ['label' => 'زر الطلب', 'type' => 'text', 'default' => 'اطلب الآن'],
                    'hero_cta_secondary' => ['label' => 'زر استعراض القائمة', 'type' => 'text', 'default' => 'استعرض القائمة'],
                    'hero_rating' => ['label' => 'التقييم الظاهر', 'type' => 'text', 'default' => '4.9'],
                    'hero_customers' => ['label' => 'نص العملاء السعداء', 'type' => 'text', 'default' => 'أكثر من 75 ألف عميل سعيد'],
                    'hero_image' => ['label' => 'صورة الكابتن', 'type' => 'image', 'default' => '', 'default_public' => 'images/hero-courier.png'],
                ],
            ],
            'ticker' => [
                'label' => 'شريط الأخبار',
                'hint' => 'الشارة الثابتة على يمين الشريط',
                'fields' => [
                    'ticker_badge' => ['label' => 'نص شارة الأخبار', 'type' => 'text', 'default' => 'أخبار سفرة'],
                ],
            ],
            'categories' => [
                'label' => 'قسم الفئات',
                'hint' => 'عنوان القسم وأزرار البطاقات',
                'fields' => [
                    'categories_title' => ['label' => 'عنوان القسم', 'type' => 'text', 'default' => 'الفئات'],
                    'categories_view_all' => ['label' => 'زر عرض الكل', 'type' => 'text', 'default' => 'عرض الكل'],
                    'categories_cta' => ['label' => 'زر البطاقة', 'type' => 'text', 'default' => 'اطلب الآن'],
                ],
            ],
            'places' => [
                'label' => 'قسم المطاعم',
                'hint' => 'عنوان شبكة المطاعم والكافيهات',
                'fields' => [
                    'places_kicker' => ['label' => 'التسمية الصغيرة', 'type' => 'text', 'default' => 'المطاعم والكافيهات'],
                    'places_title' => ['label' => 'العنوان', 'type' => 'text', 'default' => 'أفضل الأماكن الجاهزة لخدمتك الآن'],
                    'places_view_all' => ['label' => 'زر عرض الكل', 'type' => 'text', 'default' => 'عرض الكل'],
                ],
            ],
            'app' => [
                'label' => 'قسم التطبيق',
                'hint' => 'عنوان بانر تحميل التطبيق',
                'fields' => [
                    'app_heading_line1' => ['label' => 'عنوان التطبيق — السطر الأول', 'type' => 'text', 'default' => 'حمّل'],
                    'app_heading_line2' => ['label' => 'عنوان التطبيق — السطر الثاني', 'type' => 'text', 'default' => 'تطبيقنا'],
                ],
            ],
            'partners' => [
                'label' => 'قسم الشراكات',
                'hint' => 'العنوان أعلى شعارات الشركاء — الشعارات تُدار من الأسفل',
                'fields' => [
                    'partners_title' => ['label' => 'عنوان الشراكات', 'type' => 'text', 'default' => 'شركاء يثقون بنا'],
                ],
            ],
            'loyalty' => [
                'label' => 'برنامج الولاء',
                'hint' => 'نادي النقاط الذهبي',
                'fields' => [
                    'loyalty_kicker' => ['label' => 'الشارة', 'type' => 'text', 'default' => 'برنامج الولاء الأول في غزة'],
                    'loyalty_title' => ['label' => 'العنوان', 'type' => 'text', 'default' => 'كل وجبة تطلبها، تُقرّبك من وجبة مجانية تالية!'],
                    'loyalty_text' => ['label' => 'الوصف', 'type' => 'textarea', 'default' => 'مع برنامج نقاط سفرة غزة، تكسب نقاطاً مباشرة مع كل شيكل تصرفه في أي مطعم أو كافيه. استبدل نقاطك بأطباق فاخرة، مشروبات باردة، أو حلويات شهية دون أن تدفع قرشاً إضافياً.'],
                    'loyalty_cta' => ['label' => 'زر الاستبدال', 'type' => 'text', 'default' => 'استبدال النقاط الآن'],
                ],
            ],
            'why' => [
                'label' => 'لماذا سفرة غزة',
                'hint' => 'بطاقات الثقة الأربع',
                'fields' => [
                    'why_title' => ['label' => 'العنوان', 'type' => 'text', 'default' => 'لماذا يختار أهل غزة منصة سفرة؟'],
                    'why_kicker' => ['label' => 'التسمية الصغيرة', 'type' => 'text', 'default' => 'معايير الجودة والضيافة'],
                    'why_subtitle' => ['label' => 'النص التوضيحي', 'type' => 'textarea', 'default' => 'كباتن محترفون، مطاعم معتمدة، ودفع مرن – كل ما تحتاجه لتجربة طعام استثنائية بلا قلق'],
                    'why_1_title' => ['label' => 'البطاقة 1 — العنوان', 'type' => 'text', 'default' => 'أسرع شبكة كباتن'],
                    'why_1_text' => ['label' => 'البطاقة 1 — الوصف', 'type' => 'textarea', 'default' => 'كباتن مجهزون بحقائب عازلة تحافظ على حرارة وجبتك ونكهتها كأنها طازجة من الفرن.'],
                    'why_2_title' => ['label' => 'البطاقة 2 — العنوان', 'type' => 'text', 'default' => 'مطاعم معتمدة وموثوقة'],
                    'why_2_text' => ['label' => 'البطاقة 2 — الوصف', 'type' => 'textarea', 'default' => 'معايير نظافة وجودة صارمة لكل مطعم ينضم لشبكتنا لضمان أعلى مستوى من المذاق والصحة.'],
                    'why_3_title' => ['label' => 'البطاقة 3 — العنوان', 'type' => 'text', 'default' => 'دفع مرن 100%'],
                    'why_3_text' => ['label' => 'البطاقة 3 — الوصف', 'type' => 'textarea', 'default' => 'ادفع نقداً عند استلام طلبك، أو عبر محفظتك الرقمية ونقاط المكافآت بدون أي تعقيد أو عمولات.'],
                    'why_4_title' => ['label' => 'البطاقة 4 — العنوان', 'type' => 'text', 'default' => 'بفخر.. من غزة لأهلها'],
                    'why_4_text' => ['label' => 'البطاقة 4 — الوصف', 'type' => 'textarea', 'default' => 'منصة وطنية تدعم المشاريع والمطاعم والأيدي العاملة الغزية لتعزيز اقتصادنا المحلي.'],
                    'why_cta' => ['label' => 'زر البطاقات', 'type' => 'text', 'default' => 'عرض التفاصيل'],
                ],
            ],
            'join' => [
                'label' => 'بانر الانضمام',
                'hint' => 'دعوة المطاعم والكباتن',
                'fields' => [
                    'join_title' => ['label' => 'العنوان', 'type' => 'textarea', 'default' => "انضم إلى سفرة غزة\nكشريك مطعم أو كابتن توصيل"],
                    'join_cta_restaurant' => ['label' => 'زر المطعم', 'type' => 'text', 'default' => 'التسجيل كمطعم'],
                    'join_cta_courier' => ['label' => 'زر الديلفري', 'type' => 'text', 'default' => 'التسجيل كديلفري'],
                    'join_image' => ['label' => 'صورة الخلفية', 'type' => 'image', 'default' => '', 'default_public' => 'images/categories/sweets.jpg'],
                ],
            ],
        ];
    }

    public static function defaults(): array
    {
        $defaults = [];
        foreach (static::schema() as $section) {
            foreach ($section['fields'] as $key => $field) {
                $defaults[$key] = (string) ($field['default'] ?? '');
            }
        }

        return $defaults;
    }

    public static function stored(): array
    {
        $raw = Setting::value(self::SETTING_KEY);
        if (is_array($raw)) {
            return $raw;
        }
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function values(): array
    {
        return array_merge(static::defaults(), static::stored());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $values = static::values();

        return $values[$key] ?? $default ?? (static::defaults()[$key] ?? '');
    }

    public static function imageUrl(string $key): string
    {
        $path = (string) static::get($key, '');
        if ($path !== '') {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            return Storage::disk('public')->url($path);
        }

        foreach (static::schema() as $section) {
            if (isset($section['fields'][$key]['default_public'])) {
                return asset($section['fields'][$key]['default_public']);
            }
        }

        return '';
    }

    public static function save(array $values): void
    {
        $allowed = array_keys(static::defaults());
        $current = static::stored();
        foreach ($allowed as $key) {
            if (array_key_exists($key, $values)) {
                $current[$key] = is_string($values[$key]) ? $values[$key] : (string) $values[$key];
            }
        }

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            [
                'label' => 'محتوى الصفحة الرئيسية',
                'value' => json_encode($current, JSON_UNESCAPED_UNICODE),
            ]
        );
        Setting::forgetCache();
    }

    public static function fieldKeysByType(string $type): array
    {
        $keys = [];
        foreach (static::schema() as $section) {
            foreach ($section['fields'] as $key => $field) {
                if (($field['type'] ?? 'text') === $type) {
                    $keys[] = $key;
                }
            }
        }

        return $keys;
    }
}
