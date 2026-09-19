<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['phone' => '0590000000'],
            [
                'name' => 'مدير سفرة',
                'email' => 'admin@sofra.ps',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        $customer = User::query()->updateOrCreate(
            ['phone' => '0591111111'],
            [
                'name' => 'أحمد الغزي',
                'email' => 'ahmad@example.com',
                'password' => Hash::make('123456'),
                'role' => 'customer',
                'points_balance' => 40,
            ]
        );

        if ($customer->pointTransactions()->doesntExist()) {
            $customer->pointTransactions()->create([
                'type' => 'adjust',
                'points' => 40,
                'description' => 'رصيد ترحيبي للتجربة',
            ]);
        }

        Membership::query()->updateOrCreate(
            ['name' => 'العضوية الأساسية'],
            [
                'monthly_price' => 50,
                'discount_percent' => 5,
                'free_delivery' => false,
                'points_multiplier' => 1.25,
                'description' => 'خصم 5% على كل طلب، ونقاط إضافية على كل 10 شيكل.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Membership::query()->updateOrCreate(
            ['name' => 'العضوية المميزة'],
            [
                'monthly_price' => 100,
                'discount_percent' => 10,
                'free_delivery' => true,
                'points_multiplier' => 1.50,
                'description' => 'خصم 10% على كل طلب، توصيل مجاني، ونقاط إضافية أعلى.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $settings = [
            ['key' => 'delivery_fee', 'value' => '10', 'label' => 'رسوم التوصيل (شيكل)'],
            ['key' => 'points_per_amount', 'value' => '10', 'label' => 'كل كم شيكل = نقطة واحدة'],
            ['key' => 'drink_points', 'value' => '20', 'label' => 'نقاط استبدال مشروب'],
            ['key' => 'meal_points', 'value' => '50', 'label' => 'نقاط استبدال وجبة'],
            ['key' => 'restaurant_expiry_warning_days', 'value' => '7', 'label' => 'تنبيه انتهاء عرض المطعم قبل (أيام)'],
            ['key' => 'membership_expiry_warning_days', 'value' => '3', 'label' => 'تنبيه انتهاء عضوية الزبون قبل (أيام)'],
            ['key' => 'restaurant_listing_days', 'value' => '90', 'label' => 'مدة عرض المطعم بعد الموافقة (أيام)'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'label' => $setting['label']]
            );
        }

        $places = [
            [
                'name' => 'مطعم دار الياسمين',
                'type' => 'restaurant',
                'description' => 'مأكولات فلسطينية بيتية: مقلوبة، مسخن، وقدرة على أصولها الغزية.',
                'phone' => '0592001001',
                'address' => 'الرمال — شارع الجلاء، غزة',
                'is_featured' => true,
                'expires_in' => 40,
                'menu' => [
                    ['مقلوبة دجاج', 'وجبات', 28, 'أرز مع دجاج وباذنجان وبهارات البيت.'],
                    ['مسخن رول', 'وجبات', 22, 'خبز طابون، دجاج، سماق وبصل.'],
                    ['قدرة لحمة', 'وجبات', 35, 'حمص وأرز ولحمة على الطريقة الغزية.'],
                    ['سلطة عربية', 'مقبلات', 10, 'بندورة، خيار، بقدونس وزيت زيتون.'],
                    ['ليموناضة نعناع', 'مشروبات', 8, 'ليمون طازج مع نعناع بلدي.'],
                    ['كنافة نابلسية', 'حلويات', 12, 'كنافة ناعمة بالجبنة.'],
                ],
            ],
            [
                'name' => 'كافي تِرا',
                'type' => 'cafe',
                'description' => 'قهوة مختصة، حلويات يومية، ومكان هادئ على شارع عمر المختار.',
                'phone' => '0592001002',
                'address' => 'شارع عمر المختار، غزة',
                'is_featured' => true,
                'expires_in' => 6,
                'menu' => [
                    ['اسبريسو', 'مشروبات', 8, 'جرعة مزدوجة من قهوة عربية مختصة.'],
                    ['لاتيه', 'مشروبات', 12, 'حليب مبخر مع اسبريسو.'],
                    ['آيس أمريكانو', 'مشروبات', 11, 'قهوة باردة لحر غزة.'],
                    ['كرواسون جبنة', 'وجبات', 10, 'معجنات صباحية.'],
                    ['تشيز كيك فراولة', 'حلويات', 14, 'قطعة يومية طازجة.'],
                    ['شاي أخضر بالنعناع', 'مشروبات', 7, 'شاي مغلي مع نعناع طازج.'],
                ],
            ],
            [
                'name' => 'مشاوي أبو العبد',
                'type' => 'restaurant',
                'description' => 'مشاوي فحم، كباب، وكباب دجاج. أكل شوارع غزة كما يجب أن يكون.',
                'phone' => '0592001003',
                'address' => 'الشجاعية، دوار أبو اسكندر',
                'is_featured' => true,
                'expires_in' => 90,
                'menu' => [
                    ['مشاوي مشكل', 'وجبات', 40, 'كباب، شيش، وكفتة مع خبز وسلطات.'],
                    ['كباب لحم', 'وجبات', 32, 'كباب فحم مع بصل سماق.'],
                    ['شيش طاووق', 'وجبات', 28, 'دجاج متبل على الفحم.'],
                    ['حمص باللحمة', 'مقبلات', 14, 'حمص سائل مع سمنة ولحمة مفرومة.'],
                    ['عيران', 'مشروبات', 5, 'لبن مع نعناع وملح.'],
                    ['بطاطا مقلية', 'مقبلات', 8, 'بطاطا مقرمشة.'],
                ],
            ],
            [
                'name' => 'كافي زمان',
                'type' => 'cafe',
                'description' => 'مزاج غزة القديم: قهوة هيل، شاي، وأراجيل في رواق حجري.',
                'phone' => '0592001004',
                'address' => 'البلدة القديمة، بجوار المسجد العمري',
                'is_featured' => false,
                'expires_in' => 20,
                'menu' => [
                    ['قهوة عربية بالهيل', 'مشروبات', 6, 'فنجان صغير على الأصول.'],
                    ['شاي أحمر', 'مشروبات', 5, 'شاي ثقيل مع نعناع.'],
                    ['كيك يومي', 'حلويات', 9, 'قطعة حسب المتوفر.'],
                    ['مناقيش زعتر', 'وجبات', 8, 'من التنور.'],
                    ['سندويشة جبنة عكاوي', 'وجبات', 12, 'خبز طازج مع زعتر وزيت.'],
                    ['موكا باردة', 'مشروبات', 13, 'شوكولاتة وقهوة وحليب.'],
                ],
            ],
            [
                'name' => 'مطعم السمك الأزرق',
                'type' => 'restaurant',
                'description' => 'سمك غزة الطازج: مقلي، مشوي، وصيادية رز.',
                'phone' => '0592001005',
                'address' => 'منطقة الميناء، غزة',
                'is_featured' => false,
                'expires_in' => 55,
                'menu' => [
                    ['صيادية سمك', 'وجبات', 38, 'أرز أحمر مع سمك مقلي.'],
                    ['سمك مشوي', 'وجبات', 45, 'حسب المتوفر يومياً.'],
                    ['سمك مقلي', 'وجبات', 36, 'مع بطاطا وسلطة طحينة.'],
                    ['حبار مقلي', 'مقبلات', 22, 'حلقات مقرمشة.'],
                    ['سلطة طحينة', 'مقبلات', 8, 'طحينة، ليمون وثوم.'],
                    ['ليموناضة', 'مشروبات', 7, 'طازجة.'],
                ],
            ],
        ];

        foreach ($places as $place) {
            $restaurant = Restaurant::query()->updateOrCreate(
                ['name' => $place['name']],
                [
                    'type' => $place['type'],
                    'description' => $place['description'],
                    'phone' => $place['phone'],
                    'address' => $place['address'],
                    'starts_at' => now()->subDays(5)->toDateString(),
                    'expires_at' => now()->addDays($place['expires_in'])->toDateString(),
                    'is_active' => true,
                    'is_featured' => $place['is_featured'],
                ]
            );

            foreach ($place['menu'] as $item) {
                MenuItem::query()->updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'name' => $item[0],
                    ],
                    [
                        'category' => $item[1],
                        'price' => $item[2],
                        'description' => $item[3],
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
