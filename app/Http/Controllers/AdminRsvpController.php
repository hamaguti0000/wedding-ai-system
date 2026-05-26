<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminRsvpController extends Controller
{
    public function export(Request $request)
    {
        $filter = (string)($request->get('filter') ?: 'all');

        $guests = User::where('role', 'guest')
            ->with('guestProfile')
            ->orderBy('created_at')
            ->get();

        $filtered = match($filter) {
            'attending' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'attending'),
            'declining' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'declining'),
            'pending'   => $guests->filter(fn($u) => !$u->guestProfile || $u->guestProfile->participation === 'pending'),
            default     => $guests,
        };

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rsvp_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($filtered) {
            $out = fopen('php://output', 'w');
            // BOM for Excel
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ユーザー名', '姓', '名', 'フリガナ姓', 'フリガナ名',
                'お立場', 'ご関係', '出欠',
                '出席人数（合計）', 'うちお子様',
                'アレルギー', 'アレルギー詳細',
                '電話番号', '郵便番号', '住所',
                'メモ', '回答日時',
            ]);

            foreach ($filtered as $u) {
                $p = $u->guestProfile;
                fputcsv($out, [
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

    public function index(Request $request)
    {
        $guests = User::where('role', 'guest')
            ->with('guestProfile')
            ->orderBy('created_at')
            ->get();

        $summary = [
            'total'     => $guests->count(),
            'attending' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'attending')->count(),
            'declining' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'declining')->count(),
            'pending'   => $guests->filter(fn($u) => !$u->guestProfile || $u->guestProfile->participation === 'pending')->count(),
        ];

        // 出席者の合計人数（大人＋子供）— ビューで全件渡してクライアント側フィルター
        $totalAttending = $guests
            ->filter(fn($u) => $u->guestProfile?->participation === 'attending')
            ->sum(fn($u) => $u->guestProfile->attending_count ?? 0);

        $totalChildren = $guests
            ->filter(fn($u) => $u->guestProfile?->participation === 'attending')
            ->sum(fn($u) => $u->guestProfile->children_count ?? 0);

        return view('admin.rsvp', compact(
            'guests', 'summary', 'totalAttending', 'totalChildren'
        ));
    }
}
