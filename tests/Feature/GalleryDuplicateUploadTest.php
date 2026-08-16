<?php

use App\Models\GalleryPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** 座標に応じて色が変わるグラデーション画像を生成する（seedを変えると別の画像になる） */
function makeUploadableTestImage(int $seed = 0, string $name = 'photo.jpg', int $size = 64): UploadedFile
{
    $img = imagecreatetruecolor($size, $size);

    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $v = ($x * 4 + $y * 4 + $seed) % 256;
            $color = imagecolorallocate($img, $v, 255 - $v, ($v + $seed) % 256);
            imagesetpixel($img, $x, $y, $color);
        }
    }

    ob_start();
    imagejpeg($img, null, 92);
    $content = ob_get_clean();

    return UploadedFile::fake()->createWithContent($name, $content);
}

describe('ゲスト写真アップロードの重複検出', function () {
    it('同じ写真を2回アップロードすると2回目は登録されない', function () {
        Storage::fake('public');
        $guest = makeGuest('attending');

        $this->actingAs($guest)
            ->post('/gallery/upload', ['photos' => [makeUploadableTestImage(1)]])
            ->assertRedirect();

        expect(GalleryPhoto::count())->toBe(1);

        $this->actingAs($guest)
            ->post('/gallery/upload', ['photos' => [makeUploadableTestImage(1)]])
            ->assertRedirect()
            ->assertSessionHas('success');

        expect(GalleryPhoto::count())->toBe(1);
    });

    it('違う写真は両方とも登録される', function () {
        Storage::fake('public');
        $guest = makeGuest('attending');

        $this->actingAs($guest)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(1)]]);
        $this->actingAs($guest)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(200)]]);

        expect(GalleryPhoto::count())->toBe(2);
    });

    it('別のゲストが投稿しても同じ写真なら重複として除外される', function () {
        Storage::fake('public');
        $guestA = makeGuest('attending');
        $guestB = makeGuest('attending');

        $this->actingAs($guestA)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(42)]]);
        $this->actingAs($guestB)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(42)]]);

        expect(GalleryPhoto::count())->toBe(1);
    });

    it('アップロードされた写真には file_hash と phash が保存される', function () {
        Storage::fake('public');
        $guest = makeGuest('attending');

        $this->actingAs($guest)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(5)]]);

        $photo = GalleryPhoto::first();
        expect($photo->file_hash)->not->toBeNull();
        expect($photo->phash)->not->toBeNull();
    });
});

describe('管理者の公式写真アップロードの重複検出', function () {
    it('同じ写真を2回アップロードすると2回目は登録されない', function () {
        Storage::fake('public');
        $admin = makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/gallery', ['photos' => [makeUploadableTestImage(9)]])
            ->assertRedirect();

        expect(GalleryPhoto::count())->toBe(1);

        $this->actingAs($admin)
            ->post('/admin/gallery', ['photos' => [makeUploadableTestImage(9)]])
            ->assertRedirect();

        expect(GalleryPhoto::count())->toBe(1);
    });
});
