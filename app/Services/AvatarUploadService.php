<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use ImagickException;
use RuntimeException;

class AvatarUploadService
{
    private const HEIC_EXTENSIONS = ['heic', 'heif'];

    /**
     * アバター写真を保存する。iPhoneで撮影したHEIC/HEIF画像は
     * ブラウザで表示できないため、JPEGに変換してから保存する。
     */
    public function store(UploadedFile $file, string $directory = 'avatars'): string
    {
        if (! $this->isHeic($file)) {
            return $file->store($directory, 'public');
        }

        return $this->storeConvertedToJpeg($file, $directory);
    }

    private function isHeic(UploadedFile $file): bool
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        return in_array($extension, self::HEIC_EXTENSIONS, true);
    }

    private function storeConvertedToJpeg(UploadedFile $file, string $directory): string
    {
        if (! class_exists(Imagick::class)) {
            throw new RuntimeException('この画像形式（HEIC）を処理できません。JPEGまたはPNG形式でお試しください。');
        }

        try {
            $image = new Imagick($file->getRealPath());
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(88);
            $image = $image->flattenImages();
        } catch (ImagickException $e) {
            throw new RuntimeException('この画像形式（HEIC）を処理できません。JPEGまたはPNG形式でお試しください。', 0, $e);
        }

        $path = trim($directory, '/').'/'.(string) Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $image->getImageBlob());
        $image->clear();

        return $path;
    }
}
