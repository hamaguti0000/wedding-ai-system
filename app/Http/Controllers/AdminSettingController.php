<?php

namespace App\Http\Controllers;

use App\Models\WeddingSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function edit()
    {
        $setting = WeddingSetting::firstOrNew([]);

        return view('admin.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'groom_name'     => 'required|string|max:50',
            'bride_name'     => 'required|string|max:50',
            'ceremony_date'  => 'required|date',
            'ceremony_time'  => 'required|date_format:H:i',
            'reception_time' => 'required|date_format:H:i',
            'venue_name'     => 'required|string|max:100',
            'venue_address'  => 'required|string|max:200',
            'venue_url'      => 'nullable|url|max:500',
            'message'        => 'nullable|string|max:1000',
        ], [
            'groom_name.required'     => '新郎名は必須です',
            'bride_name.required'     => '新婦名は必須です',
            'ceremony_date.required'  => '挙式日は必須です',
            'ceremony_date.date'      => '正しい日付を入力してください',
            'ceremony_time.required'  => '挙式時刻は必須です',
            'ceremony_time.date_format' => '時刻は HH:MM 形式で入力してください',
            'reception_time.required' => '披露宴時刻は必須です',
            'reception_time.date_format' => '時刻は HH:MM 形式で入力してください',
            'venue_name.required'     => '会場名は必須です',
            'venue_address.required'  => '会場住所は必須です',
            'venue_url.url'           => '正しいURL形式で入力してください',
        ]);

        WeddingSetting::updateOrCreate(
            [],
            [
                'groom_name'        => $request->groom_name,
                'bride_name'        => $request->bride_name,
                'ceremony_date'     => $request->ceremony_date,
                'ceremony_time'     => $request->ceremony_time . ':00',
                'reception_time'    => $request->reception_time . ':00',
                'venue_name'        => $request->venue_name,
                'venue_address'     => $request->venue_address,
                'venue_url'         => $request->venue_url ?: null,
                'message'           => $request->message,
                'is_seating_public' => $request->boolean('is_seating_public'),
            ]
        );

        return redirect()->route('admin.settings')
            ->with('success', '式の情報を更新しました');
    }
}
