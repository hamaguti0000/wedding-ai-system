<?php

use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\SeatingTable;

// ─── テーブル作成 ─────────────────────────────────────────

describe('POST /admin/seating/tables テーブル作成', function () {

    it('テーブルを作成できる', function () {
        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/tables', ['name' => 'A卓'])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        expect(SeatingTable::where('name', 'A卓')->exists())->toBeTrue();
    });

    it('レスポンスに table オブジェクトが含まれる', function () {
        $response = $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/tables', ['name' => 'B卓'])
            ->assertStatus(200);

        $response->assertJsonStructure(['success', 'table' => ['id', 'name']]);
    });

    it('seat_count を指定すると席が自動作成される', function () {
        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/tables', ['name' => 'C卓', 'seat_count' => 4]);

        $table = SeatingTable::where('name', 'C卓')->first();
        expect($table->seats()->count())->toBe(4);
    });

    it('seat_count 省略時は席が作成されない', function () {
        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/tables', ['name' => 'D卓']);

        expect(SeatingTable::where('name', 'D卓')->first()->seats()->count())->toBe(0);
    });

    it('name 未入力はバリデーションエラー（422）', function () {
        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/tables', ['name' => ''])
            ->assertStatus(422);
    });

    it('ゲストは 403', function () {
        $this->actingAs(makeGuest())
            ->postJson('/admin/seating/tables', ['name' => 'A卓'])
            ->assertStatus(403);
    });
});

// ─── テーブル削除 ─────────────────────────────────────────

describe('DELETE /admin/seating/tables/{id} テーブル削除', function () {

    it('テーブルを削除できる', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->deleteJson("/admin/seating/tables/{$table->id}")
            ->assertJson(['success' => true]);

        expect(SeatingTable::find($table->id))->toBeNull();
    });

    it('テーブル削除時に紐づく席も削除される', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->deleteJson("/admin/seating/tables/{$table->id}");

        expect(Seat::find($seat->id))->toBeNull();
    });

    it('存在しないテーブルは 404', function () {
        $this->actingAs(makeAdmin())
            ->deleteJson('/admin/seating/tables/99999')
            ->assertStatus(404);
    });
});

// ─── テーブル位置更新 ─────────────────────────────────────

describe('PATCH /admin/seating/tables/{id}/position 位置更新', function () {

    it('座標を更新できる', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->patchJson("/admin/seating/tables/{$table->id}/position", ['x' => 150, 'y' => 200])
            ->assertJson(['success' => true]);

        $table->refresh();
        expect($table->pos_x)->toBe(150);
        expect($table->pos_y)->toBe(200);
    });

    it('x・y 未入力はバリデーションエラー', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->patchJson("/admin/seating/tables/{$table->id}/position", [])
            ->assertStatus(422);
    });
});

// ─── 席追加 ──────────────────────────────────────────────

describe('POST /admin/seating/tables/{id}/seats 席追加', function () {

    it('席を追加できる', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->postJson("/admin/seating/tables/{$table->id}/seats", ['type' => 'vip'])
            ->assertJson(['success' => true]);

        expect($table->seats()->where('type', 'vip')->exists())->toBeTrue();
    });

    it('type 未指定なら normal が設定される', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->postJson("/admin/seating/tables/{$table->id}/seats", []);

        expect($table->seats()->first()->type)->toBe('normal');
    });

    it('座標を明示指定できる', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->postJson("/admin/seating/tables/{$table->id}/seats", ['pos_x' => 80, 'pos_y' => 40]);

        $seat = $table->seats()->first();
        expect($seat->pos_x)->toBe(80);
        expect($seat->pos_y)->toBe(40);
    });
});

// ─── 席更新 ──────────────────────────────────────────────

