<?php

namespace App\Services;

use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploadService
{
    private const VARIANTS = [
        'thumbnail' => 400,
        'medium' => 900,
        'large' => 1600,
    ];

    public function upload(UploadedFile $file, ?int $uploadedBy = null, array $metadata = []): MediaAsset
    {
        $checksum = hash_file('sha256', $file->getRealPath());
        $existing = MediaAsset::active()->where('checksum', $checksum)->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($file, $uploadedBy, $metadata, $checksum) {
            $disk = 'public';
            $directory = 'media/'.now()->format('Y/m');
            $extension = strtolower($file->getClientOriginalExtension());
            $fileName = (string) Str::uuid().'.'.$extension;
            $path = $directory.'/'.$fileName;

            Storage::disk($disk)->putFileAs($directory, $file, $fileName);

            [$width, $height] = $this->dimensions($file->getRealPath());

            $media = MediaAsset::create(array_merge([
                'disk' => $disk,
                'directory' => $directory,
                'path' => $path,
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => $extension,
                'file_size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
                'checksum' => $checksum,
                'uploaded_by' => $uploadedBy,
                'is_active' => true,
            ], $metadata));

            $this->generateVariants($media, $file->getRealPath());

            return $media;
        });
    }

    private function generateVariants(MediaAsset $media, string $sourcePath): void
    {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            return;
        }

        foreach (self::VARIANTS as $variant => $maxWidth) {
            try {
                $this->generateVariant($media, $sourcePath, $variant, $maxWidth);
            } catch (\Throwable $exception) {
                Log::warning('Media variant generation failed.', [
                    'media_asset_id' => $media->id,
                    'variant' => $variant,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function generateVariant(MediaAsset $media, string $sourcePath, string $variant, int $maxWidth): void
    {
        [$width, $height, $type] = getimagesize($sourcePath) ?: [null, null, null];

        if (! $width || ! $height) {
            return;
        }

        $targetWidth = min($width, $maxWidth);
        $targetHeight = (int) round($height * ($targetWidth / $width));

        $source = $this->imageResource($sourcePath, $type);

        if (! $source) {
            return;
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $directory = $media->directory.'/variants';
        $path = $directory.'/'.pathinfo($media->file_name, PATHINFO_FILENAME).'-'.$variant.'.webp';
        $tempPath = tempnam(sys_get_temp_dir(), 'media_variant_');

        imagewebp($canvas, $tempPath, 82);
        Storage::disk($media->disk)->put($path, file_get_contents($tempPath));

        imagedestroy($source);
        imagedestroy($canvas);
        @unlink($tempPath);

        $media->variants()->updateOrCreate(
            ['variant' => $variant],
            [
                'disk' => $media->disk,
                'path' => $path,
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'file_size' => Storage::disk($media->disk)->size($path),
                'width' => $targetWidth,
                'height' => $targetHeight,
            ]
        );
    }

    private function imageResource(string $path, int $type): mixed
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    private function dimensions(string $path): array
    {
        $size = @getimagesize($path);

        return $size ? [$size[0], $size[1]] : [null, null];
    }
}
