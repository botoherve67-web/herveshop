<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ProfileUpdatedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    public function test_registration_logs_in_the_user_and_sends_a_welcome_notification_without_email_otp(): void
    {
        Notification::fake();

        $this->post('/inscription', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'whatsapp' => '+228 90000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('account.dashboard'))
            ->assertSessionHas('success');

        $user = User::where('email', 'jean@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_existing_unverified_user_can_log_in_without_email_otp(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'jean@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/connexion', [
            'identifiant' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_profile_update_sends_a_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('account.update'), [
            'name' => 'Jean Dupont',
            'whatsapp' => '+228 90000000',
        ])->assertSessionHas('success');

        Notification::assertSentTo($user, ProfileUpdatedNotification::class);
    }
}
