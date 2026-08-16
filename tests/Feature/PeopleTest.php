<?php

use App\Models\GalleryPhoto;
use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\SeatingTable;
use App\Models\User;

/** 席次表に登録済み（座席割り当て済み）のゲストを作成する */
function makeSeatedGuest(?string $participation = 'attending'): User
{
    $guest = makeGuest($participation);
    $table = SeatingTable::create(['name' => 'テスト卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
    $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
    SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest->id]);

    return $guest;
}

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
        $target = makeSeatedGuest();

        $this->actingAs($viewer)
            ->get(route('people.show', $target))
            ->assertStatus(200);
    });
});

describe('PeopleController 席次表登録者への絞り込み', function () {

    it('一覧には座席が割り当てられているゲストのみ表示される', function () {
        $seated   = makeSeatedGuest();
        $unseated = makeGuest('attending');

        $data = $this->actingAs(makeGuest('attending'))
            ->get('/people')
            ->getOriginalContent()
            ->getData();

        expect($data['people']->pluck('id'))->toContain($seated->id);
        expect($data['people']->pluck('id'))->not->toContain($unseated->id);
    });

    it('座席未割り当てのゲストのアルバムは404になる', function () {
        $unseated = makeGuest('attending');

        $this->actingAs(makeGuest('attending'))
            ->get(route('people.show', $unseated))
            ->assertNotFound();
    });
});

describe('PeopleController 人物アルバムの絞り込み', function () {

    it('タグ付けされた承認済み写真だけが表示される', function () {
        $target = makeSeatedGuest();

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
        $target = makeSeatedGuest();

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
