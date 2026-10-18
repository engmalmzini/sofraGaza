<?php

$root = dirname(__DIR__);
$dist = $root.DIRECTORY_SEPARATOR.'hostinger-dist';
$zipPath = $dist.DIRECTORY_SEPARATOR.'assets-restore.zip';

if (is_file($zipPath)) {
    unlink($zipPath);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Could not create {$zipPath}\n");
    exit(1);
}

$added = 0;
$files = [
    'public/fonts/material-symbols-outlined.woff2' => 'fonts/material-symbols-outlined.woff2',
    'resources/views/partials/icon-font.blade.php' => 'resources/views/partials/icon-font.blade.php',
    'resources/views/partials/restaurant-card.blade.php' => 'resources/views/partials/restaurant-card.blade.php',
    'resources/views/partials/home-mobile.blade.php' => 'resources/views/partials/home-mobile.blade.php',
    'resources/views/partials/boosted-spotlight.blade.php' => 'resources/views/partials/boosted-spotlight.blade.php',
    'resources/views/partials/saved-dish-row.blade.php' => 'resources/views/partials/saved-dish-row.blade.php',
    'resources/views/home.blade.php' => 'resources/views/home.blade.php',
    'resources/views/layouts/public.blade.php' => 'resources/views/layouts/public.blade.php',
    'resources/views/restaurants/partials/dish-card-mobile.blade.php' => 'resources/views/restaurants/partials/dish-card-mobile.blade.php',
    'resources/views/restaurants/partials/dish-card-desktop.blade.php' => 'resources/views/restaurants/partials/dish-card-desktop.blade.php',
    'resources/views/restaurants/partials/show-mobile.blade.php' => 'resources/views/restaurants/partials/show-mobile.blade.php',
    'resources/views/restaurants/partials/show-desktop.blade.php' => 'resources/views/restaurants/partials/show-desktop.blade.php',
    'resources/css/app.css' => 'resources/css/app.css',
    'resources/views/partials/app-download.blade.php' => 'resources/views/partials/app-download.blade.php',
    'resources/views/partials/splash.blade.php' => 'resources/views/partials/splash.blade.php',
    'resources/views/layouts/public.blade.php' => 'resources/views/layouts/public.blade.php',
    'resources/views/layouts/auth.blade.php' => 'resources/views/layouts/auth.blade.php',
    'resources/views/layouts/admin.blade.php' => 'resources/views/layouts/admin.blade.php',
    'resources/views/layouts/partner.blade.php' => 'resources/views/layouts/partner.blade.php',
    'resources/views/layouts/courier.blade.php' => 'resources/views/layouts/courier.blade.php',
    'config/brand.php' => 'config/brand.php',
];

foreach ($files as $from => $to) {
    $absolute = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $from);
    if (! is_file($absolute)) {
        fwrite(STDERR, "Missing {$from}\n");
        exit(1);
    }
    $zip->addFromString($to, file_get_contents($absolute));
    $added++;
}

$images = $root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images';
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($images, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $file) {
    if (! $file->isFile()) {
        continue;
    }
    $relative = 'images/'.ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($images))), '/');
    $zip->addFromString($relative, file_get_contents($file->getPathname()));
    $added++;
}

$build = $root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'build';
if (is_dir($build)) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($build, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $relative = 'build/'.ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($build))), '/');
        $zip->addFromString($relative, file_get_contents($file->getPathname()));
        $added++;
    }
}

$zip->close();
echo "Created {$zipPath} (".number_format(filesize($zipPath) / 1024, 1)." KB, {$added} files)\n";
