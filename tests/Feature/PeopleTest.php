<?php

use App\Models\GalleryPhoto;

describe('PeopleController アクセス制御', function () {

    it('未認証 → /login にリダイレクト', function () {
        $this->get('/people')->assertRedirect('/login');
    });

    it('ログイン済みゲスト → /people は200', function () {
        $this->actingAs(makeGuest('attending'))
            ->get('/people')
            ->assertStatus(200);
    });

    it('ログイン済みゲストは他ゲストの写真アルバムも閲覧できる', function () {
        $viewer = makeGuest('attending');
        $target = makeGuest('attending');

        $this->actingAs($viewer)
            ->get(route('people.show', $target))
            ->assertStatus(200);
    });
});

describe('PeopleController 人物アルバムの絞り込み', function () {

    it('タグ付けされた承認済み写真だけが表示される', function () {
        $target = makeGuest('attending');

        $tagged = GalleryPhoto::create([
            'file_path'  => 'gallery/tagged.jpg',
            'is_active'  => true,
            'status'     => 'approved',
            'sort_order' => 1,
        ]);
        $tagged->taggedUsers()->attach($target->id);

        $untagged = GalleryPhoto::create([
            'file_path'  => 'gallery/untagged.jpg',
            'is_active'  => true,
            'status'     => 'approved',
            'sort_order' => 2,
        ]);

        $data = $this->actingAs(makeGuest('attending'))
            ->get(route('people.show', $target))
            ->getOriginalContent()
            ->getData();

        expect($data['photos']->pluck('id'))->toContain($tagged->id);
        expect($data['photos']->pluck('id'))->not->toContain($untagged->id);
    });

    it('非公開(is_active=false)や未承認の写真はタグ付けされていても表示されない', function () {
        $target = makeGuest('attending');

        $inactive = GalleryPhoto::create([
            'file_path'  => 'gallery/inactive.jpg',
            'is_active'  => false,
            'status'     => 'approved',
            'sort_order' => 1,
        ]);
        $inactive->taggedUsers()->attach($target->id);

        $pending = GalleryPhoto::create([
            'file_path'       => 'gallery/pending.jpg',
            'is_active'       => false,
            'status'          => 'pending',
            'is_guest_upload' => true,
            'sort_order'      => 2,
        ]);
        $pending->taggedUsers()->attach($target->id);

        $data = $this->actingAs(makeGuest('attending'))
            ->get(route('people.show', $target))
            ->getOriginalContent()
            ->getData();

        expect($data['photos'])->toBeEmpty();
    });
});
