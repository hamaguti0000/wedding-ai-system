<?php

namespace App\Http\Controllers;

use App\Models\WeddingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProfileController extends Controller
{
    public function edit()
    {
        return view('admin.profiles', [
            'setting' => WeddingSetting::firstOrNew([]),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'groom_bio'   => 'nullable|string|max:2000',
            'bride_bio'   => 'nullable|string|max:2000',
            'groom_photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'bride_photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'groom_photo.image' => '新郎の写真は画像ファイルを選択してください',
            'groom_photo.max'   => '新郎の写真は5MB以下にしてください',
            'bride_photo.image' => '新婦の写真は画像ファイルを選択してください',
            'bride_photo.max'   => '新婦の写真は5MB以下にしてください',
        ]);

        $data = [
            'groom_bio' => $request->groom_bio ?: null,
            'bride_bio' => $request->bride_bio ?: null,
        ];

        $existing = WeddingSetting::first();

        foreach (['groom', 'bride'] as $person) {
            if ($request->hasFile("{$person}_photo")) {
                $old = $existing?->{"{$person}_photo"};
                if ($old) Storage::disk('public')->delete($old);

                $file = $request->file("{$person}_photo");
                $ext  = $file->getClientOriginalExtension();
                $path = $file->storeAs('profiles', "{$person}.{$ext}", 'public');
                $data["{$person}_photo"] = $path;
            }
        }

        WeddingSetting::updateOrCreate([], $data);

        return redirect()->route('admin.profiles')
            ->with('success', 'プロフィールを更新しました');
    }
}
