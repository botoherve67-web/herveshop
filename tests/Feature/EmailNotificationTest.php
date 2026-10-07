<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use App\Notifications\ProfileUpdatedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    public function test_registration_sends_an_email_otp_without_authenticating_the_user(): void
    {
        Notification::fake();

        $this->post('/inscription', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'whatsapp' => '+228 90000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('email.verify'))
            ->assertSessionHas('pending_email', 'jean@example.com');

        $user = User::where('email', 'jean@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertGuest();
        Notification::assertSentTo($user, EmailVerificationOtpNotification::class);
        Notification::assertNotSentTo($user, WelcomeNotification::class);
    }

    public function test_user_is_logged_in_and_welcomed_after_entering_the_correct_otp(): void
    {
        Notification::fake();

        $this->post('/inscription', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('email.verify'));

        $user = User::where('email', 'jean@example.com')->firstOrFail();
        $code = null;
        Notification::assertSentTo($user, EmailVerificationOtpNotification::class, function (EmailVerificationOtpNotification $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $this->post(route('email.verify.submit'), ['code' => $code])
            ->assertRedirect(route('account.dashboard'))
            ->assertSessionHas('success');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
        Notification::assertSentTo($user, WelcomeNotification::class);
        $this->assertNull($user->fresh()->email_verification_otp);
    }

    public function test_incorrect_otp_is_limited_and_locks_verification_after_five_attempts(): void
    {
        Notification::fake();

        $this->post('/inscription', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'jean@example.com')->firstOrFail();
        $sentCode = null;
        Notification::assertSentTo($user, EmailVerificationOtpNotification::class, function (EmailVerificationOtpNotification $notification) use (&$sentCode) {
            $sentCode = $notification->code;

            return true;
        });
        $incorrectCode = $sentCode === '000000' ? '111111' : '000000';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('email.verify.submit'), ['code' => $incorrectCode]);
        }

        $user->refresh();
        $this->assertSame(5, $user->email_verification_otp_attempts);
        $this->assertTrue($user->email_verification_otp_locked_until->isFuture());

        $this->post(route('email.verify.submit'), ['code' => '123456'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_expired_otp_cannot_verify_the_email(): void
    {
        Notification::fake();

        $this->post('/inscription', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'jean@example.com')->firstOrFail();
        $code = null;
        Notification::assertSentTo($user, EmailVerificationOtpNotification::class, function (EmailVerificationOtpNotification $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });
        $user->forceFill(['email_verification_otp_expires_at' => now()->subMinute()])->save();

        $this->post(route('email.verify.submit'), ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
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
