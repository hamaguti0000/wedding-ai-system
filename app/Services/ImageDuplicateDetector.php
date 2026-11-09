<?php

namespace App\Services;

use App\Models\GalleryPhoto;

class ImageDuplicateDetector
{
    /**
     * 見た目がどの程度似ていれば「同じ写真」とみなすかの閾値（0〜64、小さいほど厳密）。
     * 複数人が同じ写真を再圧縮・リサイズして送ってきても検出できるよう、
     * 完全一致（0）よりやや緩めに設定している。
     */
    private const NEAR_DUPLICATE_THRESHOLD = 6;

    public function fileHash(string $absolutePath): ?string
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        $hash = hash_file('sha256', $absolutePath);

        return $hash !== false ? $hash : null;
    }

    /**
     * 8x8グレースケール平均ハッシュ（aHash）。
     * リサイズ・多少の再圧縮では値が変わりにくく、見た目が同じ写真同士は
     * ハミング距離が小さくなる。
     */
    public function perceptualHash(string $absolutePath): ?string
    {
        if (! extension_loaded('gd') || ! is_file($absolutePath)) {
            return null;
        }

        $data = @file_get_contents($absolutePath);
        if ($data === false) {
            return null;
        }

        $src = @imagecreatefromstring($data);
        if ($src === false) {
            return null;
        }

        $size = 8;
        $resized = imagecreatetruecolor($size, $size);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $size, $size, imagesx($src), imagesy($src));

        $values = [];
        $sum = 0;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $gray = (int) round((($rgb >> 16 & 0xFF) + ($rgb >> 8 & 0xFF) + ($rgb & 0xFF)) / 3);
                $values[] = $gray;
                $sum += $gray;
            }
        }

        $average = $sum / count($values);

        $bits = '';
        foreach ($values as $value) {
            $bits .= $value >= $average ? '1' : '0';
        }

        $hex = '';
        for ($i = 0; $i < 64; $i += 4) {
            $hex .= dechex(bindec(substr($bits, $i, 4)));
        }

        return $hex;
    }

    public function hammingDistance(string $hashA, string $hashB): int
    {
        if (strlen($hashA) !== strlen($hashB)) {
            return PHP_INT_MAX;
        }

        $distance = 0;
        for ($i = 0, $len = strlen($hashA); $i < $len; $i++) {
            $distance += substr_count(decbin(hexdec($hashA[$i]) ^ hexdec($hashB[$i])), '1');
        }

        return $distance;
    }

    /**
     * 却下済み以外の既存写真の中から、完全一致または見た目が非常に近い写真を探す。
     */
    public function findDuplicate(string $absolutePath): ?GalleryPhoto
    {
        $fileHash = $this->fileHash($absolutePath);
        $phash    = $this->perceptualHash($absolutePath);

        if ($fileHash === null && $phash === null) {
            return null;
        }

        $candidates = GalleryPhoto::where('status', '!=', 'rejected')
            ->where(function ($query) {
                $query->whereNotNull('file_hash')->orWhereNotNull('phash');
            })
            ->get(['id', 'file_hash', 'phash']);

        foreach ($candidates as $candidate) {
            if ($fileHash !== null && $candidate->file_hash === $fileHash) {
                return GalleryPhoto::find($candidate->id);
            }

            if ($phash !== null && $candidate->phash !== null
                && $this->hammingDistance($phash, $candidate->phash) <= self::NEAR_DUPLICATE_THRESHOLD) {
                return GalleryPhoto::find($candidate->id);
            }
        }

        return null;
    }
}
