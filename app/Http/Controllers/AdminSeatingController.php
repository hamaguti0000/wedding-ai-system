<?php

namespace App\Http\Controllers;

use App\Models\GuestGroup;
use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\SeatingTable;
use App\Models\User;
use App\Models\WeddingSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class AdminSeatingController extends Controller
{
    private const MAX_SEATS_PER_TABLE = 8;
    private const TABLE_COLUMNS = 8;

    public function index()
    {
        // ── Display data: View に渡す Collection ───────────────────────────
        // *Guests suffix → Collection<User> であることを名前で保証する

        $hasGuestGroups = Schema::hasTable('guest_groups');
        $tableRelations = ['seats.assignment.user.guestProfile'];
        if ($hasGuestGroups) {
            $tableRelations[] = 'assignedGroups.primaryGuest';
        }

        $tables = SeatingTable::with($tableRelations)->orderBy('display_order')->get();
        if (! $hasGuestGroups) {
            $tables->each->setRelation('assignedGroups', collect());
        }

        // 内部計算用（View には渡さない）
        $attendingAll = User::where('role', 'guest')
            ->whereHas('guestProfile', fn($q) => $q->where('participation', 'attending'))
            ->with(['guestProfile', 'seatAssignment.seat.seatingTable'])
            ->get();

        $assignedGuests   = $attendingAll->filter(fn($u) => $u->seatAssignment !== null)->values();
        $unassignedGuests = $attendingAll->filter(fn($u) => $u->seatAssignment === null)->values();

        // 席の「ゲストを選ぶ」セレクトに使う、名前順の全出席者一覧
        $allGuests = $attendingAll->sortBy(function (User $u) {
            $p = $u->guestProfile;
            return $p ? $p->last_name.$p->first_name : $u->name;
        })->values();

        // ── Aggregate data: int のみ、Collection を含まない ────────────────
        $summary = [
            'total'      => $attendingAll->count(),      // int
            'assigned'   => $assignedGuests->count(),    // int
            'unassigned' => $unassignedGuests->count(),  // int
        ];

        $typeConfig = Seat::typeConfig();
        $setting    = WeddingSetting::first();
        $seatingGroups = $hasGuestGroups
            ? GuestGroup::with(['primaryGuest', 'assignedSeatingTables'])
                ->get()
                ->sortBy(fn (GuestGroup $group) => $group->displayName())
                ->values()
            : collect();

        // View に渡すデータの責務:
        //   $tables           → EloquentCollection<SeatingTable>
        //   $assignedGuests   → Collection<User>  (配置済み出席者)
        //   $unassignedGuests → Collection<User>  (未配置出席者)
        //   $allGuests        → Collection<User>  (氏名順の全出席者、席選択セレクト用)
        //   $summary          → array<string, int> (集計値のみ)
        //   $typeConfig       → array<string, array> (席タイプ設定)
        return view('admin.seating', compact(
            'tables', 'assignedGuests', 'unassignedGuests', 'allGuests', 'summary', 'typeConfig', 'setting', 'seatingGroups'
        ));
    }

    /** 印刷・共有用の読み取り専用ページ（優雅な一覧表示）*/
    public function print()
    {
        $tables = SeatingTable::with([
            'seats.assignment.user.guestProfile',
        ])->orderBy('display_order')->get();

        $setting = WeddingSetting::first();

        return view('admin.seating-print', compact('tables', 'setting'));
    }

    /** ゲスト非公開のまま、管理者がゲスト向け席次表を確認するページ */
    public function guestPreview()
    {
        $tables = SeatingTable::with([
            'seats.assignment.user.guestProfile',
        ])->orderBy('display_order')->get();

        $setting = WeddingSetting::first();
        $typeConfig = Seat::typeConfig();
        $user = auth()->user();
        $profile = null;
        $mySeat = null;
        $myTableId = null;
        $isPublished = $tables->isNotEmpty()
            && $tables->some(fn($t) => $t->seats->isNotEmpty());

        return view('seating.guest', compact(
            'tables',
            'mySeat',
            'myTableId',
            'user',
            'profile',
            'setting',
            'typeConfig',
            'isPublished'
        ));
    }

    /** ゲストを選択してエスコートカードを印刷するページ */
    public function escortCards(Request $request)
    {
        return view('admin.escort-cards', $this->escortCardData($request));
    }


    public function escortCardsPdf(Request $request)
    {
        $data = $this->escortCardData($request);

        $payload = [
            // 新郎新婦名・挙式日はescort-template.png自体に描き込み済みのため、
            // ここでは組み立てない(スクリプト側で重ねて描画すると二重表示になる)。
            'template' => public_path('images/escort-template.png'),
            'guests' => $data['guests']->map(function (User $guest) use ($data) {
                $profile = $guest->guestProfile;
                $table = $guest->seatAssignment?->seat?->seatingTable;
                $kanjiName = $profile ? trim(($profile->last_name ?? '') . ' ' . ($profile->first_name ?? '')) : $guest->name;
                [$firstNameEn, $lastNameEn] = $this->romanNameParts($guest->username);

                return [
                    // usernameは「名前_苗字」のローマ字で登録されている運用のため、それを氏名の
                    // 正式なローマ字表記として使う(ふりがな未入力のゲストが多く自動変換は不可)。
                    'first_name' => $firstNameEn,
                    'last_name' => $lastNameEn,
                    'name' => $kanjiName ?: $guest->username,
                    'table' => $table ? ($data['tableMarks'][$table->id] ?? '') : '',
                ];
            })->values()->all(),
        ];

        $dir = storage_path('app/escort-cards');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $token = uniqid('escort_', true);
        $jsonPath = $dir . '/' . $token . '.json';
        $pdfPath = $dir . '/' . $token . '.pdf';
        file_put_contents($jsonPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $process = new Process([
            'python3',
            base_path('scripts/generate_escort_cards_pdf.py'),
            $jsonPath,
            $pdfPath,
        ]);
        $process->setTimeout(180);
        $process->run();

        @unlink($jsonPath);

        if (! $process->isSuccessful() || ! is_file($pdfPath)) {
            report(new \RuntimeException('Escort PDF generation failed: ' . $process->getErrorOutput() . $process->getOutput()));
            abort(500, 'PDF生成に失敗しました。');
        }

        return response()->file($pdfPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="escort-cards.pdf"',
        ])->deleteFileAfterSend(true);
    }

    private function escortCardData(Request $request): array
    {
        $tables = SeatingTable::query()
            ->orderBy('display_order')
            ->get(['id', 'name']);

        $tableMarks = $tables
            ->values()
            ->mapWithKeys(fn ($table, $index) => [
                $table->id => SeatingTable::letterForIndex($index),
            ]);

        $allGuests = User::query()
            ->where('role', 'guest')
            ->with(['guestProfile', 'seatAssignment.seat.seatingTable'])
            ->get()
            ->sortBy(function (User $user) {
                $profile = $user->guestProfile;

                return sprintf(
                    '%s-%s-%s',
                    $profile?->participation ?? 'pending',
                    $profile?->furigana() ?: ($profile?->last_name ?? $user->name),
                    $profile?->first_name ?? ''
                );
            })
            ->values();

        $defaultSelectedIds = $allGuests
            ->filter(fn (User $user) =>
                $user->guestProfile?->participation === 'attending'
                && $user->seatAssignment?->seat?->seatingTable
            )
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $selectedIds = collect($request->input('print_user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if (! $request->has('selection_submitted')) {
            $selectedIds = $defaultSelectedIds;
        }

        $selectedIdSet = $selectedIds->flip();

        $guests = $allGuests
            ->filter(fn (User $user) => $selectedIdSet->has($user->id))
            ->sortBy(function (User $user) {
                $profile = $user->guestProfile;

                return sprintf(
                    '%04d-%s-%s',
                    $user->seatAssignment?->seat?->seatingTable?->display_order ?? 9999,
                    $profile?->last_name ?? $user->name,
                    $profile?->first_name ?? ''
                );
            })
            ->values();

        return [
            'allGuests' => $allGuests,
            'guests' => $guests,
            'selectedIds' => $selectedIds,
            'setting' => WeddingSetting::first(),
            'tableMarks' => $tableMarks,
        ];
    }

    /**
     * 「firstname_lastname」形式のusernameを Title Case のローマ字氏名に分解する。
     * 形式に合わないアカウント(テストユーザー等)は空文字を返し、呼び出し側で漢字表記にフォールバックする。
     *
     * @return array{0: string, 1: string} [firstName, lastName]
     */
    private function romanNameParts(string $username): array
    {
        $parts = explode('_', $username, 2);

        if (count($parts) !== 2) {
            return ['', ''];
        }

        $titleCase = fn (string $s) => ucfirst(strtolower(trim($s)));

        return [$titleCase($parts[0]), $titleCase($parts[1])];
    }

    // ── テーブル ────────────────────────────────────────────

    public function storeTable(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:50',
            'seat_count'  => 'nullable|integer|min:0|max:' . self::MAX_SEATS_PER_TABLE,
            'pos_x'       => 'nullable|integer|min:0|max:9999',
            'pos_y'       => 'nullable|integer|min:0|max:9999',
        ], [
            'name.required' => 'テーブル名を入力してください',
            'seat_count.max' => '1テーブルの最大席数は8席です',
        ]);

        $count = SeatingTable::count();
        // 配置図へのドラッグ&ドロップで作成した場合は落とした位置をそのまま使う。
        // 表側の「テーブルを追加」フォームからの場合は座標が来ないので自動配置する。
        if ($request->filled('pos_x') && $request->filled('pos_y')) {
            $posX = (int) $request->pos_x;
            $posY = (int) $request->pos_y;
        } else {
            [$posX, $posY] = $this->findFreeTablePosition();
        }

        $table = SeatingTable::create([
            'name'          => $request->name,
            'display_order' => $count + 1,
            'pos_x'         => $posX,
            'pos_y'         => $posY,
        ]);

        // 初期席を自動作成（seat_count が指定された場合、最大8席）
        $seatCount = (int) ($request->seat_count ?? 0);
        $seatSlots = self::seatSlots();
        $seats = [];
        for ($i = 0; $i < $seatCount; $i++) {
            $seats[] = Seat::create([
                'seating_table_id' => $table->id,
                'type'             => 'normal',
                'pos_x'            => $seatSlots[$i]['x'],
                'pos_y'            => $seatSlots[$i]['y'],
            ]);
        }

        return response()->json([
            'success' => true,
            'table'   => $table,
            'seats'   => $seats,
        ]);
    }

    public function destroyTable(int $tableId): JsonResponse
    {
        SeatingTable::findOrFail($tableId)->delete();
        return response()->json(['success' => true]);
    }

    public function updatePosition(Request $request, int $tableId): JsonResponse
    {
        $request->validate([
            'x' => 'required|integer|min:0|max:9999',
            'y' => 'required|integer|min:0|max:9999',
        ]);

        SeatingTable::findOrFail($tableId)->update([
            'pos_x' => $request->x,
            'pos_y' => $request->y,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateTable(Request $request, int $tableId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
        ], [
            'name.required' => 'テーブル名を入力してください',
        ]);

        $table = SeatingTable::findOrFail($tableId);
        $table->update(['name' => $request->name]);

        return response()->json(['success' => true, 'table' => $table]);
    }

    // ── 席 ──────────────────────────────────────────────────

    /** テーブルに席を追加（最大8席） */
    public function storeSeat(Request $request, int $tableId): JsonResponse
    {
        $table = SeatingTable::findOrFail($tableId);

        if ($table->seats()->count() >= self::MAX_SEATS_PER_TABLE) {
            return response()->json(['error' => '1テーブルの最大席数は8席です'], 422);
        }

        $request->validate([
            'type'  => 'nullable|string|max:30',
            'label' => 'nullable|string|max:20',
            'pos_x' => 'nullable|integer|min:0',
            'pos_y' => 'nullable|integer|min:0',
        ]);

        $seatSlots = self::seatSlots();
        $slotIdx   = $table->seats()->count(); // 追加前の数 = 次のスロット番号

        $seat = Seat::create([
            'seating_table_id' => $tableId,
            'type'             => $request->type ?? 'normal',
            'label'            => $request->label,
            'pos_x'            => $request->pos_x ?? $seatSlots[$slotIdx]['x'],
            'pos_y'            => $request->pos_y ?? $seatSlots[$slotIdx]['y'],
        ]);

        return response()->json(['success' => true, 'seat' => $seat]);
    }

    /** 席の属性（type / label / pos）を更新 */
    public function updateSeat(Request $request, int $seatId): JsonResponse
    {
        $request->validate([
            'type'  => 'nullable|string|max:30',
            'label' => 'nullable|string|max:20',
            'pos_x' => 'nullable|integer|min:0|max:9999',
            'pos_y' => 'nullable|integer|min:0|max:9999',
        ]);

        $seat = Seat::findOrFail($seatId);
        $seat->update(array_filter(
            $request->only(['type', 'label', 'pos_x', 'pos_y']),
            fn($v) => $v !== null
        ));

        return response()->json(['success' => true, 'seat' => $seat]);
    }

    /** 席を削除（配置済みゲストは未配置へ戻る）*/
    public function destroySeat(int $seatId): JsonResponse
    {
        $seat = Seat::with('assignment.user.guestProfile')->findOrFail($seatId);
        $freedGuest = $seat->assignment?->user ? $this->serializeGuest($seat->assignment->user) : null;
        $seat->delete(); // cascade で seat_assignments も削除

        return response()->json(['success' => true, 'freed_guest' => $freedGuest]);
    }

    // ── 配置 ────────────────────────────────────────────────

    public function assignGroup(Request $request): JsonResponse
    {
        if (! Schema::hasTable('guest_groups')) {
            return response()->json(['error' => 'グループ機能はまだ利用できません'], 422);
        }

        $request->validate([
            'guest_group_id' => 'required|string|exists:guest_groups,id',
            'seating_table_id' => 'nullable|integer|exists:seating_tables,id',
        ]);

        DB::table('seating_table_group_assignments')
            ->where('guest_group_id', $request->guest_group_id)
            ->delete();

        if ($request->filled('seating_table_id')) {
            DB::table('seating_table_group_assignments')->insert([
                'guest_group_id' => $request->guest_group_id,
                'seating_table_id' => (int) $request->seating_table_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'guest_group_id' => $request->guest_group_id,
            'seating_table_id' => $request->input('seating_table_id'),
        ]);
    }

    /** ゲストを席に配置（移動も兼ねる）*/
    public function assign(Request $request): JsonResponse
    {
        $request->validate([
            'seat_id' => 'required|exists:seats,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $seat = Seat::with('assignment')->findOrFail($request->seat_id);

        // 席が別のゲストに占有されていたら拒否
        if ($seat->assignment && $seat->assignment->user_id !== (int) $request->user_id) {
            return response()->json(['error' => 'この席には既に別のゲストが配置されています'], 422);
        }

        // ゲストの既存配置を解除
        SeatAssignment::where('user_id', $request->user_id)->delete();

        SeatAssignment::create([
            'seat_id' => $request->seat_id,
            'user_id' => $request->user_id,
        ]);

        return response()->json(['success' => true, 'seat_id' => $seat->id]);
    }

    /** 配置を解除 */
    public function unassign(int $userId): JsonResponse
    {
        $user = User::with('guestProfile')->find($userId);
        SeatAssignment::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'guest'   => $user ? $this->serializeGuest($user) : null,
        ]);
    }

    /** ゲストカードの表示に必要な情報だけを抜き出す（未配置プールへの復帰表示に使う） */
    private function serializeGuest(User $user): array
    {
        $p    = $user->guestProfile;
        $name = $p ? trim($p->last_name.' '.$p->first_name) : $user->name;

        return [
            'user_id' => $user->id,
            'name'    => $name,
            'initial' => mb_substr($name, 0, 1, 'UTF-8'),
            'side'    => $p?->guest_side,
            'rel'     => $p?->relationship,
        ];
    }

    /**
     * 新規テーブルを既存テーブルと重ならない位置に配置する。
     * テーブルは最大4列×2行・8席（横60px間隔・縦78px間隔、ヘッダー込みで
     * おおよそ240×220px）になり得るため、それを1マスとしたグリッドを走査し、
     * 既存テーブルの矩形と重ならない最初の空きマスを返す。
     * ドラッグで自由に動かした後でも、新規追加時に自動で衝突を避けられる。
     *
     * @return array{0: int, 1: int}
     */
    private function findFreeTablePosition(): array
    {
        $cellW  = 240;
        $cellH  = 220;
        $margin = 24;
        $maxCols = self::TABLE_COLUMNS;

        $existing = SeatingTable::query()->get(['pos_x', 'pos_y']);

        for ($row = 0; $row < 100; $row++) {
            for ($col = 0; $col < $maxCols; $col++) {
                $x = $margin + $col * $cellW;
                $y = $margin + $row * $cellH;

                $overlaps = $existing->contains(
                    fn (SeatingTable $t): bool => abs((int) $t->pos_x - $x) < $cellW
                        && abs((int) $t->pos_y - $y) < $cellH
                );

                if (! $overlaps) {
                    return [$x, $y];
                }
            }
        }

        // 理論上ほぼ到達しないが、念のため既存テーブル数ベースの位置にフォールバック
        $count = $existing->count();

        return [$margin + ($count % $maxCols) * $cellW, $margin + intdiv($count, $maxCols) * $cellH];
    }

    /**
     * 1テーブル最大8席（4列×2行）の相対座標。
     * 横60px・縦78px間隔（席の丸34px＋氏名ラベル分の余白）を確保し、
     * 印刷・プレビュー表示で隣の席の名前ラベルと重ならないようにしている。
     */
    private static function seatSlots(): array
    {
        return [
            ['x' => 14,  'y' => 12],
            ['x' => 74,  'y' => 12],
            ['x' => 134, 'y' => 12],
            ['x' => 194, 'y' => 12],
            ['x' => 14,  'y' => 90],
            ['x' => 74,  'y' => 90],
            ['x' => 134, 'y' => 90],
            ['x' => 194, 'y' => 90],
        ];
    }

}
