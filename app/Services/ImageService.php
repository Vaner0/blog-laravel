<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class ImageService
{
    public function __construct(private CloudinaryImageClient $cloudinary) {}

    /**
     * @return array{url: string, public_id: string}
     */
    public function storeArticleImage(UploadedFile $image): array
    {
        $source = null;
        $canvas = null;
        $temporaryPath = null;

        try {
            $source = $this->createSource($image);
            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);
            $targetWidth = min($sourceWidth, 1600);
            $targetHeight = (int) round($sourceHeight * ($targetWidth / $sourceWidth));
            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

            if (! $canvas instanceof \GdImage) {
                throw new RuntimeException('Impossible de préparer cette image.');
            }

            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
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

            $temporaryPath = tempnam(sys_get_temp_dir(), 'blog-image-');

            if ($temporaryPath === false) {
                throw new RuntimeException('Impossible de créer le fichier temporaire de l’image.');
            }

            if (! imagewebp($canvas, $temporaryPath, 78)) {
                throw new RuntimeException('Impossible de compresser cette image.');
            }

            return $this->cloudinary->upload($temporaryPath);
        } finally {
            if ($source instanceof \GdImage) {
                imagedestroy($source);
            }

            if ($canvas instanceof \GdImage) {
                imagedestroy($canvas);
            }

            if (is_string($temporaryPath) && is_file($temporaryPath) && ! unlink($temporaryPath)) {
                throw new RuntimeException('Impossible de supprimer le fichier temporaire de l’image.');
            }
        }
    }

    public function delete(?string $path, ?string $publicId = null): void
    {
        if ($publicId !== null) {
            $this->cloudinary->delete($publicId);

            return;
        }

        if ($path !== null && ! Str::startsWith($path, ['http://', 'https://'])) {
            $localPath = storage_path('app/public/'.$path);

            if (is_file($localPath) && ! unlink($localPath)) {
                throw new RuntimeException('Impossible de supprimer l’image locale.');
            }
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
