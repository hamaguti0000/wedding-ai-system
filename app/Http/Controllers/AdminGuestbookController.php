<?php

namespace App\Http\Controllers;

use App\Models\GuestbookMessage;
use Illuminate\Http\Request;

class AdminGuestbookController extends Controller
{
    public function index()
    {
        $messages = GuestbookMessage::with('user')->latest()->get();
        return view('admin.guestbook', compact('messages'));
    }

    public function update(Request $request, int $id)
    {
        GuestbookMessage::findOrFail($id)->update([
            'is_public' => $request->boolean('is_public'),
        ]);
        return back()->with('success', '更新しました');
    }

    public function destroy(int $id)
    {
        GuestbookMessage::findOrFail($id)->delete();
        return back()->with('success', '削除しました');
    }
}
