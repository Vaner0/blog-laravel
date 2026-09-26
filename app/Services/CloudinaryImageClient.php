<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use RuntimeException;

class CloudinaryImageClient
{
    /**
     * @return array{url: string, public_id: string}
     */
    public function upload(string $path): array
    {
        $response = $this->cloudinary()
            ->uploadApi()
            ->upload($path, [
                'folder' => 'blog/articles',
                'resource_type' => 'image',
                'format' => 'webp',
            ]);

        $url = $response['secure_url'] ?? null;
        $publicId = $response['public_id'] ?? null;

        if (! is_string($url) || $url === '' || ! is_string($publicId) || $publicId === '') {
            throw new RuntimeException('Cloudinary a retourné une réponse d’image invalide.');
        }

        return [
            'url' => $url,
            'public_id' => $publicId,
        ];
    }

    public function delete(string $publicId): void
    {
        $this->cloudinary()
            ->uploadApi()
            ->destroy($publicId, ['invalidate' => true]);
    }

    private function cloudinary(): Cloudinary
    {
        $url = config('services.cloudinary.url');

        if (! is_string($url) || trim($url) === '') {
            throw new RuntimeException('La configuration Cloudinary (CLOUDINARY_URL) est manquante.');
        }

        return new Cloudinary($url);
    }
}
