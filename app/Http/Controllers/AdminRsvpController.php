<?php

namespace App\Http\Controllers;

use App\Models\SeatingTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminRsvpController extends Controller
{
    /**
     * ゲスト側(新郎/新婦)→ご関係の順で並び替えるための優先度。
     * 数字が小さいほど先に並ぶ。未設定(null)は各グループの末尾に回す。
     */
    private const SIDE_ORDER = ['groom' => 0, 'bride' => 1];
    private const RELATIONSHIP_ORDER = ['family' => 0, 'friend' => 1, 'other' => 2, 'colleague' => 3];

    public function export(Request $request)
    {
        $filter = (string)($request->get('filter') ?: 'all');

        $guests = User::where('role', 'guest')
            ->with(['guestProfile', 'seatAssignment.seat.seatingTable'])
            ->orderBy('created_at')
            ->get();

        $filtered = match($filter) {
            'attending' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'attending'),
            'declining' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'declining'),
            'pending'   => $guests->filter(fn($u) => !$u->guestProfile || $u->guestProfile->participation === 'pending'),
            default     => $guests,
        };

        // 新郎側全員 → 新婦側全員、各側の中では親族 → 友人 → その他 → 会社関係の順
        $sorted = $filtered
            ->sortBy(function (User $u) {
                $p = $u->guestProfile;
                $sideRank = self::SIDE_ORDER[$p?->guest_side] ?? count(self::SIDE_ORDER);
                $relRank  = self::RELATIONSHIP_ORDER[$p?->relationship] ?? count(self::RELATIONSHIP_ORDER);

                return sprintf('%d-%d', $sideRank, $relRank);
            })
            ->values();

        // 卓の記号(A〜Z、続けてa〜z…)はseating_tablesのdisplay_order順インデックスから決まる
        $tableLetters = SeatingTable::query()
            ->orderBy('display_order')
            ->pluck('id')
            ->values()
            ->mapWithKeys(fn ($id, $index) => [$id => SeatingTable::letterForIndex($index)]);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rsvp_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($sorted, $tableLetters) {
            $out = fopen('php://output', 'w');
            // BOM for Excel
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                '番号', '卓',
                'ユーザー名', '姓', '名', 'フリガナ姓', 'フリガナ名',
                'お立場', 'ご関係', '出欠',
                '出席人数（合計）', 'うちお子様',
                'アレルギー', 'アレルギー詳細',
                '電話番号', '郵便番号', '住所',
                'メモ', '回答日時',
            ]);

            $number = 0;
            foreach ($sorted as $u) {
                $number++;
                $p = $u->guestProfile;
                $tableId = $u->seatAssignment?->seat?->seatingTable?->id;

                fputcsv($out, [
                    $number,
                    $tableId ? ($tableLetters[$tableId] ?? '') : '',
                    $u->username,
                    $p?->last_name ?? '',
                    $p?->first_name ?? '',
                    $p?->furigana_sei ?? '',
                    $p?->furigana_mei ?? '',
                    $p ? $p->guestSideLabel() : '',
                    $p ? $p->relationshipLabel() : '',
                    $p ? $p->participationLabel() : '未回答',
                    $p?->attending_count ?? 0,
                    $p?->children_count ?? 0,
                    $p?->has_allergy ? 'あり' : 'なし',
                    $p?->allergy_notes ?? '',
                    $p?->phone ?? '',
                    $p?->postal_code ?? '',
                    $p?->address ?? '',
                    $p?->notes ?? '',
                    $p?->responded_at?->format('Y/m/d H:i') ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
