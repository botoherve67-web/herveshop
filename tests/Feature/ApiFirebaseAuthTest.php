<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FirebaseIdTokenVerifier;
use Tests\TestCase;

class ApiFirebaseAuthTest extends TestCase
{
    public function test_mobile_home_catalog_endpoint_is_available(): void
    {
        $this->getJson('/api/home')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'categories',
                'featured_products',
                'preorder_products',
                'popular_products',
                'recent_reviews',
                'banners',
            ]);
    }

    public function test_firebase_registration_creates_a_laravel_api_session(): void
    {
        $this->mockFirebaseToken([
            'sub' => 'firebase-business-user',
            'email' => 'business@example.com',
            'name' => 'Business User',
        ]);

        $response = $this->postJson('/api/auth/firebase', [
            'id_token' => 'firebase-id-token',
            'name' => 'Business User',
            'whatsapp' => '+228 90000000',
            'account_type' => 'partner',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.name', 'Business User')
            ->assertJsonPath('user.email', 'business@example.com');

        $user = User::where('firebase_uid', 'firebase-business-user')->firstOrFail();
        $this->assertSame('partner', $user->account_type);
        $this->assertNotEmpty($user->affiliate_code);
        $this->assertNotEmpty($response->json('token'));

        $this->withToken($response->json('token'))
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_firebase_login_does_not_change_an_existing_account_type(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'account_type' => 'customer',
        ]);
        $this->mockFirebaseToken([
            'sub' => 'firebase-existing-customer',
            'email' => 'customer@example.com',
            'email_verified' => true,
        ]);

        $this->postJson('/api/auth/firebase', [
            'id_token' => 'firebase-id-token',
            'account_type' => 'partner',
        ])->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->assertSame('customer', $user->fresh()->account_type);
        $this->assertNull($user->fresh()->affiliate_code);
    }

    public function test_unverified_firebase_email_cannot_link_an_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $this->mockFirebaseToken([
            'sub' => 'firebase-unverified-customer',
            'email' => 'customer@example.com',
            'email_verified' => false,
        ]);

        $this->postJson('/api/auth/firebase', [
            'id_token' => 'firebase-id-token',
        ])->assertStatus(409)
            ->assertJsonPath('code', 'email_verification_required');

        $this->assertNull($user->fresh()->firebase_uid);
    }

    public function test_firebase_registration_rejects_unsupported_account_types(): void
    {
        $this->postJson('/api/auth/firebase', [
            'id_token' => 'firebase-id-token',
            'account_type' => 'admin',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('users', ['email' => 'user@example.com']);
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
