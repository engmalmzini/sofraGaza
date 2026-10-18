<?php

/**
 * Pack the full Laravel app/ tree for Hostinger File Manager restore.
 * Zip layout (extract inside public_html):
 *   app/Services/CartService.php
 *   app/Providers/AppServiceProvider.php
 *   ...
 */

$root = dirname(__DIR__);
$appDir = $root.DIRECTORY_SEPARATOR.'app';
$dist = $root.DIRECTORY_SEPARATOR.'hostinger-dist';
$zipPath = $dist.DIRECTORY_SEPARATOR.'app-restore.zip';

if (! is_dir($appDir)) {
    fwrite(STDERR, "app/ is missing.\n");
    exit(1);
}

if (! is_dir($dist) && ! mkdir($dist, 0775, true) && ! is_dir($dist)) {
    fwrite(STDERR, "Could not create hostinger-dist.\n");
    exit(1);
}

if (is_file($zipPath)) {
    unlink($zipPath);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Could not create {$zipPath}\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$added = 0;
foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $absolute = $file->getPathname();
    $relative = 'app/'.ltrim(str_replace('\\', '/', substr($absolute, strlen($appDir))), '/');
    $zip->addFromString($relative, file_get_contents($absolute));
    $added++;
}

$zip->close();

if (! is_file($appDir.DIRECTORY_SEPARATOR.'Services'.DIRECTORY_SEPARATOR.'CartService.php')) {
    fwrite(STDERR, "CartService.php missing from local app/Services.\n");
    exit(1);
}

echo "Created {$zipPath} (".number_format(filesize($zipPath) / 1024, 1)." KB, {$added} files)\n";
echo "Upload app-restore.zip AND restore-app.php into public_html, then open:\n";
echo "https://sofragaza.co/restore-app.php?key=sofra-install-7k4m2p\n";
