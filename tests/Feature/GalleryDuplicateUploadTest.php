<?php

use App\Models\GalleryPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/** 座標に応じて色が変わるグラデーション画像を生成する（seedを変えると別の画像になる） */
function makeUploadableTestImage(int $seed = 0, string $name = 'photo.jpg', int $size = 64): UploadedFile
{
    if (! function_exists('imagecreatetruecolor')) {
        test()->markTestSkipped('GD extension is required for generated upload images.');
    }

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

describe('ゲスト写真アップロード', function () {
    it('同じ写真を2回アップロードしても両方登録される', function () {
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

        expect(GalleryPhoto::count())->toBe(2);
    });

    it('違う写真は両方とも登録される', function () {
        Storage::fake('public');
        $guest = makeGuest('attending');

        $this->actingAs($guest)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(1)]]);
        $this->actingAs($guest)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(200)]]);

        expect(GalleryPhoto::count())->toBe(2);
    });

    it('別のゲストが同じ写真を投稿しても両方登録される', function () {
        Storage::fake('public');
        $guestA = makeGuest('attending');
        $guestB = makeGuest('attending');

        $this->actingAs($guestA)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(42)]]);
        $this->actingAs($guestB)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(42)]]);

        expect(GalleryPhoto::count())->toBe(2);
    });

    it('アップロードされた写真は重複判定ハッシュなしでも登録される', function () {
        Storage::fake('public');
        $guest = makeGuest('attending');

        $this->actingAs($guest)->post('/gallery/upload', ['photos' => [makeUploadableTestImage(5)]]);

        $photo = GalleryPhoto::first();
        expect($photo->file_hash)->toBeNull();
        expect($photo->phash)->toBeNull();
    });
});

describe('管理者の公式写真アップロード', function () {
    it('同じ写真を2回アップロードしても両方登録される', function () {
        Storage::fake('public');
        $admin = makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/gallery', ['photos' => [makeUploadableTestImage(9)]])
            ->assertRedirect();

        expect(GalleryPhoto::count())->toBe(1);

        $this->actingAs($admin)
            ->post('/admin/gallery', ['photos' => [makeUploadableTestImage(9)]])
            ->assertRedirect();

        expect(GalleryPhoto::count())->toBe(2);
    });
});

describe('ギャラリー複数保存', function () {
    it('選択した公開写真をzipでダウンロードできる', function () {
        Storage::fake('public');
        $guest = makeGuest('attending');

        Storage::disk('public')->put('gallery/one.jpg', 'one');
        Storage::disk('public')->put('gallery/two.jpg', 'two');

        $first = GalleryPhoto::create([
            'file_path' => 'gallery/one.jpg',
            'sort_order' => 1,
            'is_active' => true,
            'status' => 'approved',
        ]);
        $second = GalleryPhoto::create([
            'file_path' => 'gallery/two.jpg',
            'sort_order' => 2,
            'is_active' => true,
            'status' => 'approved',
        ]);

        $this->actingAs($guest)
            ->post(route('gallery.download'), ['photo_tokens' => [Crypt::encryptString((string) $first->id), Crypt::encryptString((string) $second->id)]])
            ->assertOk()
            ->assertHeader('content-type', 'application/zip');
    });
});
