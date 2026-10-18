<?php

$root = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images';
if (! is_dir($root)) {
    fwrite(STDERR, "public/images missing\n");
    exit(1);
}

$saved = 0;
$bytes = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $path = $file->getPathname();
    $ext = strtolower($file->getExtension());
    if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        continue;
    }

    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
    $max = maxDimension($relative);
    $before = filesize($path);
    if ($before < 24 * 1024 && $max >= 1600) {
        continue;
    }

    $info = @getimagesize($path);
    if ($info === false) {
        continue;
    }

    [$width, $height, $type] = $info;
    $src = match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG => @imagecreatefrompng($path),
        default => false,
    };
    if (! $src) {
        continue;
    }

    $scale = min(1, $max / max($width, $height));
    $newW = max(1, (int) round($width * $scale));
    $newH = max(1, (int) round($height * $scale));

    if ($scale < 1) {
        $dst = imagecreatetruecolor($newW, $newH);
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($src);
        $src = $dst;
    }

    $tmp = $path.'.tmpopt';
    $ok = false;
    if ($type === IMAGETYPE_JPEG) {
        $ok = imagejpeg($src, $tmp, 72);
    } else {
        imagesavealpha($src, true);
        $ok = imagepng($src, $tmp, 8);
    }
    imagedestroy($src);

    if (! $ok || ! is_file($tmp)) {
        @unlink($tmp);
        continue;
    }

    $after = filesize($tmp);
    if ($after > 0 && $after < $before * 0.97) {
        rename($tmp, $path);
        $saved++;
        $bytes += ($before - $after);
        echo $relative.' '.round($before / 1024).'KB -> '.round($after / 1024).'KB'."\n";
    } else {
        unlink($tmp);
    }
}

echo "optimized {$saved} files, saved ".round($bytes / 1024)." KB\n";

function maxDimension(string $relative): int
{
    if (str_starts_with($relative, 'avatars/')) {
        return 160;
    }
    if (str_starts_with($relative, 'categories/') || str_starts_with($relative, 'partners/') || str_starts_with($relative, 'payments/') || str_starts_with($relative, 'dishes/')) {
        return 480;
    }
    if (str_starts_with($relative, 'app/')) {
        return 900;
    }
    if (str_contains($relative, 'hero')) {
        return 1100;
    }
    if (str_contains($relative, 'logo')) {
        return 512;
    }
    if (str_contains($relative, 'auth-banner')) {
        return 1400;
    }

    return 1200;
}

