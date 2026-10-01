<?php

namespace Database\Seeders;

use App\Models\HomePartner;
use App\Models\Membership;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\RestaurantPlan;
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
                'is_super_admin' => true,
                'admin_active' => true,
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

        User::query()->updateOrCreate(
            ['phone' => '0593003003'],
            [
                'name' => 'سامي الدلفري',
                'email' => 'courier@sofra.ps',
                'password' => Hash::make('123456'),
                'role' => 'courier',
                'courier_status' => User::COURIER_APPROVED,
                'bike_type' => User::BIKE_BICYCLE,
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
                'description' => 'خصم 5% على كل طلب، ونقاط إضافية بمعدل 1.25 نقطة بدل نقطة واحدة.',
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
                'description' => 'خصم 10% على كل طلب، توصيل مجاني، ونقاط إضافية بمعدل 1.5 نقطة بدل نقطة واحدة.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        RestaurantPlan::seedDefaults();

        $settings = [
            ['key' => 'delivery_fee', 'value' => '10', 'label' => 'رسوم التوصيل (شيكل)'],
            [
                'key' => 'delivery_fees_by_area',
                'value' => json_encode([
                    'الرمال' => 10,
                    'تل الهوى' => 10,
                    'النصر' => 10,
                    'الشجاعية' => 12,
                    'البلدة القديمة' => 10,
                    'الميناء' => 10,
                    'دير البلح' => 15,
                    'خانيونس' => 20,
                ], JSON_UNESCAPED_UNICODE),
                'label' => 'رسوم التوصيل حسب المناطق',
            ],
            ['key' => 'points_per_amount', 'value' => '1', 'label' => 'كل كم شيكل = نقطة اكتساب واحدة (عام لكل المطاعم)'],
            ['key' => 'points_redeem_per_amount', 'value' => '1', 'label' => 'كل كم شيكل = نقطة استبدال واحدة (عام)'],
            ['key' => 'points_include_delivery', 'value' => '1', 'label' => 'احتساب التوصيل في اكتساب النقاط (1 نعم / 0 لا)'],
            ['key' => 'drink_points', 'value' => '20', 'label' => 'نقاط استبدال مشروب'],
            ['key' => 'meal_points', 'value' => '50', 'label' => 'نقاط استبدال وجبة'],
            ['key' => 'referral_inviter_points', 'value' => '50', 'label' => 'نقاط الداعي عند انضمام صديق بكوده'],
            ['key' => 'referral_invitee_points', 'value' => '50', 'label' => 'نقاط الصديق الجديد عند إدخال كود الدعوة'],
            ['key' => 'restaurant_expiry_warning_days', 'value' => '7', 'label' => 'تنبيه انتهاء عرض المطعم قبل (أيام)'],
            ['key' => 'membership_expiry_warning_days', 'value' => '3', 'label' => 'تنبيه انتهاء عضوية الزبون قبل (أيام)'],
            ['key' => 'restaurant_listing_days', 'value' => '90', 'label' => 'مدة عرض المطعم بعد الموافقة (أيام)'],
            ['key' => 'bank_name', 'value' => 'بنك فلسطين', 'label' => 'اسم البنك للتحويل'],
            ['key' => 'bank_account_number', 'value' => '2345678', 'label' => 'رقم الحساب البنكي'],
            ['key' => 'bank_iban', 'value' => 'PS04PALS000000000002345678', 'label' => 'رقم الآيبان (IBAN)'],
            ['key' => 'bank_beneficiary_name', 'value' => 'سفرة غزة — Sofra Gaza', 'label' => 'اسم المستفيد البنكي'],
            ['key' => 'jawwal_pay_number', 'value' => '0599000000', 'label' => 'رقم محفظة جوال باي'],
            ['key' => 'jawwal_pay_name', 'value' => 'محفظة سفرة غزة', 'label' => 'اسم صاحب محفظة جوال باي'],
            ['key' => 'palpay_number', 'value' => '0599000000', 'label' => 'رقم محفظة بال باي (PalPay)'],
            ['key' => 'palpay_name', 'value' => 'محفظة بال باي — سفرة غزة', 'label' => 'اسم صاحب محفظة بال باي'],
            ['key' => 'payment_instructions_note', 'value' => 'يرجى كتابة رقم هاتفك أو رقم الطلب في ملاحظات التحويل، ورفع صورة الإشعار للمراجعة الفورية.', 'label' => 'ملاحظات التحويل للزبائن'],
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
                'cuisine' => 'palestinian',
                'area' => 'الرمال',
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
                'cuisine' => 'cafe',
                'area' => 'الرمال',
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
                'cuisine' => 'grill',
                'area' => 'الشجاعية',
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
                    ['عيران', 'مشروبات', 5, 'لبن مع نعناع وملح.', 'images/dishes/ayran.jpg'],
                    ['بطاطا مقلية', 'مقبلات', 8, 'بطاطا مقرمشة.'],
                ],
            ],
            [
                'name' => 'كافي زمان',
                'type' => 'cafe',
                'cuisine' => 'cafe',
                'area' => 'البلدة القديمة',
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
                'cuisine' => 'seafood',
                'area' => 'الميناء',
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
            [
                'name' => 'شاورما العمدة',
                'type' => 'restaurant',
                'cuisine' => 'shawarma',
                'area' => 'الرمال',
                'description' => 'شاورما دجاج ولحمة على الصاج، خبز طازج وطحينة البيت.',
                'phone' => '0592001006',
                'address' => 'الرمال — شارع الوحدة، غزة',
                'is_featured' => true,
                'expires_in' => 70,
                'menu' => [
                    ['ساندوتش شاورما دجاج', 'ساندويش', 18, 'شاورما دجاج على الصاج مع ثوم وطحينة.'],
                    ['شاورما لحم', 'ساندويش', 22, 'لحم متبل على الصاج.'],
                    ['صحن شاورما مشكل', 'وجبات', 32, 'دجاج ولحمة مع بطاطا وخبز صاج.'],
                    ['بطاطا شاورما', 'مقبلات', 12, 'بطاطا مع شاورما وصوص الثوم.'],
                ],
            ],
            [
                'name' => 'بيتزا ميرامار',
                'type' => 'restaurant',
                'cuisine' => 'pizza',
                'area' => 'تل الهوى',
                'description' => 'بيتزا فرن حجري وفطائر جبنة يومية.',
                'phone' => '0592001007',
                'address' => 'تل الهوى، شارع النفق',
                'is_featured' => true,
                'expires_in' => 60,
                'menu' => [
                    ['بيتزا مارغريتا', 'بيتزا', 28, 'صلصة بندورة وجبنة موزاريلا.'],
                    ['بيتزا خضار', 'بيتزا', 30, 'فلفل، زيتون، ومشروم.'],
                    ['فطيرة جبنة', 'فطائر', 14, 'عجينة طرية بالجبنة.'],
                    ['فطيرة زعتر', 'فطائر', 10, 'زعتر بلدي وزيت زيتون.'],
                ],
            ],
            [
                'name' => 'برجر الرمال',
                'type' => 'restaurant',
                'cuisine' => 'burger',
                'area' => 'الرمال',
                'description' => 'برجر لحم ودجاج وساندويشات جاهزة للتوصيل.',
                'phone' => '0592001008',
                'address' => 'الرمال — شارع عمر المختار',
                'is_featured' => false,
                'expires_in' => 50,
                'menu' => [
                    ['برجر لحم كلاسيك', 'برجر', 24, 'لحم مشوي مع خس وصوص البيت.'],
                    ['برجر دجاج مقرمش', 'برجر', 22, 'دجاج مقلي في خبز طري.'],
                    ['ساندويش فيليه', 'ساندويش', 20, 'فيليه دجاج مع ثوم.'],
                    ['بطاطا مقلية', 'مقبلات', 8, 'حصّة كبيرة.'],
                ],
            ],
            [
                'name' => 'حلويات الدحدوح',
                'type' => 'restaurant',
                'cuisine' => 'sweets',
                'area' => 'الرمال',
                'description' => 'كنافة نابلسية وحلويات عربية طازجة يومياً.',
                'phone' => '0592001009',
                'address' => 'الرمال — شارع الجلاء',
                'is_featured' => true,
                'expires_in' => 80,
                'menu' => [
                    ['كنافة نابلسية', 'حلويات', 16, 'كنافة ناعمة بالجبنة والسمن.'],
                    ['بقلاوة', 'حلويات', 12, 'طبقات عجين مع قطر وفستق.'],
                    ['هريسة', 'حلويات', 10, 'هريسة بالقطر.'],
                    ['كعك العيد', 'حلويات', 8, 'حسب الموسم.'],
                ],
            ],
            [
                'name' => 'فرن البلد',
                'type' => 'restaurant',
                'cuisine' => 'breakfast',
                'area' => 'النصر',
                'description' => 'فطور غزي: مناقيش، بيض، ومعجنات من التنور.',
                'phone' => '0592001010',
                'address' => 'شارع النصر، غزة',
                'is_featured' => false,
                'expires_in' => 45,
                'menu' => [
                    ['مناقيش زعتر', 'فطور', 8, 'من التنور مع زيت زيتون.'],
                    ['مناقيش جبنة', 'فطور', 10, 'عجينة طرية بالجبنة.'],
                    ['فطور مشكل', 'فطور', 22, 'بيض، أجبان، وزيتون.'],
                    ['كرواسون زعتر', 'معجنات', 9, 'صباحي ساخن.'],
                ],
            ],
        ];

        foreach ($places as $place) {
            $restaurant = Restaurant::query()->updateOrCreate(
                ['name' => $place['name']],
                [
                    'type' => $place['type'],
                    'cuisine' => $place['cuisine'] ?? null,
                    'area' => $place['area'] ?? null,
                    'description' => $place['description'],
                    'phone' => $place['phone'],
                    'address' => $place['address'],
                    'starts_at' => now()->subDays(5)->toDateString(),
                    'expires_at' => now()->addDays($place['expires_in'])->toDateString(),
                    'is_active' => true,
                    'is_featured' => $place['is_featured'],
                    'verification_status' => Restaurant::VERIFICATION_APPROVED,
                ]
            );

            foreach ($place['menu'] as $item) {
                $payload = [
                    'category' => $item[1],
                    'price' => $item[2],
                    'description' => $item[3],
                    'is_available' => true,
                ];
                if (isset($item[4])) {
                    $payload['image_path'] = $item[4];
                }
                MenuItem::query()->updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'name' => $item[0],
                    ],
                    $payload
                );
            }
        }

        $sampleUsers = [
            ['name' => 'محمد الهسي', 'phone' => '0597111222', 'email' => 'mohammed@example.com'],
            ['name' => 'أم يوسف الغزية', 'phone' => '0597222333', 'email' => 'om_yousef@example.com'],
            ['name' => 'خالد النجار', 'phone' => '0597333444', 'email' => 'khaled@example.com'],
            ['name' => 'منى الكرد', 'phone' => '0597444555', 'email' => 'muna@example.com'],
            ['name' => 'إياد حبيب', 'phone' => '0597555666', 'email' => 'eyad@example.com'],
        ];

        $users = [$customer];
        foreach ($sampleUsers as $u) {
            $users[] = User::query()->updateOrCreate(
                ['phone' => $u['phone']],
                [
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => Hash::make('123456'),
                    'role' => 'customer',
                    'points_balance' => rand(15, 60),
                    'wallet_balance' => rand(50, 250),
                ]
            );
        }

        $allRestaurants = Restaurant::all();
        $sampleReviews = [
            ['rating' => 5, 'comment' => 'الأكل واصل سخن وطازج والتوصيل سريع جداً، تجربة ممتازة وبنصح فيهم بشدة.'],
            ['rating' => 5, 'comment' => 'شغل مرتب ونظافة عالية، ونكهة أصلية على أصولها. بارك الله فيكم.'],
            ['rating' => 4, 'comment' => 'الطعم جداً زاكي والكمية وفيرة، التوصيل تأخر 5 دقائق فقط بس الأكل عوّض كل شي.'],
            ['rating' => 5, 'comment' => 'الخبز طازج والمشاوي متبلة صح، أفضل تجربة طلب أونلاين في غزة.'],
            ['rating' => 5, 'comment' => 'خدمة ممتازة وتغليف متقن حافظ على سخونة الوجبة. سفرة غزة ما قصرتوا.'],
            ['rating' => 4, 'comment' => 'جودة ممتازة وسعر مناسب، بنطلب من عندهم دايماً.'],
        ];

        foreach ($allRestaurants as $index => $restaurant) {
            $count = rand(3, 5);
            for ($i = 0; $i < $count; $i++) {
                $user = $users[($index + $i) % count($users)];
                $sample = $sampleReviews[($index + $i) % count($sampleReviews)];

                \App\Models\Review::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'restaurant_id' => $restaurant->id,
                    ],
                    [
                        'rating' => $sample['rating'],
                        'comment' => $sample['comment'],
                        'is_approved' => true,
                        'created_at' => now()->subDays(rand(1, 20))->subHours(rand(1, 12)),
                    ]
                );
            }
        }

        // Seed default coupons
        $coupons = [
            [
                'code' => 'GAZA10',
                'type' => 'percent',
                'value' => 10,
                'min_order_amount' => 20,
                'max_discount' => 15,
                'description' => 'خصم 10% بحد أقصى 15 ₪ للطلبات فوق 20 ₪',
            ],
            [
                'code' => 'SOFRA15',
                'type' => 'percent',
                'value' => 15,
                'min_order_amount' => 50,
                'max_discount' => 25,
                'description' => 'خصم 15% للطلبات العائلية فوق 50 ₪',
            ],
            [
                'code' => 'WELCOME',
                'type' => 'fixed',
                'value' => 5,
                'min_order_amount' => 15,
                'description' => 'خصم 5 ₪ ترحيبي على أي طلب',
            ],
        ];

        foreach ($coupons as $c) {
            \App\Models\Coupon::query()->updateOrCreate(
                ['code' => $c['code']],
                array_merge($c, ['is_active' => true])
            );
        }

        $partners = [
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
        foreach ($partners as $partner) {
            HomePartner::query()->updateOrCreate(
                ['name' => $partner['name']],
                array_merge($partner, ['is_active' => true, 'url' => null])
            );
        }
    }
}
