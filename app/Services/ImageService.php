<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageService
{
    public function storeArticleImage(UploadedFile $image): string
    {
        $source = $this->createSource($image);
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $maxWidth = 1600;
        $targetWidth = min($sourceWidth, $maxWidth);
        $targetHeight = (int) round($sourceHeight * ($targetWidth / $sourceWidth));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        $path = 'articles/'.uniqid('', true).'.webp';
        ob_start();
        imagewebp($canvas, null, 78);
        $contents = ob_get_clean();

        if ($contents === false) {
            imagedestroy($source);
            imagedestroy($canvas);
            throw new RuntimeException('Impossible de compresser cette image.');
        }

        Storage::disk('public')->put($path, $contents);
        imagedestroy($source);
        imagedestroy($canvas);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }

    private function createSource(UploadedFile $image): \GdImage
    {
        $source = match ($image->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($image->getRealPath()),
            'image/png' => imagecreatefrompng($image->getRealPath()),
            'image/webp' => imagecreatefromwebp($image->getRealPath()),
            default => false,
        };

        if (! $source instanceof \GdImage) {
            throw new RuntimeException('Format d’image non pris en charge.');
        }

        return $source;
    }
}
