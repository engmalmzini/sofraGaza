<?php

$root = dirname(__DIR__);
$names = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$scan = [
    $root.DIRECTORY_SEPARATOR.'resources',
    $root.DIRECTORY_SEPARATOR.'app',
];

foreach ($scan as $dir) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $ext = strtolower($file->getExtension());
        if (! in_array($ext, ['php', 'js', 'blade.php', 'css'], true) && ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }
        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            continue;
        }
        if (preg_match_all('/material-symbols-outlined[^>]*>\s*([a-z0-9_]+)/', $contents, $m)) {
            foreach ($m[1] as $name) {
                $names[$name] = true;
            }
        }
        if (preg_match_all("/'icon'\s*=>\s*'([a-z0-9_]+)'/", $contents, $m)) {
            foreach ($m[1] as $name) {
                $names[$name] = true;
            }
        }
        if (preg_match_all('/icon\(\):\s*string.*?return match.*?default => \'([a-z0-9_]+)\'/s', $contents, $m)) {
            foreach ($m[1] as $name) {
                $names[$name] = true;
            }
        }
    }
}

$extra = [
    'add', 'add_photo_alternate', 'account_balance_wallet', 'account_circle', 'arrow_back', 'arrow_forward',
    'bolt', 'call', 'cancel', 'check', 'check_circle', 'chevron_left', 'close', 'content_copy', 'cooking',
    'credit_card', 'delete', 'delivery_dining', 'diamond', 'drag_indicator', 'edit', 'edit_note', 'emoji_events',
    'error', 'expand_more', 'explore', 'fastfood', 'history', 'home', 'home_pin', 'hourglass_top', 'image',
    'inbox', 'info', 'local_cafe', 'local_fire_department', 'local_offer', 'location_on', 'lock', 'login',
    'logout', 'lunch_dining', 'mail', 'menu', 'military_tech', 'monitoring', 'moped', 'north', 'note',
    'notifications', 'notifications_off', 'open_in_full', 'open_in_new', 'pause', 'payments', 'pedal_bike',
    'person', 'person_add', 'person_remove', 'play_arrow', 'rate_review', 'receipt', 'receipt_long', 'refresh',
    'remove', 'restart_alt', 'restaurant', 'restaurant_menu', 'save', 'schedule', 'search', 'search_off',
    'send', 'shopping_bag', 'shopping_cart', 'shopping_cart_checkout', 'soup_kitchen', 'south', 'star',
    'stars', 'storefront', 'table_rows', 'task_alt', 'tune', 'two_wheeler', 'unfold_more', 'verified',
    'verified_user', 'view_carousel', 'view_kanban', 'visibility', 'volume_up', 'workspace_premium',
    'confirmation_number', 'calendar_month', 'groups', 'done', 'event_busy', 'public', 'all_inclusive',
    'outdoor_grill', 'icecream', 'set_meal', 'expand_more', 'favorite', 'favorite_border',
];

foreach ($extra as $name) {
    $names[$name] = true;
}

$list = array_keys($names);
sort($list);

$out = $root.DIRECTORY_SEPARATOR.'hostinger-dist'.DIRECTORY_SEPARATOR.'icon-names.txt';
if (! is_dir(dirname($out))) {
    mkdir(dirname($out), 0775, true);
}
file_put_contents($out, implode(' ', $list));
echo count($list)." icons\n";
echo implode(',', $list)."\n";
