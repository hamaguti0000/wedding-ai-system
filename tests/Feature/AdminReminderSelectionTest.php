<?php

namespace Tests\Feature;

use App\Mail\ReminderMail;
use App\Models\ReminderSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminReminderSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_reminder_to_selected_guests_only(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $selectedA = User::factory()->create(['role' => 'guest', 'email' => 'a@example.com']);
        $selectedB = User::factory()->create(['role' => 'guest', 'email' => 'b@example.com']);
        User::factory()->create(['role' => 'guest', 'email' => 'c@example.com']);

        $this->actingAs($admin)
            ->post(route('admin.reminders.store'), [
                'title' => '個別送信テスト',
                'target' => 'selected',
                'selected_user_ids' => [$selectedA->id, $selectedB->id],
                'subject' => '招待状のお知らせ',
                'message' => 'Web招待状をご確認ください。',
                'send_now' => '1',
            ])
            ->assertRedirect();

        $reminder = ReminderSchedule::firstOrFail();

        $this->assertSame('sent', $reminder->status);
        $this->assertSame(2, $reminder->sent_count);
        $this->assertEqualsCanonicalizing([$selectedA->id, $selectedB->id], $reminder->selected_user_ids);

        Mail::assertSent(ReminderMail::class, 2);
        Mail::assertSent(ReminderMail::class, fn (ReminderMail $mail) => $mail->hasTo('a@example.com'));
        Mail::assertSent(ReminderMail::class, fn (ReminderMail $mail) => $mail->hasTo('b@example.com'));
        Mail::assertNotSent(ReminderMail::class, fn (ReminderMail $mail) => $mail->hasTo('c@example.com'));
    }

    public function test_selected_target_requires_at_least_one_guest(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.reminders'))
            ->post(route('admin.reminders.store'), [
                'title' => '未選択テスト',
                'target' => 'selected',
                'subject' => '件名',
                'message' => '本文',
                'send_now' => '0',
            ])
            ->assertRedirect(route('admin.reminders'))
            ->assertSessionHasErrors('selected_user_ids');
    }
}
