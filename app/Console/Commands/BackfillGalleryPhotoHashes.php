<?php

namespace App\Console\Commands;

use App\Models\GalleryPhoto;
use App\Services\ImageDuplicateDetector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillGalleryPhotoHashes extends Command
{
    protected $signature = 'gallery:backfill-hashes';

    protected $description = '重複検出機能の導入前にアップロードされた写真にハッシュを計算して保存する';

    public function handle(ImageDuplicateDetector $duplicateDetector): int
    {
        $photos = GalleryPhoto::whereNull('file_hash')->get();

        $this->info("{$photos->count()}件のハッシュを計算します。");

        foreach ($photos as $photo) {
            $path = Storage::disk('public')->path($photo->file_path);

            if (! is_file($path)) {
                $this->warn("ファイルが見つかりません（ID {$photo->id}）: {$photo->file_path}");
                continue;
            }

            $photo->update([
                'file_hash' => $duplicateDetector->fileHash($path),
                'phash'     => $duplicateDetector->perceptualHash($path),
            ]);
        }

        $this->info('完了しました。');

        return self::SUCCESS;
    }
}
