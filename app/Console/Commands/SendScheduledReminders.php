<?php

namespace App\Console\Commands;

use App\Http\Controllers\AdminReminderController;
use App\Models\EmailAuditLog;
use App\Models\ReminderSchedule;
use Illuminate\Console\Command;

class SendScheduledReminders extends Command
{
    protected $signature   = 'reminders:send-scheduled';
    protected $description = '予約済みリマインダーのうち送信時刻を過ぎたものを送信する';

    public function handle(AdminReminderController $controller): int
    {
        $due = ReminderSchedule::where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->line('送信対象のリマインダーはありません');
            return self::SUCCESS;
        }

        foreach ($due as $reminder) {
            $this->info("送信中: [{$reminder->id}] {$reminder->title}");
            $recipients = $reminder->resolveRecipients();
            $count      = 0;

            foreach ($recipients as $user) {
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->send(new \App\Mail\ReminderMail($user, $reminder));
                    $this->recordDeliveryLog($reminder, $user, true);
                    $count++;
                } catch (\Throwable $e) {
                    $this->recordDeliveryLog($reminder, $user, false, $e);
                    $this->warn("  送信失敗: {$user->email} — {$e->getMessage()}");
                }
            }

            $reminder->update([
                'status'     => 'sent',
                'sent_at'    => now(),
                'sent_count' => $count,
            ]);

            $this->info("  → {$count}件送信完了");
        }

        return self::SUCCESS;
    }

    private function recordDeliveryLog(
        ReminderSchedule $reminder,
        \App\Models\User $user,
        bool $sent,
        ?\Throwable $exception = null
    ): void {
        EmailAuditLog::create([
            'user_id' => $user->id,
            'actor_user_id' => null,
            'action' => $sent ? 'reminder_sent' : 'reminder_failed',
            'old_email' => null,
            'new_email' => $user->email,
            'message' => $sent
                ? "予約リマインダーメールを送信しました: {$reminder->subject}"
                : "予約リマインダーメールの送信に失敗しました: {$reminder->subject}",
            'meta' => [
                'reminder_schedule_id' => $reminder->id,
                'title' => $reminder->title,
                'subject' => $reminder->subject,
                'target' => $reminder->target,
                'target_event_day' => $reminder->target_event_day,
                'email' => $user->email,
                'error' => $exception?->getMessage(),
            ],
        ]);
    }
}
