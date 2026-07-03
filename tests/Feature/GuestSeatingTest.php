<?php

use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\SeatingTable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

// ─── アクセス制御 ──────────────────────────────────────────

describe('GuestSeatingController アクセス制御', function () {

    it('未認証 → /login にリダイレクト', function () {
        $this->get('/seating')->assertRedirect('/login');
    });

    it('admin → /admin/seating にリダイレクト', function () {
        $this->actingAs(makeAdmin())
            ->get('/seating')
            ->assertRedirect(route('admin.seating'));
    });

    it('プロフィールなし（未招待）→ /home にリダイレクト', function () {
        $this->actingAs(makeGuest())
            ->get('/seating')
            ->assertRedirect(route('dashboard'));
    });

    it('pending ゲスト → /home にリダイレクト', function () {
        $this->actingAs(makeGuest('pending'))
            ->get('/seating')
            ->assertRedirect(route('dashboard'));
    });

    it('欠席ゲスト → /home にリダイレクト', function () {
        $this->actingAs(makeGuest('declining'))
            ->get('/seating')
            ->assertRedirect(route('dashboard'));
    });

    it('出席ゲスト → 200', function () {
        $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->assertStatus(200);
    });
});

// ─── isPublished フラグ ───────────────────────────────────

describe('GuestSeatingController isPublished', function () {

    it('テーブルがない場合は未公開（false）', function () {
        $data = $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['isPublished'])->toBeFalse();
    });

    it('テーブルはあるが席がない場合は未公開（false）', function () {
        SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);

        $data = $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['isPublished'])->toBeFalse();
    });

    it('テーブルと席が存在する場合は公開済み（true）', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $data = $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['isPublished'])->toBeTrue();
    });

    it('少数のテーブルしか無くても32卓分の空マスは表示されない（固定グリッド廃止の確認）', function () {
        $tableA = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $tableB = SeatingTable::create(['name' => 'B卓', 'display_order' => 2, 'pos_x' => 0, 'pos_y' => 0]);
        Seat::create(['seating_table_id' => $tableA->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        Seat::create(['seating_table_id' => $tableB->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->assertSee('A卓')
            ->assertSee('B卓')
            ->assertDontSee('gs-table--empty', false)
            ->assertDontSee('gs-table__ghost', false);
    });

    it('未公開時はビューに準備中メッセージが含まれる', function () {
        $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->assertSee('席次表は準備中です');
    });

    it('公開済みなら準備中メッセージが含まれない', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->assertDontSee('席次表は準備中です');
    });
});

// ─── 自席データ ───────────────────────────────────────────

describe('GuestSeatingController 自席データ', function () {

    it('未配置ゲストの mySeat は null', function () {
        $data = $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['mySeat'])->toBeNull();
        expect($data['myTableId'])->toBeNull();
    });

    it('配置済みゲストの mySeat は Seat モデル', function () {
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest->id]);

        $data = $this->actingAs($guest)
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['mySeat'])->toBeInstanceOf(Seat::class);
        expect($data['mySeat']->id)->toBe($seat->id);
    });

    it('myTableId は mySeat の seating_table_id と一致する', function () {
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest->id]);

        $data = $this->actingAs($guest)
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['myTableId'])->toBe($table->id);
    });

    it('別ゲストの席は自席として認識されない', function () {
        $me    = makeGuest('attending');
        $other = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $other->id]);

        $data = $this->actingAs($me)
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['mySeat'])->toBeNull();
    });
});

// ─── View データ型保証 ────────────────────────────────────

describe('GuestSeatingController View データ型', function () {

    it('tables は EloquentCollection', function () {
        $data = $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['tables'])->toBeInstanceOf(EloquentCollection::class);
    });

    it('typeConfig は label / color / bg キーを持つ配列', function () {
        $data = $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['typeConfig'])->toBeArray()->not->toBeEmpty();
        foreach ($data['typeConfig'] as $cfg) {
            expect($cfg)->toHaveKey('label');
            expect($cfg)->toHaveKey('color');
            expect($cfg)->toHaveKey('bg');
        }
    });

    it('isPublished はブール値', function () {
        $data = $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->getOriginalContent()
            ->getData();

        expect($data['isPublished'])->toBeBool();
    });
});
