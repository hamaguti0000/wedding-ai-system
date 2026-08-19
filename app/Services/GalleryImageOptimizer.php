<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryImageOptimizer
{
    private const MAX_EDGE = 2000;
    private const JPEG_QUALITY = 84;

    public function store(UploadedFile $file, string $directory): string
    {
        if (! extension_loaded('gd')) {
            return $file->store($directory, 'public');
        }

        $sourcePath = $file->getRealPath();
        if (! $sourcePath || ! is_file($sourcePath)) {
            return $file->store($directory, 'public');
        }

        $optimized = $this->optimizeToJpeg($sourcePath);
        if ($optimized === null) {
            return $file->store($directory, 'public');
        }

        $path = trim($directory, '/') . '/' . Str::random(40) . '.jpg';
        Storage::disk('public')->put($path, $optimized);

        return $path;
    }

    public function optimizeExistingPublicFile(string $relativePath): bool
    {
        if (! extension_loaded('gd')) {
            return false;
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        if (! is_file($absolutePath)) {
            return false;
        }

        $optimized = $this->optimizeToJpeg($absolutePath);
        if ($optimized === null) {
            return false;
        }

        file_put_contents($absolutePath, $optimized);

        return true;
    }

    private function optimizeToJpeg(string $absolutePath): ?string
    {
        @ini_set('memory_limit', '512M');

        $data = @file_get_contents($absolutePath);
        if ($data === false) {
            return null;
        }

        $src = @imagecreatefromstring($data);
        if (! $src) {
            return null;
        }

        $src = $this->applyExifOrientation($src, $absolutePath);

        $width = imagesx($src);
        $height = imagesy($src);
        if ($width <= 0 || $height <= 0) {
            imagedestroy($src);
            return null;
        }

        $scale = min(1, self::MAX_EDGE / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);
        imagecopyresampled($canvas, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imageinterlace($canvas, true);
        imagejpeg($canvas, null, self::JPEG_QUALITY);
        $jpeg = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($src);

        return $jpeg !== false ? $jpeg : null;
    }

    private function applyExifOrientation(\GdImage $image, string $absolutePath): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($absolutePath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
