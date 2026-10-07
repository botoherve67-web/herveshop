<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ProfileUpdatedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    public function test_registration_sends_a_welcome_notification(): void
    {
        Notification::fake();

        $this->post('/inscription', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'whatsapp' => '+228 90000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('account.dashboard'));

        $user = User::where('email', 'jean@example.com')->firstOrFail();
        Notification::assertSentTo($user, WelcomeNotification::class);
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
