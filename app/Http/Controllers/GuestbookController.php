<?php

namespace App\Http\Controllers;

use App\Models\GuestbookMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestbookController extends Controller
{
    public function index()
    {
        $messages = GuestbookMessage::where('is_public', true)
            ->with('user')
            ->latest()
            ->get();

        $myMessage = GuestbookMessage::where('user_id', Auth::id())->first();

        return view('guestbook', compact('messages', 'myMessage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'sticker' => 'nullable|string|max:10',
        ], [
            'message.required' => 'メッセージを入力してください',
            'message.max'      => '500文字以内で入力してください',
        ]);

        // 1ユーザー1メッセージ（既存は更新）
        GuestbookMessage::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'message'   => $request->message,
                'sticker'   => $request->sticker ?: null,
                'is_public' => true,
            ]
        );

        return back()->with('success', 'メッセージを投稿しました！');
    }

    public function destroy()
    {
        GuestbookMessage::where('user_id', Auth::id())->delete();
        return back()->with('success', 'メッセージを削除しました');
    }
}
