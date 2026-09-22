<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait HasStoredReceipt
{
    public function receiptUrl(): ?string
    {
        if (! filled($this->transfer_receipt_path)) {
            return null;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $this->transfer_receipt_path), '/');
    }

    public function receiptAbsolutePath(): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $this->transfer_receipt_path), '/');

        if ($path === '') {
            return null;
        }

        $candidates = [
            Storage::disk('public')->path($path),
            storage_path('app/public/'.$path),
            storage_path('app/'.$path),
            public_path('storage/'.$path),
            public_path($path),
        ];

        if (str_contains($path, '/')) {
            $candidates[] = Storage::disk('public')->path(basename($path));
        }

        foreach ($candidates as $full) {
            if (is_string($full) && is_file($full)) {
                return $full;
            }
        }

        return null;
    }

    public function hasReceiptFile(): bool
    {
        return $this->receiptAbsolutePath() !== null;
    }

    public function receiptDataUri(): ?string
    {
        $full = $this->receiptAbsolutePath();

        if (! $full) {
            return null;
        }

        $mime = mime_content_type($full) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($full));
    }

    public function receiptResponse(): BinaryFileResponse
    {
        $full = $this->receiptAbsolutePath();
        abort_unless($full, 404, 'إشعار الحوالة غير متوفر.');

        return response()->file($full);
    }
}
