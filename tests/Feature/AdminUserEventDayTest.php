<?php

namespace Tests\Feature;

use App\Models\GuestProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserEventDayTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_guest_defaults_to_day_two(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'username' => 'day2_guest',
                'password' => 'password',
                'role' => 'guest',
                'last_name' => '山田',
                'first_name' => '太郎',
            ])
            ->assertRedirect(route('admin.users'));

        $guest = User::where('username', 'day2_guest')->firstOrFail();
        $this->assertSame('day2', $guest->guestProfile->event_day);
    }

    public function test_admin_can_bulk_move_selected_guests_to_day_one(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guestA = User::factory()->create(['role' => 'guest']);
        $guestB = User::factory()->create(['role' => 'guest']);
        $guestC = User::factory()->create(['role' => 'guest']);

        foreach ([$guestA, $guestB, $guestC] as $guest) {
            GuestProfile::create([
                'user_id' => $guest->id,
                'participation' => 'pending',
                'event_day' => 'day2',
            ]);
        }

        $this->actingAs($admin)
            ->patch(route('admin.users.bulk-event-day'), [
                'user_ids' => [$guestA->id, $guestB->id],
                'event_day' => 'day1',
            ])
            ->assertRedirect(route('admin.users'));

        $this->assertSame('day1', $guestA->fresh()->guestProfile->event_day);
        $this->assertSame('day1', $guestB->fresh()->guestProfile->event_day);
        $this->assertSame('day2', $guestC->fresh()->guestProfile->event_day);
    }

    public function test_inline_relationship_update_does_not_reset_event_day(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guest = User::factory()->create(['role' => 'guest']);
        GuestProfile::create([
            'user_id' => $guest->id,
            'participation' => 'pending',
            'event_day' => 'day1',
            'relationship' => 'friend',
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.users.guest-info', $guest->id), [
                'relationship' => 'family',
            ])
            ->assertOk();

        $profile = $guest->fresh()->guestProfile;
        $this->assertSame('family', $profile->relationship);
        $this->assertSame('day1', $profile->event_day);
    }
}
