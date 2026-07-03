<?php

use App\Models\ProfileBookPage;
use App\Models\WeddingSetting;
use App\Services\PdfToImagesConverter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeWeddingSetting(array $overrides = []): WeddingSetting
{
    return WeddingSetting::create(array_merge([
        'groom_name'     => '新郎',
        'bride_name'     => '新婦',
        'ceremony_date'  => '2026-07-19',
        'ceremony_time'  => '12:00:00',
        'reception_time' => '13:00:00',
        'venue_name'     => 'テスト会場',
        'venue_address'  => '東京都渋谷区1-1-1',
    ], $overrides));
}

describe('GET /admin/profile-book 管理画面表示', function () {

    it('管理者は表示できる', function () {
        $this->actingAs(makeAdmin())
            ->get('/admin/profile-book')
            ->assertOk();
    });

    it('ゲストは403', function () {
        $this->actingAs(makeGuest())
            ->get('/admin/profile-book')
            ->assertStatus(403);
    });
});

describe('POST /admin/profile-book PDFアップロード', function () {

    it('PDFをアップロードすると各ページが画像として保存される', function () {
        Storage::fake('public');

        $tmpImg1 = tempnam(sys_get_temp_dir(), 'pbtest').'.jpg';
        $tmpImg2 = tempnam(sys_get_temp_dir(), 'pbtest').'.jpg';
        file_put_contents($tmpImg1, 'fake-image-1');
        file_put_contents($tmpImg2, 'fake-image-2');

        $this->mock(PdfToImagesConverter::class, function ($mock) use ($tmpImg1, $tmpImg2) {
            $mock->shouldReceive('convert')->once()->andReturn([$tmpImg1, $tmpImg2]);
        });

        $pdf = UploadedFile::fake()->create('profile.pdf', 100, 'application/pdf');

        $this->actingAs(makeAdmin())
            ->post('/admin/profile-book', ['pdf' => $pdf])
            ->assertRedirect();

        expect(ProfileBookPage::count())->toBe(2);
        expect(ProfileBookPage::where('page_number', 1)->exists())->toBeTrue();
        expect(ProfileBookPage::where('page_number', 2)->exists())->toBeTrue();
    });

    it('再アップロードすると古いページは削除され新しいページに置き換わる', function () {
        Storage::fake('public');

        $tmpImg = tempnam(sys_get_temp_dir(), 'pbtest').'.jpg';
        file_put_contents($tmpImg, 'fake-image');

        Storage::disk('public')->put('profile-book/old.jpg', 'old-content');
        ProfileBookPage::create(['page_number' => 1, 'image_path' => 'profile-book/old.jpg']);
        ProfileBookPage::create(['page_number' => 2, 'image_path' => 'profile-book/old2.jpg']);

        $this->mock(PdfToImagesConverter::class, function ($mock) use ($tmpImg) {
            $mock->shouldReceive('convert')->once()->andReturn([$tmpImg]);
        });

        $pdf = UploadedFile::fake()->create('profile.pdf', 100, 'application/pdf');

        $this->actingAs(makeAdmin())
            ->post('/admin/profile-book', ['pdf' => $pdf]);

        expect(ProfileBookPage::count())->toBe(1);
        Storage::disk('public')->assertMissing('profile-book/old.jpg');
    });

    it('PDF以外のファイルはバリデーションエラー', function () {
        $file = UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg');

        $this->actingAs(makeAdmin())
            ->post('/admin/profile-book', ['pdf' => $file])
            ->assertSessionHasErrors('pdf');
    });

    it('ゲストは403', function () {
        $pdf = UploadedFile::fake()->create('profile.pdf', 100, 'application/pdf');

        $this->actingAs(makeGuest())
            ->post('/admin/profile-book', ['pdf' => $pdf])
            ->assertStatus(403);
    });

    it('変換に失敗した場合、既存のページは維持される', function () {
        Storage::fake('public');
        Storage::disk('public')->put('profile-book/keep.jpg', 'keep-content');
        ProfileBookPage::create(['page_number' => 1, 'image_path' => 'profile-book/keep.jpg']);

        $this->mock(PdfToImagesConverter::class, function ($mock) {
            $mock->shouldReceive('convert')->once()->andThrow(new RuntimeException('conversion failed'));
        });

        $pdf = UploadedFile::fake()->create('profile.pdf', 100, 'application/pdf');

        $this->actingAs(makeAdmin())
            ->post('/admin/profile-book', ['pdf' => $pdf])
            ->assertSessionHas('error');

        expect(ProfileBookPage::count())->toBe(1);
        Storage::disk('public')->assertExists('profile-book/keep.jpg');
    });
});

describe('DELETE /admin/profile-book 削除', function () {

    it('全ページを削除できる', function () {
        Storage::fake('public');
        Storage::disk('public')->put('profile-book/a.jpg', 'a');
        ProfileBookPage::create(['page_number' => 1, 'image_path' => 'profile-book/a.jpg']);

        $this->actingAs(makeAdmin())
            ->delete('/admin/profile-book')
            ->assertRedirect();

        expect(ProfileBookPage::count())->toBe(0);
        Storage::disk('public')->assertMissing('profile-book/a.jpg');
    });
});

describe('GET /profile-book ゲスト向け表示', function () {

    it('ページが無い場合は準備中メッセージ', function () {
        $this->actingAs(makeGuest())
            ->get('/profile-book')
            ->assertSee('準備中');
    });

    it('ページがある場合はビューアが表示される', function () {
        Storage::fake('public');
        Storage::disk('public')->put('profile-book/a.jpg', 'a');
        ProfileBookPage::create(['page_number' => 1, 'image_path' => 'profile-book/a.jpg']);

        $this->actingAs(makeGuest())
            ->get('/profile-book')
            ->assertOk()
            ->assertSee('pbBook', false);
    });

    it('is_profile_book_public が false の場合はホームへリダイレクト', function () {
        makeWeddingSetting(['is_profile_book_public' => false]);
        ProfileBookPage::create(['page_number' => 1, 'image_path' => 'profile-book/a.jpg']);

        $this->actingAs(makeGuest())
            ->get('/profile-book')
            ->assertRedirect(route('dashboard'));
    });

    it('is_profile_book_public が false でも管理者はプレビューできる', function () {
        Storage::fake('public');
        Storage::disk('public')->put('profile-book/a.jpg', 'a');
        makeWeddingSetting(['is_profile_book_public' => false]);
        ProfileBookPage::create(['page_number' => 1, 'image_path' => 'profile-book/a.jpg']);

        $this->actingAs(makeAdmin())
            ->get('/profile-book')
            ->assertOk();
    });

    it('is_profile_book_public が true の場合は表示される', function () {
        Storage::fake('public');
        Storage::disk('public')->put('profile-book/a.jpg', 'a');
        makeWeddingSetting(['is_profile_book_public' => true]);
        ProfileBookPage::create(['page_number' => 1, 'image_path' => 'profile-book/a.jpg']);

        $this->actingAs(makeGuest())
            ->get('/profile-book')
            ->assertOk();
    });

    it('公開設定がオフのときヘッダーメニューにリンクが表示されない', function () {
        makeWeddingSetting(['is_profile_book_public' => false]);

        $this->actingAs(makeGuest('attending'))
            ->get('/gallery')
            ->assertDontSee('プロフィールブック');
    });

    it('公開設定がオンのときヘッダーメニューにリンクが表示される', function () {
        makeWeddingSetting(['is_profile_book_public' => true]);

        $this->actingAs(makeGuest('attending'))
            ->get('/gallery')
            ->assertSee('プロフィールブック');
    });
});
