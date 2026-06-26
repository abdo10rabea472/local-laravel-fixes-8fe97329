<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    private const SIZES = [
        'thumb' => [150, 150],
        'medium' => [500, 500],
        'large' => [1200, 1200],
    ];

    public function storeProductImages(UploadedFile $file, int $productId): array
    {
        $baseName = time() . '_' . Str::random(8);
        $sourceExt = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $directory = "products/{$productId}";

        // إذا كان WebP مدعومًا، نحفظ بـ webp لتقليل المساحة، وإلا نُبقي الامتداد الأصلي.
        $targetExt = (extension_loaded('gd') && function_exists('imagewebp')) ? 'webp' : $sourceExt;

        // إذا لم يكن GD متاحًا، خزّن الملف كما هو فقط.
        if (! extension_loaded('gd')) {
            $originalPath = $file->storeAs($directory, "{$baseName}.{$sourceExt}", 'public');
            return ['image' => $originalPath, 'thumb' => null, 'medium' => null, 'large' => null];
        }

        // نقرأ الملف من المسار المؤقت قبل نقله
        $source = $this->createImageResource($file->getRealPath(), $sourceExt);

        if (! $source) {
            // فشل التحويل: نرجع لتخزين الملف الأصلي كما هو
            $originalPath = $file->storeAs($directory, "{$baseName}.{$sourceExt}", 'public');
            return ['image' => $originalPath, 'thumb' => null, 'medium' => null, 'large' => null];
        }

        $originalPath = "{$directory}/{$baseName}.{$targetExt}";
        Storage::disk('public')->makeDirectory($directory);
        $this->saveImage($source, Storage::disk('public')->path($originalPath), $targetExt);

        $paths = [
            'image' => $originalPath,
            'thumb' => null,
            'medium' => null,
            'large' => null,
        ];

        foreach (self::SIZES as $sizeName => [$width, $height]) {
            $resized = $this->resizeImage($source, $width, $height);
            if (! $resized) {
                continue;
            }

            $sizePath = "{$directory}/{$baseName}_{$sizeName}.{$targetExt}";
            $this->saveImage($resized, Storage::disk('public')->path($sizePath), $targetExt);
            imagedestroy($resized);
            $paths[$sizeName] = $sizePath;
        }

        imagedestroy($source);

        return $paths;
    }

    public function storeCategoryImage(UploadedFile $file, string $type = 'image'): string
    {
        return $this->storeOptimized($file, "categories/{$type}");
    }

    public function storeSettingImage(UploadedFile $file, string $key): string
    {
        return $this->storeOptimized($file, "settings/{$key}");
    }

    public function storeSectionBackgroundImage(UploadedFile $file): string
    {
        return $this->storeOptimized($file, 'sections/backgrounds');
    }

    /**
     * يخزّن الصورة بصيغة WebP إن أمكن، وإلا بامتدادها الأصلي.
     */
    private function storeOptimized(UploadedFile $file, string $directory): string
    {
        $baseName = time() . '_' . Str::random(8);
        $sourceExt = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            return $file->storeAs($directory, "{$baseName}.{$sourceExt}", 'public');
        }

        $source = $this->createImageResource($file->getRealPath(), $sourceExt);
        if (! $source) {
            return $file->storeAs($directory, "{$baseName}.{$sourceExt}", 'public');
        }

        Storage::disk('public')->makeDirectory($directory);
        $path = "{$directory}/{$baseName}.webp";
        $this->saveImage($source, Storage::disk('public')->path($path), 'webp');
        imagedestroy($source);

        return $path;
    }

    public function deletePaths(?string ...$paths): void
    {
        $filtered = array_filter($paths);

        if ($filtered) {
            Storage::disk('public')->delete($filtered);
        }
    }

    private function createImageResource(string $path, string $extension)
    {
        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'gif' => @imagecreatefromgif($path),
            default => false,
        };
    }

    private function resizeImage($source, int $maxWidth, int $maxHeight)
    {
        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);

        if ($srcWidth <= 0 || $srcHeight <= 0) {
            return false;
        }

        $ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight, 1);
        $newWidth = (int) max(1, round($srcWidth * $ratio));
        $newHeight = (int) max(1, round($srcHeight * $ratio));

        $dest = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);

        imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);

        return $dest;
    }

    private function saveImage($image, string $path, string $extension): void
    {
        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $path, 85),
            'png' => imagepng($image, $path, 6),
            'webp' => function_exists('imagewebp') ? imagewebp($image, $path, 85) : imagejpeg($image, $path, 85),
            'gif' => imagegif($image, $path),
            default => imagejpeg($image, $path, 85),
        };
    }
}
