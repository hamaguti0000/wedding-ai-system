<?php

use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\SeatingTable;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

// ─── AdminSeatingController::index() ──────────────────────

describe('AdminSeatingController::index() のデータ構造', function () {

    it('View に渡す変数の型が保証されている', function () {
        $admin = makeAdmin();
        $view  = $this->actingAs($admin)->get('/admin/seating')
            ->assertStatus(200)
            ->getOriginalContent();

        $data = $view->getData();

        // EloquentCollection<SeatingTable>
        expect($data['tables'])->toBeInstanceOf(EloquentCollection::class);

        // Collection<User>
        expect($data['assignedGuests'])->toBeInstanceOf(Collection::class);
        expect($data['unassignedGuests'])->toBeInstanceOf(Collection::class);

        // array<string, int> — int のみ、Collection を含まない
        expect($data['summary'])->toBeArray();
        expect($data['summary']['total'])->toBeInt();
        expect($data['summary']['assigned'])->toBeInt();
        expect($data['summary']['unassigned'])->toBeInt();

        // array<string, array> — 席タイプ設定
        expect($data['typeConfig'])->toBeArray();
        foreach ($data['typeConfig'] as $key => $cfg) {
            expect($cfg)->toBeArray();
            expect($cfg)->toHaveKeys(['label', 'color', 'bg']);
        }
    });

    it('旧変数名（assigned, unassigned, attending）は View に露出しない', function () {
        $admin = makeAdmin();
        $data  = $this->actingAs($admin)->get('/admin/seating')
            ->getOriginalContent()->getData();

        expect(array_key_exists('attending',  $data))->toBeFalse('$attending はView不要');
        expect(array_key_exists('assigned',   $data))->toBeFalse('$assigned は $assignedGuests に改名');
        expect(array_key_exists('unassigned', $data))->toBeFalse('$unassigned は $unassignedGuests に改名');
    });

    it('summary の各 count は対応する Collection の count と一致する', function () {
        $admin = makeAdmin();

        makeGuest('attending');
        makeGuest('attending');
        makeGuest('declining'); // 集計に入らないことも確認

        $data = $this->actingAs($admin)->get('/admin/seating')
            ->getOriginalContent()->getData();

        expect($data['summary']['assigned'])
            ->toBe($data['assignedGuests']->count());

        expect($data['summary']['unassigned'])
            ->toBe($data['unassignedGuests']->count());

        expect($data['summary']['total'])
            ->toBe($data['assignedGuests']->count() + $data['unassignedGuests']->count());
    });

    it('summary の不変条件: assigned + unassigned = total', function () {
        $admin = makeAdmin();
        makeGuest('attending');
        makeGuest('attending');
        makeGuest('attending');

        $summary = $this->actingAs($admin)->get('/admin/seating')
            ->getOriginalContent()->getData()['summary'];

        expect($summary['assigned'] + $summary['unassigned'])->toBe($summary['total']);
    });

    it('欠席者（declining）は席次データに含まれない', function () {
        $admin   = makeAdmin();
        $attend  = makeGuest('attending');
        $decline = makeGuest('declining');

        $data = $this->actingAs($admin)->get('/admin/seating')
            ->getOriginalContent()->getData();

        $allIds = $data['assignedGuests']->pluck('id')
            ->merge($data['unassignedGuests']->pluck('id'));

        expect($allIds->contains($attend->id))->toBeTrue();
        expect($allIds->contains($decline->id))->toBeFalse();
    });

    it('配置済みゲストは assignedGuests に、未配置は unassignedGuests に分類される', function () {
        $admin  = makeAdmin();
        $guest1 = makeGuest('attending');
        $guest2 = makeGuest('attending');

        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest1->id]);

        $data = $this->actingAs($admin)->get('/admin/seating')
            ->getOriginalContent()->getData();

        expect($data['assignedGuests']->pluck('id')->contains($guest1->id))->toBeTrue();
        expect($data['unassignedGuests']->pluck('id')->contains($guest1->id))->toBeFalse();

        expect($data['unassignedGuests']->pluck('id')->contains($guest2->id))->toBeTrue();
        expect($data['assignedGuests']->pluck('id')->contains($guest2->id))->toBeFalse();
    });

    it('assignedGuests の各要素は User モデルである', function () {
        $admin = makeAdmin();
        $guest = makeGuest('attending');
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest->id]);

        $data = $this->actingAs($admin)->get('/admin/seating')
            ->getOriginalContent()->getData();

        $data['assignedGuests']->each(fn($u) => expect($u)->toBeInstanceOf(User::class));
        $data['unassignedGuests']->each(fn($u) => expect($u)->toBeInstanceOf(User::class));
    });

    it('tables は SeatingTable モデルのコレクションである', function () {
        $admin = makeAdmin();
        SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        SeatingTable::create(['name' => 'B卓', 'display_order' => 2, 'pos_x' => 0, 'pos_y' => 0]);

        $tables = $this->actingAs($admin)->get('/admin/seating')
            ->getOriginalContent()->getData()['tables'];

        expect($tables)->toBeInstanceOf(EloquentCollection::class);
        $tables->each(fn($t) => expect($t)->toBeInstanceOf(SeatingTable::class));
    });
});