describe('PATCH /admin/seating/seats/{id} 席更新', function () {

    it('席タイプを更新できる', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->patchJson("/admin/seating/seats/{$seat->id}", ['type' => 'vip'])
            ->assertJson(['success' => true]);

        expect($seat->refresh()->type)->toBe('vip');
    });

    it('席の座標を更新できる', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->patchJson("/admin/seating/seats/{$seat->id}", ['pos_x' => 80, 'pos_y' => 90]);

        expect($seat->refresh()->pos_x)->toBe(80);
        expect($seat->refresh()->pos_y)->toBe(90);
    });

    it('存在しない席は 404', function () {
        $this->actingAs(makeAdmin())
            ->patchJson('/admin/seating/seats/99999', ['type' => 'vip'])
            ->assertStatus(404);
    });
});

// ─── 席削除 ──────────────────────────────────────────────

describe('DELETE /admin/seating/seats/{id} 席削除', function () {

    it('席を削除できる', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->deleteJson("/admin/seating/seats/{$seat->id}")
            ->assertJson(['success' => true]);

        expect(Seat::find($seat->id))->toBeNull();
    });

    it('配置済みゲストの freed_user_id がレスポンスに含まれる', function () {
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest->id]);

        $this->actingAs(makeAdmin())
            ->deleteJson("/admin/seating/seats/{$seat->id}")
            ->assertJson(['freed_user_id' => $guest->id]);
    });

    it('席削除で配置情報も削除される', function () {
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest->id]);

        $this->actingAs(makeAdmin())
            ->deleteJson("/admin/seating/seats/{$seat->id}");

        expect(SeatAssignment::where('user_id', $guest->id)->exists())->toBeFalse();
    });

    it('空席の場合 freed_user_id は null', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->deleteJson("/admin/seating/seats/{$seat->id}")
            ->assertJson(['freed_user_id' => null]);
    });
});

// ─── 配置 ────────────────────────────────────────────────

describe('POST /admin/seating/assign 配置', function () {

    it('ゲストを席に配置できる', function () {
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/assign', ['seat_id' => $seat->id, 'user_id' => $guest->id])
            ->assertJson(['success' => true]);

        expect(SeatAssignment::where('user_id', $guest->id)->where('seat_id', $seat->id)->exists())
            ->toBeTrue();
    });

    it('既存配置を解除して別の席に移動できる（1ゲスト=1席 UNIQUE 保証）', function () {
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat1 = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0,  'pos_y' => 0]);
        $seat2 = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 60, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat1->id, 'user_id' => $guest->id]);

        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/assign', ['seat_id' => $seat2->id, 'user_id' => $guest->id]);

        expect(SeatAssignment::where('user_id', $guest->id)->value('seat_id'))->toBe($seat2->id);
        expect(SeatAssignment::where('seat_id', $seat1->id)->exists())->toBeFalse();
    });

    it('別ゲストが占有中の席への配置は 422', function () {
        $guest1 = makeGuest('attending');
        $guest2 = makeGuest('attending');
        $table  = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat   = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest1->id]);

        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/assign', ['seat_id' => $seat->id, 'user_id' => $guest2->id])
            ->assertStatus(422);

        // guest2 は配置されていないこと
        expect(SeatAssignment::where('user_id', $guest2->id)->exists())->toBeFalse();
    });

    it('seat_id・user_id 未入力はバリデーションエラー', function () {
        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/assign', [])
            ->assertStatus(422);
    });

    it('存在しない seat_id はバリデーションエラー', function () {
        $guest = makeGuest('attending');

        $this->actingAs(makeAdmin())
            ->postJson('/admin/seating/assign', ['seat_id' => 99999, 'user_id' => $guest->id])
            ->assertStatus(422);
    });
});

// ─── 配置解除 ─────────────────────────────────────────────

describe('DELETE /admin/seating/unassign/{userId} 配置解除', function () {

    it('配置を解除できる', function () {
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest->id]);

        $this->actingAs(makeAdmin())
            ->deleteJson("/admin/seating/unassign/{$guest->id}")
            ->assertJson(['success' => true]);

        expect(SeatAssignment::where('user_id', $guest->id)->exists())->toBeFalse();
    });

    it('配置のないユーザーでも成功する（冪等性）', function () {
        $guest = makeGuest('attending');

        $this->actingAs(makeAdmin())
            ->deleteJson("/admin/seating/unassign/{$guest->id}")
            ->assertJson(['success' => true]);
    });
});
