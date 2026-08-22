<?php

use App\Services\ImageDuplicateDetector;

/** 座標に応じて色が変わるグラデーション画像を生成する（seedを変えると別の画像になる） */
function makeHashTestImage(int $seed, int $size = 64): string
{
    if (! function_exists('imagecreatetruecolor')) {
        test()->markTestSkipped('GD extension is required for generated hash images.');
    }

    $path = tempnam(sys_get_temp_dir(), 'idd_test_') . '.jpg';
    $img  = imagecreatetruecolor($size, $size);

    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $v = ($x * 4 + $y * 4 + $seed) % 256;
            $color = imagecolorallocate($img, $v, 255 - $v, ($v + $seed) % 256);
            imagesetpixel($img, $x, $y, $color);
        }
    }

    imagejpeg($img, $path, 92);

    return $path;
}

describe('ImageDuplicateDetector::fileHash', function () {
    it('同じ内容のファイルは同じハッシュになる', function () {
        $detector = new ImageDuplicateDetector();
        $a = makeHashTestImage(1);
        $b = makeHashTestImage(1);

        expect($detector->fileHash($a))->toBe($detector->fileHash($b));

        @unlink($a); @unlink($b);
    });

    it('内容が違うファイルは異なるハッシュになる', function () {
        $detector = new ImageDuplicateDetector();
        $a = makeHashTestImage(1);
        $b = makeHashTestImage(200);

        expect($detector->fileHash($a))->not->toBe($detector->fileHash($b));

        @unlink($a); @unlink($b);
    });

    it('存在しないファイルには null を返す', function () {
        expect((new ImageDuplicateDetector())->fileHash('/tmp/does-not-exist-xyz.jpg'))->toBeNull();
    });
});

describe('ImageDuplicateDetector::perceptualHash / hammingDistance', function () {
    it('同じ見た目の画像はハミング距離0になる', function () {
        $detector = new ImageDuplicateDetector();
        $a = makeHashTestImage(1);
        $b = makeHashTestImage(1);

        $hashA = $detector->perceptualHash($a);
        $hashB = $detector->perceptualHash($b);

        expect($hashA)->not->toBeNull();
        expect($detector->hammingDistance($hashA, $hashB))->toBe(0);

        @unlink($a); @unlink($b);
    });

    it('見た目が違う画像はハミング距離が0より大きい', function () {
        $detector = new ImageDuplicateDetector();
        $a = makeHashTestImage(1);
        $b = makeHashTestImage(200);

        $hashA = $detector->perceptualHash($a);
        $hashB = $detector->perceptualHash($b);

        expect($detector->hammingDistance($hashA, $hashB))->toBeGreaterThan(0);

        @unlink($a); @unlink($b);
    });

    it('存在しないファイルには null を返す', function () {
        expect((new ImageDuplicateDetector())->perceptualHash('/tmp/does-not-exist-xyz.jpg'))->toBeNull();
    });
});
