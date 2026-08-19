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


describe('AdminGalleryController::ordering 表示順', function () {

    it('承認したゲスト投稿は公開ギャラリーの先頭に入る', function () {
        $admin = makeAdmin();
        $oldFirst = GalleryPhoto::create([
            'file_path' => 'gallery/old-first.jpg',
            'is_active' => true,
            'status' => 'approved',
            'sort_order' => 1,
        ]);
        $oldSecond = GalleryPhoto::create([
            'file_path' => 'gallery/old-second.jpg',
            'is_active' => true,
            'status' => 'approved',
            'sort_order' => 2,
        ]);
        $pending = GalleryPhoto::create([
            'file_path' => 'gallery/guest/new.jpg',
            'is_active' => false,
            'status' => 'pending',
            'sort_order' => 99,
            'is_guest_upload' => true,
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.gallery.approve', $pending))
            ->assertOk()
            ->assertJson(['success' => true]);

        expect($pending->fresh()->sort_order)->toBe(1)
            ->and($pending->fresh()->is_active)->toBeTrue()
            ->and($pending->fresh()->status)->toBe('approved')
            ->and($oldFirst->fresh()->sort_order)->toBe(2)
            ->and($oldSecond->fresh()->sort_order)->toBe(3);
    });

    it('管理者が指定した順番を保存し、ゲスト側もその順で表示する', function () {
        $admin = makeAdmin();
        $guest = makeGuest('attending');
        $first = GalleryPhoto::create([
            'file_path' => 'gallery/first.jpg',
            'is_active' => true,
            'status' => 'approved',
            'sort_order' => 1,
        ]);
        $second = GalleryPhoto::create([
            'file_path' => 'gallery/second.jpg',
            'is_active' => true,
            'status' => 'approved',
            'sort_order' => 2,
        ]);
        $third = GalleryPhoto::create([
            'file_path' => 'gallery/third.jpg',
            'is_active' => true,
            'status' => 'approved',
            'sort_order' => 3,
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.gallery.reorder'), [
                'order' => [$third->id, $first->id, $second->id],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        expect($third->fresh()->sort_order)->toBe(1)
            ->and($first->fresh()->sort_order)->toBe(2)
            ->and($second->fresh()->sort_order)->toBe(3);

        $response = $this->actingAs($guest)->get(route('gallery'));
        $response->assertOk();
        expect($response->viewData('photos')->pluck('id')->all())->toBe([
            $third->id,
            $first->id,
            $second->id,
        ]);
    });
});

describe('AdminGalleryController::tag グループ反映', function () {

    it('グループにタグ付けすると所属ゲストのギャラリーで関連写真になる', function () {
        $admin = makeAdmin();
        $guest = makeGuest('attending');
        $group = \App\Models\GuestGroup::create([
            'id' => 'test-family',
            'name' => '家族グループ',
            'sort_order' => 1,
        ]);
        $group->members()->attach($guest->id);
        $photo = GalleryPhoto::create([
            'file_path' => 'gallery/group.jpg',
            'is_active' => true,
            'status' => 'approved',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.gallery.tag', $photo), ['group_ids' => [$group->id]])
            ->assertRedirect();

        expect($photo->fresh()->taggedGroups->pluck('id')->all())->toBe([$group->id]);

        $this->actingAs($guest)
            ->get(route('gallery'))
            ->assertOk()
            ->assertSee('data-related="1"', false)
            ->assertSee('家族グループ');
    });
});