// ─── AdminController::index() ──────────────────────────────

describe('AdminController::index() のデータ構造', function () {

    it('View に渡す変数の型が保証されている', function () {
        $admin = makeAdmin();
        $data  = $this->actingAs($admin)->get('/admin')
            ->assertStatus(200)
            ->getOriginalContent()->getData();

        // EloquentCollection<User>
        expect($data['guests'])->toBeInstanceOf(EloquentCollection::class);

        // array<string, int>
        expect($data['summary'])->toBeArray();
        expect($data['summary']['total'])->toBeInt();
        expect($data['summary']['attending'])->toBeInt();
        expect($data['summary']['declining'])->toBeInt();
        expect($data['summary']['pending'])->toBeInt();
    });

    it('summary の不変条件: attending + declining + pending = total', function () {
        $admin = makeAdmin();
        makeGuest('attending');
        makeGuest('declining');

        $summary = $this->actingAs($admin)->get('/admin')
            ->getOriginalContent()->getData()['summary'];

        expect($summary['attending'] + $summary['declining'] + $summary['pending'])
            ->toBe($summary['total']);
    });

    it('guests コレクションの各要素は User モデルである', function () {
        $admin = makeAdmin();
        makeGuest('attending');

        $guests = $this->actingAs($admin)->get('/admin')
            ->getOriginalContent()->getData()['guests'];

        expect($guests)->not->toBeEmpty();
        $guests->each(fn($u) => expect($u)->toBeInstanceOf(User::class));
    });

    it('$summary に Collection が混入していない', function () {
        $admin   = makeAdmin();
        $summary = $this->actingAs($admin)->get('/admin')
            ->getOriginalContent()->getData()['summary'];

        foreach ($summary as $key => $value) {
            expect($value)->toBeInt("summary['{$key}'] は int でなければならない");
        }
    });
});

// ─── AdminOperationsController ───────────────────────────

describe('AdminOperationsController::index() のデータ構造', function () {

    it('View に渡す変数の型が保証されている', function () {
        $data = $this->actingAs(makeAdmin())->get('/admin/ops')
            ->assertStatus(200)
            ->getOriginalContent()
            ->getData();

        expect($data['summary'])->toBeArray();
        expect($data['recentResponses'])->toBeIterable();
        expect($data['recentCheckins'])->toBeIterable();
        expect($data['recentOperationLogs'])->toBeIterable();
    });
});

// ─── AdminCheckInController ──────────────────────────────

describe('AdminCheckInController::index() のデータ構造', function () {

    it('View に渡す変数の型が保証されている', function () {
        $data = $this->actingAs(makeAdmin())->get('/admin/check-in')
            ->assertStatus(200)
            ->getOriginalContent()
            ->getData();

        expect($data['summary'])->toBeArray();
        expect($data['checkedInProfiles'])->toBeIterable();
        expect($data['focusProfile'] ?? null)->toBeNull();
    });
});

describe('AdminCheckInController::guests() のデータ構造', function () {

    it('View に渡す変数の型が保証されている', function () {
        $data = $this->actingAs(makeAdmin())->get(route('admin.checkin.guests'))
            ->assertStatus(200)
            ->getOriginalContent()
            ->getData();

        expect($data['summary'])->toBeArray();
        expect($data['guests'])->toBeIterable();
        expect($data['status'])->toBeString();
        expect($data['statusLabels'])->toBeArray();
    });
});

// ─── Seat::typeConfig() の構造保証 ─────────────────────────

describe('Seat::typeConfig() の構造', function () {

    it('全タイプに label / color / bg キーが存在し正しい型を持つ', function () {
        $config = Seat::typeConfig();
        expect($config)->toBeArray()->not->toBeEmpty();

        foreach ($config as $type => $cfg) {
            expect($cfg)->toHaveKey('label');
            expect($cfg)->toHaveKey('color');
            expect($cfg)->toHaveKey('bg');
            expect($cfg['label'])->toBeString()->not->toBeEmpty();
            expect($cfg['color'])->toBeString()->toStartWith('#');
            expect($cfg['bg'])->toBeString()->toStartWith('#');
        }
    });

    it('normal タイプが必ず存在する（デフォルト値）', function () {
        expect(Seat::typeConfig())->toHaveKey('normal');
    });
});
