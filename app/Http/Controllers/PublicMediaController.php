<?php

namespace App\Http\Controllers;

use App\Support\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicMediaController
{
    public function show(string $path): BinaryFileResponse
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        abort_if($path === '' || str_contains($path, '..'), 404);

        foreach (['logs/', 'framework/', 'app/private/', '.env'] as $denied) {
            abort_if(str_starts_with($path, $denied) || $path === rtrim($denied, '/'), 404);
        }

        $full = Media::fullPath($path);
        abort_unless($full, 404);

        return response()->file($full, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
