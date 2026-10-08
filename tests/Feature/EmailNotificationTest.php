<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FirebaseIdTokenVerifier;
use App\Notifications\ProfileUpdatedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    public function test_firebase_registration_creates_a_laravel_session_and_sends_a_welcome_notification(): void
    {
        Notification::fake();
        $this->mockFirebaseToken([
            'sub' => 'firebase-new-user',
            'email' => 'jean@example.com',
            'email_verified' => false,
            'name' => 'Jean Dupont',
        ]);

        $this->postJson(route('firebase.session'), [
            'id_token' => 'firebase-id-token',
            'whatsapp' => '+228 90000000',
        ])->assertOk()
            ->assertJsonPath('redirect', route('account.dashboard'));

        $user = User::where('email', 'jean@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('firebase-new-user', $user->firebase_uid);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_verified_firebase_identity_is_linked_to_an_existing_admin_account(): void
    {
        $user = User::factory()->create([
            'email' => 'jean@example.com',
            'is_admin' => true,
        ]);
        $this->mockFirebaseToken([
            'sub' => 'firebase-admin-user',
            'email' => 'jean@example.com',
            'email_verified' => true,
            'name' => 'A different display name',
        ]);

        $this->postJson(route('firebase.session'), [
            'id_token' => 'firebase-id-token',
        ])->assertOk()
            ->assertJsonPath('redirect', route('admin.dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('firebase-admin-user', $user->fresh()->firebase_uid);
        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_unverified_firebase_email_cannot_claim_an_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'jean@example.com']);
        $this->mockFirebaseToken([
            'sub' => 'firebase-unverified-user',
            'email' => 'jean@example.com',
            'email_verified' => false,
        ]);

        $this->postJson(route('firebase.session'), [
            'id_token' => 'firebase-id-token',
        ])->assertStatus(409)
            ->assertJsonPath('code', 'email_verification_required');

        $this->assertGuest();
        $this->assertNull($user->fresh()->firebase_uid);
    }

    public function test_deleted_firebase_uid_cannot_recreate_an_application_account(): void
    {
        DB::table('deleted_firebase_uids')->insert([
            'firebase_uid' => 'firebase-deleted-user',
            'deleted_at' => now(),
        ]);
        $this->mockFirebaseToken([
            'sub' => 'firebase-deleted-user',
            'email' => 'deleted@example.com',
            'email_verified' => true,
        ]);

        $this->postJson(route('firebase.session'), [
            'id_token' => 'firebase-id-token',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);
        $this->assertGuest();
    }

    public function test_admin_deleting_a_firebase_user_records_the_uid_block(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['firebase_uid' => 'firebase-customer']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $customer))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertDatabaseHas('deleted_firebase_uids', [
            'firebase_uid' => 'firebase-customer',
        ]);
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

    private function mockFirebaseToken(array $claims): void
    {
        $verifier = \Mockery::mock(FirebaseIdTokenVerifier::class);
        $verifier->shouldReceive('verify')
            ->once()
            ->with('firebase-id-token')
            ->andReturn($claims);

        $this->app->instance(FirebaseIdTokenVerifier::class, $verifier);
    }
}
