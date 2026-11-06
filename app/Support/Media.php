<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Media
{
    public static function url(?string $path): ?string
    {
        $path = self::normalize($path);

        if ($path === null) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    public static function normalize(?string $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path));

        if ($path === '') {
            return null;
        }

        $path = ltrim($path, '/');

        foreach (['storage/app/public/', 'app/public/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            }
        }

        return $path !== '' ? $path : null;
    }

    public static function fullPath(?string $path): ?string
    {
        $path = self::normalize($path);

        if ($path === null || str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//') || str_starts_with($path, 'images/')) {
            return null;
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $candidates = [
            Storage::disk('public')->path($path),
            storage_path('app/public/'.$path),
            storage_path('app/'.$path),
            public_path('storage/'.$path),
            public_path($path),
        ];

        foreach ($candidates as $full) {
            if (is_string($full) && is_file($full)) {
                return $full;
            }
        }

        return null;
    }
}
