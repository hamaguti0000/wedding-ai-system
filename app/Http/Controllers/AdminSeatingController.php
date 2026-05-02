<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\SeatingTable;
use App\Models\User;
use App\Models\WeddingSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSeatingController extends Controller
{
    private const MAX_SEATS_PER_TABLE = 8;

    public function index()
    {
        // ── Display data: View に渡す Collection ───────────────────────────
        // *Guests suffix → Collection<User> であることを名前で保証する

        $tables = SeatingTable::with([
            'seats.assignment.user.guestProfile',
        ])->orderBy('display_order')->get();

        // 内部計算用（View には渡さない）
        $attendingAll = User::where('role', 'guest')
            ->whereHas('guestProfile', fn($q) => $q->where('participation', 'attending'))
            ->with(['guestProfile', 'seatAssignment.seat.seatingTable'])
            ->get();

        $assignedGuests   = $attendingAll->filter(fn($u) => $u->seatAssignment !== null)->values();
        $unassignedGuests = $attendingAll->filter(fn($u) => $u->seatAssignment === null)->values();

        // ── Aggregate data: int のみ、Collection を含まない ────────────────
        $summary = [
            'total'      => $attendingAll->count(),      // int
            'assigned'   => $assignedGuests->count(),    // int
            'unassigned' => $unassignedGuests->count(),  // int
        ];

        $typeConfig = Seat::typeConfig();
        $setting    = WeddingSetting::first();

        // View に渡すデータの責務:
        //   $tables           → EloquentCollection<SeatingTable>
        //   $assignedGuests   → Collection<User>  (配置済み出席者)
        //   $unassignedGuests → Collection<User>  (未配置出席者)
        //   $summary          → array<string, int> (集計値のみ)
        //   $typeConfig       → array<string, array> (席タイプ設定)
        return view('admin.seating', compact(
            'tables', 'assignedGuests', 'unassignedGuests', 'summary', 'typeConfig', 'setting'
        ));
    }

    // ── テーブル ────────────────────────────────────────────

    public function storeTable(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:50',
            'seat_count'  => 'nullable|integer|min:0|max:' . self::MAX_SEATS_PER_TABLE,
        ], [
            'name.required' => 'テーブル名を入力してください',
            'seat_count.max' => '1テーブルの最大席数は8席です',
        ]);

        $count  = SeatingTable::count();
        $col    = $count % 4;
        $row    = (int) floor($count / 4);
        $posX   = 24 + $col * 380;
        $posY   = 24 + $row * 280;

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
        $seat = Seat::findOrFail($seatId);
        $userId = $seat->assignment?->user_id;
        $seat->delete(); // cascade で seat_assignments も削除

        return response()->json(['success' => true, 'freed_user_id' => $userId]);
    }

    // ── 配置 ────────────────────────────────────────────────

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
        SeatAssignment::where('user_id', $userId)->delete();
        return response()->json(['success' => true]);
    }

    private static function seatSlots(): array
    {
        return [
            ['x' => 18,  'y' => 12],
            ['x' => 104, 'y' => 12],
            ['x' => 190, 'y' => 12],
            ['x' => 276, 'y' => 12],
            ['x' => 18,  'y' => 94],
            ['x' => 104, 'y' => 94],
            ['x' => 190, 'y' => 94],
            ['x' => 276, 'y' => 94],
        ];
    }
}
