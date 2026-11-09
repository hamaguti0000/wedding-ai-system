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

// ─── 卓の左右振り分け ──────────────────────────────────────

describe('ゲスト席次表の左右振り分け', function () {

    /**
     * 空席を先に除外してから半分に割ると、空席の入り方によって人が本来と逆側に
     * 表示されてしまう（2026-08-12、管理画面では右側の人がゲスト画面では左側に
     * 出ていたことで発覚）。管理画面・印刷版と同じく「空席込みの全席」を基準に
     * 左右を決めることを固定する。
     */
    it('空席が偏っていても管理画面と同じ左右に振り分けられる', function () {
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => '親族卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);

        // 8席中、左半分(0-3)は後ろ2席だけ、右半分(4-7)は後ろ3席だけ埋める。
        // 旧ロジックだと埋席5件をceil(5/2)=3で割り、右側の1人目が左に混ざっていた。
        $seats = [];
        for ($i = 0; $i < 8; $i++) {
            $seats[] = Seat::create([
                'seating_table_id' => $table->id,
                'type' => 'normal',
                'pos_x' => $i,
                'pos_y' => 0,
            ]);
        }
        $leftUsers  = [makeGuest('attending'), makeGuest('attending')];
        $rightUsers = [makeGuest('attending'), makeGuest('attending'), makeGuest('attending')];
        SeatAssignment::create(['seat_id' => $seats[2]->id, 'user_id' => $leftUsers[0]->id]);
        SeatAssignment::create(['seat_id' => $seats[3]->id, 'user_id' => $leftUsers[1]->id]);
        SeatAssignment::create(['seat_id' => $seats[5]->id, 'user_id' => $rightUsers[0]->id]);
        SeatAssignment::create(['seat_id' => $seats[6]->id, 'user_id' => $rightUsers[1]->id]);
        SeatAssignment::create(['seat_id' => $seats[7]->id, 'user_id' => $rightUsers[2]->id]);

        $rendered = $this->actingAs($guest)->get('/seating')->getContent();

        // 左ブロックと右ブロックを取り出し、それぞれの人数を確認する
        preg_match(
            '/gs-table__guests--left(.*?)gs-table__wreath(.*?)gs-table__guests--right(.*?)<\/article>/s',
            $rendered,
            $m
        );
        expect($m)->not->toBeEmpty();

        $leftBlock  = $m[1];
        $rightBlock = $m[3];

        expect(substr_count($leftBlock, 'gs-guest__name'))->toBe(2);
        expect(substr_count($rightBlock, 'gs-guest__name'))->toBe(3);
    });
});
