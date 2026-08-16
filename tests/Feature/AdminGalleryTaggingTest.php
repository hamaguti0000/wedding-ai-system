<?php

use App\Models\GalleryPhoto;

describe('AdminGalleryController::tag アクセス制御', function () {

    it('未認証 → /login にリダイレクト', function () {
        $photo = GalleryPhoto::create([
            'file_path'  => 'gallery/a.jpg',
            'is_active'  => true,
            'status'     => 'approved',
            'sort_order' => 1,
        ]);

        $this->post(route('admin.gallery.tag', $photo))->assertRedirect('/login');
    });

    it('ゲストは/homeへリダイレクトされ、タグ付けは行われない', function () {
        $photo = GalleryPhoto::create([
            'file_path'  => 'gallery/a.jpg',
            'is_active'  => true,
            'status'     => 'approved',
            'sort_order' => 1,
        ]);
        $guest = makeGuest('attending');

        $this->actingAs($guest)
            ->post(route('admin.gallery.tag', $photo), ['user_ids' => [$guest->id]])
            ->assertRedirect(route('dashboard'));

        expect($photo->fresh()->taggedUsers)->toBeEmpty();
    });
});

describe('AdminGalleryController::tag タグ付け', function () {

    it('管理者が user_ids を送ると写真にタグ付けされる', function () {
        $admin  = makeAdmin();
        $guest  = makeGuest('attending');
        $photo  = GalleryPhoto::create([
            'file_path'  => 'gallery/a.jpg',
            'is_active'  => true,
            'status'     => 'approved',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.gallery.tag', $photo), ['user_ids' => [$guest->id]])
            ->assertRedirect();

        expect($photo->fresh()->taggedUsers->pluck('id')->all())->toBe([$guest->id]);
    });

    it('user_ids を空で送るとタグが解除される(sync)', function () {
        $admin = makeAdmin();
        $guest = makeGuest('attending');
        $photo = GalleryPhoto::create([
            'file_path'  => 'gallery/a.jpg',
            'is_active'  => true,
            'status'     => 'approved',
            'sort_order' => 1,
        ]);
        $photo->taggedUsers()->attach($guest->id);

        $this->actingAs($admin)
            ->post(route('admin.gallery.tag', $photo), [])
            ->assertRedirect();

        expect($photo->fresh()->taggedUsers)->toBeEmpty();
    });
});
