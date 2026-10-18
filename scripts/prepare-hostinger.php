<?php

/**
 * Build a Hostinger-ready zip:
 *   php scripts/prepare-hostinger.php
 *   php scripts/prepare-hostinger.php --with-vendor
 *   php scripts/prepare-hostinger.php --skip-build
 *
 * Extract the zip inside the domain folder (next to public_html), not inside public_html.
 */

$root = dirname(__DIR__);
$dist = $root.DIRECTORY_SEPARATOR.'hostinger-dist';
$withVendor = in_array('--with-vendor', $argv, true);
$skipBuild = in_array('--skip-build', $argv, true);

if (! $skipBuild) {
    echo "Building frontend assets...\n";
    passthru(npmCommand().' run build', $buildStatus);
    if ($buildStatus !== 0) {
        fwrite(STDERR, "npm run build failed. Fix Vite errors or re-run with --skip-build after a successful build.\n");
        exit(1);
    }
}

$manifest = $root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'manifest.json';
if (! is_file($manifest)) {
    fwrite(STDERR, "public/build/manifest.json is missing. Run npm run build first.\n");
    exit(1);
}

if (! is_dir($dist) && ! mkdir($dist, 0775, true) && ! is_dir($dist)) {
    fwrite(STDERR, "Could not create hostinger-dist.\n");
    exit(1);
}

$zipPath = $dist.DIRECTORY_SEPARATOR.'sofragaza-hostinger-'.date('Ymd_His').'.zip';
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Could not create {$zipPath}\n");
    exit(1);
}

$excludeNames = [
    '.git',
    '.github',
    '.idea',
    '.vscode',
    '.cursor',
    'node_modules',
    'hostinger-dist',
    'audit-assets',
    'tests',
    'canvases',
    'reports',
    '.phpunit.cache',
    'storage/pail',
];

if (! $withVendor) {
    $excludeNames[] = 'vendor';
}

$excludeFiles = [
    '.env',
    '.env.backup',
    '.env.production',
    'Thumbs.db',
    '.DS_Store',
    'Homestead.json',
    'Homestead.yaml',
    'phpunit.xml',
    'phpunit.xml.dist',
    'hostinger-dist-listing.txt',
    'list-large-files.php',
    'test_mockup.html',
    'current_section.png',
    'Logo sofra presentation.pdf',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$added = 0;
foreach ($iterator as $file) {
    $absolute = $file->getPathname();
    $relative = ltrim(str_replace('\\', '/', substr($absolute, strlen($root))), '/');

    if ($relative === '' || shouldExclude($relative, $excludeNames, $excludeFiles)) {
        continue;
    }

    $target = remapPublicPath($relative);

    if ($file->isDir()) {
        $zip->addEmptyDir($target);
        continue;
    }

    $contents = @file_get_contents($absolute);
    if ($contents === false) {
        fwrite(STDERR, "Skipping unreadable file: {$relative}\n");
        continue;
    }

    if ($zip->addFromString($target, $contents) !== true) {
        fwrite(STDERR, "Could not add {$relative}\n");
        $zip->close();
        @unlink($zipPath);
        exit(1);
    }
    $added++;
}

if ($zip->close() !== true) {
    fwrite(STDERR, "Could not write {$zipPath}. Close the folder if it is open in Explorer, then retry.\n");
    @unlink($zipPath);
    exit(1);
}

if (! is_file($zipPath) || filesize($zipPath) < 1024) {
    fwrite(STDERR, "Zip was not created correctly.\n");
    exit(1);
}

echo "Created {$zipPath} (".number_format(filesize($zipPath) / 1048576, 1)." MB, {$added} files)\n";
echo $withVendor
    ? "Vendor is included. After upload: copy .env.hostinger.example to .env, fill secrets, then php artisan hostinger:setup\n"
    : "Vendor is excluded. After upload: composer install --no-dev --optimize-autoloader, copy .env.hostinger.example to .env, then php artisan hostinger:setup\n";
echo "Extract this zip in /home/uXXXX/domains/YOUR-DOMAIN/ so Laravel sits beside public_html.\n";

function npmCommand(): string
{
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'npm.cmd' : 'npm';
}

function remapPublicPath(string $relative): string
{
    if ($relative === 'public') {
        return 'public_html';
    }

    if (str_starts_with($relative, 'public/')) {
        return 'public_html/'.substr($relative, strlen('public/'));
    }

    return $relative;
}

function shouldExclude(string $relative, array $excludeNames, array $excludeFiles): bool
{
    $parts = explode('/', $relative);
    foreach ($parts as $part) {
        if (in_array($part, $excludeNames, true)) {
            return true;
        }
        if (str_ends_with($part, '.zip') || str_ends_with($part, '.sqlite') || str_ends_with($part, '.log')) {
            return true;
        }
    }

    $base = basename($relative);
    if (in_array($base, $excludeFiles, true)) {
        return true;
    }

    if ($relative === 'public/hot' || str_ends_with($relative, '/hot')) {
        return true;
    }

    if (str_starts_with($base, '.env.') && $base !== '.env.hostinger.example' && $base !== '.env.example') {
        return true;
    }

    if (str_starts_with($relative, 'storage/framework/cache/data/') && ! str_ends_with($relative, '.gitignore')) {
        return true;
    }
    if (str_starts_with($relative, 'storage/framework/sessions/') && ! str_ends_with($relative, '.gitignore')) {
        return true;
    }
    if (str_starts_with($relative, 'storage/framework/views/') && ! str_ends_with($relative, '.gitignore')) {
        return true;
    }
    if (str_starts_with($relative, 'storage/logs/') && ! str_ends_with($relative, '.gitignore')) {
        return true;
    }
    if (str_starts_with($relative, 'storage/app/public/') && ! in_array(basename($relative), ['.gitignore', '.htaccess'], true)) {
        return true;
    }

    return false;
}
