<?php

namespace App\Support;

class AdminAccess
{
    public const MODULES = [
        'orders' => ['label' => 'الطلبات', 'hint' => 'لوحة الطلبات الحية وتغيير الحالة'],
        'delivery' => ['label' => 'التوصيل والمندوبون', 'hint' => 'تعيين الكباتن واعتمادهم والمستحقات'],
        'restaurants' => ['label' => 'المطاعم والكوفيهات', 'hint' => 'اعتماد المطاعم والمنيو'],
        'finance' => ['label' => 'المالية والإعلانات', 'hint' => 'الدخل والمصروف وتسويات المطاعم والإعلانات'],
        'customers' => ['label' => 'الزبائن والمحفظة', 'hint' => 'تعديل النقاط ورصيد المحفظة وشحن الرصيد'],
        'memberships' => ['label' => 'العضويات والاشتراكات', 'hint' => 'تفعيل العضوية ورفض الطلبات'],
        'reviews' => ['label' => 'التقييمات والآراء', 'hint' => 'إظهار أو حذف تقييمات الزبائن'],
        'coupons' => ['label' => 'أكواد الخصم', 'hint' => 'إنشاء وإيقاف الكوبونات'],
        'homepage' => ['label' => 'محتوى الرئيسية', 'hint' => 'البنرات وشركاء الواجهة'],
        'settings' => ['label' => 'إعدادات المنصة', 'hint' => 'الأرقام، التوصيل، وحسابات الدفع'],
        'audit' => ['label' => 'سجل التعديلات', 'hint' => 'مشاهدة من عدّل وماذا حصل ومتى'],
    ];

    public static function permissionForRoute(?string $name): string
    {
        $name = (string) $name;

        return match (true) {
            str_starts_with($name, 'admin.team') => 'team',
            str_starts_with($name, 'admin.audit') => 'audit',
            str_starts_with($name, 'admin.orders') => 'orders',
            str_starts_with($name, 'admin.delivery') => 'delivery',
            str_starts_with($name, 'admin.restaurants') => 'restaurants',
            str_starts_with($name, 'admin.listings') => 'restaurants',
            str_starts_with($name, 'admin.finance') => 'finance',
            str_starts_with($name, 'admin.boosts') => 'finance',
            str_starts_with($name, 'admin.users') => 'customers',
            str_starts_with($name, 'admin.wallet-topups') => 'customers',
            str_starts_with($name, 'admin.memberships') => 'memberships',
            str_starts_with($name, 'admin.subscriptions') => 'memberships',
            str_starts_with($name, 'admin.reviews') => 'reviews',
            str_starts_with($name, 'admin.coupons') => 'coupons',
            str_starts_with($name, 'admin.homepage') => 'homepage',
            str_starts_with($name, 'admin.settings') => 'settings',
            default => 'dashboard',
        };
    }

    public static function actionLabel(?string $routeName, string $method = 'POST'): string
    {
        $labels = [
            'admin.users.points' => 'تعديل نقاط زبون',
            'admin.users.wallet' => 'تعديل رصيد محفظة زبون',
            'admin.subscriptions.approve' => 'تفعيل اشتراك عضوية',
            'admin.subscriptions.reject' => 'رفض اشتراك عضوية',
            'admin.subscriptions.card' => 'تأكيد تسليم بطاقة عضوية',
            'admin.memberships.store' => 'إضافة عضوية',
            'admin.memberships.update' => 'تعديل عضوية',
            'admin.memberships.toggle' => 'تفعيل/إيقاف عضوية',
            'admin.memberships.destroy' => 'حذف عضوية',
            'admin.restaurants.store' => 'إضافة مطعم',
            'admin.restaurants.update' => 'تعديل بيانات مطعم',
            'admin.restaurants.approve' => 'اعتماد مطعم',
            'admin.restaurants.reject' => 'رفض مطعم',
            'admin.restaurants.suspend' => 'إيقاف مطعم',
            'admin.restaurants.unsuspend' => 'إعادة تشغيل مطعم',
            'admin.restaurants.destroy' => 'حذف مطعم',
            'admin.orders.update' => 'تغيير حالة طلب',
            'admin.orders.move' => 'نقل طلب على اللوحة',
            'admin.delivery.store' => 'إضافة مندوب',
            'admin.delivery.update' => 'تعديل مندوب',
            'admin.delivery.approve' => 'اعتماد مندوب',
            'admin.delivery.reject' => 'رفض مندوب',
            'admin.delivery.assign' => 'تعيين مندوب لطلب',
            'admin.delivery.unassign' => 'إلغاء تعيين مندوب',
            'admin.delivery.payouts.complete' => 'صرف مستحق كابتن',
            'admin.delivery.payouts.reject' => 'رفض طلب صرف كابتن',
            'admin.wallet-topups.approve' => 'اعتماد شحن محفظة',
            'admin.wallet-topups.reject' => 'رفض شحن محفظة',
            'admin.boosts.approve' => 'اعتماد إعلان مطعم',
            'admin.boosts.reject' => 'رفض إعلان مطعم',
            'admin.finance.restaurants.settle' => 'تسجيل تسوية مطعم',
            'admin.finance.settlements.destroy' => 'حذف تسوية مطعم',
            'admin.finance.boosts.store' => 'تسجيل إعلان من المالية',
            'admin.finance.boosts.destroy' => 'حذف إعلان',
            'admin.finance.expenses.store' => 'إضافة مصروف',
            'admin.finance.expenses.destroy' => 'حذف مصروف',
            'admin.finance.incomes.store' => 'إضافة دخل',
            'admin.finance.incomes.destroy' => 'حذف دخل',
            'admin.coupons.store' => 'إنشاء كوبون',
            'admin.coupons.update' => 'تعديل كوبون',
            'admin.coupons.toggle' => 'تفعيل/إيقاف كوبون',
            'admin.coupons.destroy' => 'حذف كوبون',
            'admin.reviews.toggle' => 'إظهار/إخفاء تقييم',
            'admin.reviews.destroy' => 'حذف تقييم',
            'admin.settings.update' => 'تعديل إعدادات المنصة',
            'admin.homepage.update' => 'تعديل محتوى الرئيسية',
            'admin.homepage.partners.store' => 'إضافة شريك للرئيسية',
            'admin.homepage.partners.update' => 'تعديل شريك للرئيسية',
            'admin.homepage.partners.destroy' => 'حذف شريك للرئيسية',
            'admin.team.store' => 'إضافة مدير للفريق',
            'admin.team.update' => 'تعديل صلاحيات مدير',
            'admin.team.destroy' => 'إيقاف مدير',
            'admin.listings.approve' => 'اعتماد اشتراك ظهور مطعم',
            'admin.listings.reject' => 'رفض اشتراك ظهور مطعم',
        ];

        if (isset($labels[$routeName])) {
            return $labels[$routeName];
        }

        if (str_contains((string) $routeName, 'menu-items')) {
            return match ($method) {
                'DELETE' => 'حذف صنف من منيو مطعم',
                'PUT', 'PATCH' => 'تعديل صنف منيو',
                default => 'إضافة صنف لمنيو مطعم',
            };
        }

        return 'تنفيذ إجراء في لوحة التحكم';
    }

    public static function skipAuditRoute(?string $name): bool
    {
        $name = (string) $name;

        return in_array($name, [
            'admin.orders.live',
            'admin.search.suggest',
            'admin.notifications.read',
            'admin.notifications.open',
        ], true);
    }
}
