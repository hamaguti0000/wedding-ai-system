<?php

namespace App\Http\Controllers;

use App\Mail\ReminderMail;
use App\Models\ReminderSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AdminReminderController extends Controller
{
    public function index()
    {
        $reminders = ReminderSchedule::with('creator')
            ->orderByDesc('created_at')->get();

        $guests = User::with('guestProfile')
            ->where('role', 'guest')
            ->orderBy('name')
            ->get();

        return view('admin.reminders', compact('reminders', 'guests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:100',
            'target'       => 'required|in:all,attending,not_responded,selected',
            'selected_user_ids'   => 'required_if:target,selected|array',
            'selected_user_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', 'guest')],
            'subject'      => 'required|string|max:200',
            'message'      => 'required|string|max:3000',
            'scheduled_at' => 'nullable|date|after:now',
        ], [
            'title.required'        => 'タイトルを入力してください',
            'subject.required'      => '件名を入力してください',
            'message.required'      => '本文を入力してください',
            'scheduled_at.after'    => '予約日時は現在より未来を指定してください',
            'selected_user_ids.required_if' => '個別選択の場合は送信するゲストを選んでください',
        ]);

        $selectedUserIds = collect($request->input('selected_user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $reminder = ReminderSchedule::create([
            'title'               => $request->title,
            'target'              => $request->target === 'selected' ? 'all' : $request->target,
            'selected_user_ids'   => $request->target === 'selected' ? $selectedUserIds : null,
            'subject'             => $request->subject,
            'message'             => $request->message,
            'scheduled_at'        => $request->scheduled_at ?: null,
            'status'              => 'pending',
            'created_by_user_id'  => Auth::id(),
        ]);

        // 即時送信が選択された場合
        if ($request->boolean('send_now')) {
            return $this->doSend($reminder);
        }

        $msg = $request->scheduled_at
            ? '予約を作成しました（' . \Carbon\Carbon::parse($request->scheduled_at)->format('Y年n月j日 H:i') . ' 送信予定）'
            : '下書きを保存しました';

        return back()->with('success', $msg);
    }

    /** 手動送信（既存リマインダーを今すぐ送信） */
    public function send(int $id)
    {
        $reminder = ReminderSchedule::where('status', 'pending')->findOrFail($id);
        return $this->doSend($reminder);
    }

    public function cancel(int $id)
    {
        $reminder = ReminderSchedule::where('status', 'pending')->findOrFail($id);
        $reminder->update(['status' => 'cancelled']);

        return back()->with('success', 'リマインダーをキャンセルしました');
    }

    public function destroy(int $id)
    {
        ReminderSchedule::findOrFail($id)->delete();
        return back()->with('success', '削除しました');
    }

    /** 実際のメール送信処理（手動・スケジューラー共通） */
    public function doSend(ReminderSchedule $reminder): \Illuminate\Http\RedirectResponse
    {
        $recipients = $reminder->resolveRecipients();
        $count      = 0;
        $errors     = 0;

        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->send(new ReminderMail($user, $reminder));
                $count++;
            } catch (\Throwable) {
                $errors++;
            }
        }

        $reminder->update([
            'status'     => 'sent',
            'sent_at'    => now(),
            'sent_count' => $count,
        ]);

        $msg = "{$count}件送信完了";
        if ($errors > 0) {
            $msg .= "（{$errors}件は送信失敗）";
        }

        return back()->with('success', $msg);
    }
}
