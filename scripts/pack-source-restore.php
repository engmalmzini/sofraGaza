<?php

/**
 * Pack Laravel source folders that Hostinger File Manager often misses:
 * resources/, routes/, config/
 */

$root = dirname(__DIR__);
$dist = $root.DIRECTORY_SEPARATOR.'hostinger-dist';
$zipPath = $dist.DIRECTORY_SEPARATOR.'source-restore.zip';
$folders = ['resources', 'routes', 'config'];

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

$added = 0;
foreach ($folders as $folder) {
    $dir = $root.DIRECTORY_SEPARATOR.$folder;
    if (! is_dir($dir)) {
        fwrite(STDERR, "Missing {$folder}/\n");
        exit(1);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $absolute = $file->getPathname();
        $relative = $folder.'/'.ltrim(str_replace('\\', '/', substr($absolute, strlen($dir))), '/');
        $zip->addFromString($relative, file_get_contents($absolute));
        $added++;
    }
}

$zip->close();

$home = 'resources/views/home.blade.php';
$check = new ZipArchive();
$check->open($zipPath);
if ($check->locateName($home) === false) {
    $check->close();
    fwrite(STDERR, "home.blade.php missing from zip.\n");
    exit(1);
}
$check->close();

echo "Created {$zipPath} (".number_format(filesize($zipPath) / 1024, 1)." KB, {$added} files)\n";
echo "Upload source-restore.zip AND restore-source.php into public_html, then open:\n";
echo "https://sofragaza.co/restore-source.php?key=sofra-install-7k4m2p\n";
