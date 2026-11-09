<?php

use App\Models\GuestProfile;
use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\SeatingTable;

/**
 * CSVをパースして本文だけの配列で返す(BOM除去・ヘッダー行は残す)。
 */
function parseRsvpCsv(string $body): array
{
    $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
    $lines = array_filter(explode("\n", str_replace("\r\n", "\n", $body)), fn ($l) => $l !== '');

    return array_map('str_getcsv', $lines);
}

describe('GET /admin/rsvp/export CSV出力', function () {

    it('番号と卓の列がヘッダーに含まれる', function () {
        $response = $this->actingAs(makeAdmin())->get(route('admin.rsvp.export'));

        $rows = parseRsvpCsv($response->streamedContent());

        expect($rows[0])->toContain('番号', '卓');
    });

    it('卓に配置されたゲストには卓の記号が入り、未配置は空欄になる', function () {
        $table = SeatingTable::create(['name' => 'A卓', 'display_order' => 1, 'pos_x' => 0, 'pos_y' => 0]);
        $seat  = Seat::create(['seating_table_id' => $table->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $seated = makeGuest('attending');
        GuestProfile::where('user_id', $seated->id)->update(['guest_side' => 'groom', 'relationship' => 'family']);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $seated->id]);

        $unseated = makeGuest('attending');
        GuestProfile::where('user_id', $unseated->id)->update(['guest_side' => 'groom', 'relationship' => 'family']);

        $response = $this->actingAs(makeAdmin())->get(route('admin.rsvp.export'));
        $rows = parseRsvpCsv($response->streamedContent());

        $header = $rows[0];
        $tableCol = array_search('卓', $header, true);
        $usernameCol = array_search('ユーザー名', $header, true);

        $seatedRow   = collect($rows)->first(fn ($r) => $r[$usernameCol] === $seated->username);
        $unseatedRow = collect($rows)->first(fn ($r) => $r[$usernameCol] === $unseated->username);

        expect($seatedRow[$tableCol])->toBe('A');
        expect($unseatedRow[$tableCol])->toBe('');
    });

    it('27番目のテーブルは小文字aになる', function () {
        for ($i = 0; $i < 26; $i++) {
            SeatingTable::create(['name' => "卓{$i}", 'display_order' => $i + 1, 'pos_x' => 0, 'pos_y' => 0]);
        }
        $table27 = SeatingTable::create(['name' => '卓27', 'display_order' => 27, 'pos_x' => 0, 'pos_y' => 0]);
        $seat = Seat::create(['seating_table_id' => $table27->id, 'type' => 'normal', 'pos_x' => 0, 'pos_y' => 0]);

        $guest = makeGuest('attending');
        GuestProfile::where('user_id', $guest->id)->update(['guest_side' => 'groom', 'relationship' => 'family']);
        SeatAssignment::create(['seat_id' => $seat->id, 'user_id' => $guest->id]);

        $response = $this->actingAs(makeAdmin())->get(route('admin.rsvp.export'));
        $rows = parseRsvpCsv($response->streamedContent());

        $header = $rows[0];
        $tableCol = array_search('卓', $header, true);
        $usernameCol = array_search('ユーザー名', $header, true);
        $row = collect($rows)->first(fn ($r) => $r[$usernameCol] === $guest->username);

        expect($row[$tableCol])->toBe('a');
    });

    it('新郎側全員→新婦側全員、各側は親族→友人→その他→会社関係の順に並ぶ', function () {
        $make = function (string $side, string $relationship, string $lastName) {
            $guest = makeGuest('attending');
            GuestProfile::where('user_id', $guest->id)->update([
                'guest_side' => $side,
                'relationship' => $relationship,
                'last_name' => $lastName,
            ]);

            return $guest;
        };

        // わざと期待順とは違う順番で作成する
        $brideColleague = $make('bride', 'colleague', '新婦会社');
        $groomFriend     = $make('groom', 'friend', '新郎友人');
        $brideFamily     = $make('bride', 'family', '新婦親族');
        $groomFamily     = $make('groom', 'family', '新郎親族');
        $groomOther      = $make('groom', 'other', '新郎その他');
        $groomColleague  = $make('groom', 'colleague', '新郎会社');
        $brideFriend     = $make('bride', 'friend', '新婦友人');

        $response = $this->actingAs(makeAdmin())->get(route('admin.rsvp.export'));
        $rows = parseRsvpCsv($response->streamedContent());

        $header = $rows[0];
        $lastNameCol = array_search('姓', $header, true);
        $order = collect($rows)->slice(1)->pluck($lastNameCol)->values()->all();

        expect($order)->toBe([
            '新郎親族', '新郎友人', '新郎その他', '新郎会社',
            '新婦親族', '新婦友人', '新婦会社',
        ]);
    });

    it('並び替えた順に1から連番が振られる', function () {
        $make = function (string $side, string $relationship) {
            $guest = makeGuest('attending');
            GuestProfile::where('user_id', $guest->id)->update([
                'guest_side' => $side,
                'relationship' => $relationship,
            ]);

            return $guest;
        };

        $make('bride', 'friend');
        $make('groom', 'family');
        $make('groom', 'friend');

        $response = $this->actingAs(makeAdmin())->get(route('admin.rsvp.export'));
        $rows = parseRsvpCsv($response->streamedContent());

        $header = $rows[0];
        $numberCol = array_search('番号', $header, true);
        $numbers = collect($rows)->slice(1)->pluck($numberCol)->values()->all();

        expect($numbers)->toBe(['1', '2', '3']);
    });
});
