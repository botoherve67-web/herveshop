<?php

namespace Tests\Feature;

use App\Models\AffiliateCommission;
use App\Models\Order;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\AffiliateCommissionService;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AffiliateCourierProgramTest extends TestCase
{
    public function test_firebase_signup_activates_a_partner_account_with_a_unique_code(): void
    {
        Notification::fake();
        $verifier = \Mockery::mock(\App\Services\FirebaseIdTokenVerifier::class);
        $verifier->shouldReceive('verify')->once()->andReturn([
            'sub' => 'firebase-partner',
            'email' => 'partner@example.com',
            'email_verified' => true,
            'name' => 'Partenaire',
        ]);
        $this->app->instance(\App\Services\FirebaseIdTokenVerifier::class, $verifier);

        $this->postJson(route('firebase.session'), [
            'id_token' => 'partner-token',
            'account_type' => 'partner',
            'whatsapp' => '+228 90000000',
        ])->assertOk()
            ->assertJsonPath('redirect', route('affiliate.dashboard'));

        $partner = User::where('email', 'partner@example.com')->firstOrFail();
        $this->assertSame('partner', $partner->account_type);
        $this->assertNotEmpty($partner->affiliate_code);
        $this->assertTrue($partner->is_active);
        Notification::assertSentTo($partner, WelcomeNotification::class);
    }

    public function test_commission_uses_discounted_product_total_and_can_be_reinstated_without_duplication(): void
    {
        $partner = User::factory()->create([
            'account_type' => 'partner',
            'affiliate_code' => 'PARTNER123',
        ]);
        $customer = User::factory()->create();
        $order = Order::create([
            'reference' => 'HS-COMMISSION-1',
            'user_id' => $customer->id,
            'affiliate_partner_id' => $partner->id,
            'type' => 'stock',
            'mode_livraison' => 'domicile',
            'sous_total' => 10000,
            'reduction' => 500,
            'frais_livraison' => 1500,
            'total' => 11000,
            'statut_paiement' => 'paye',
            'statut' => 'confirmee',
        ]);
        $service = app(AffiliateCommissionService::class);

        $service->syncForOrder($order);
        $commission = AffiliateCommission::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(9500, $commission->eligible_amount);
        $this->assertSame(760, $commission->commission_amount);

        $order->update(['statut' => 'annulee']);
        $service->syncForOrder($order->fresh());
        $this->assertSame('reversed', $commission->fresh()->status);

        $order->update(['statut' => 'confirmee']);
        $service->syncForOrder($order->fresh());
        $this->assertSame('credited', $commission->fresh()->status);
        $this->assertNull($commission->fresh()->reversed_at);
        $this->assertDatabaseCount('affiliate_commissions', 1);
    }

    public function test_partner_withdrawal_is_limited_by_available_balance_and_reserves_pending_amount(): void
    {
        $partner = User::factory()->create([
            'account_type' => 'partner',
            'affiliate_code' => 'PARTNER456',
        ]);
        $customer = User::factory()->create();
        $order = Order::create([
            'reference' => 'HS-COMMISSION-2',
            'user_id' => $customer->id,
            'affiliate_partner_id' => $partner->id,
            'type' => 'stock',
            'mode_livraison' => 'domicile',
            'sous_total' => 31250,
            'reduction' => 0,
            'frais_livraison' => 0,
            'total' => 31250,
            'statut_paiement' => 'paye',
            'statut' => 'confirmee',
        ]);
        AffiliateCommission::create([
            'partner_id' => $partner->id,
            'order_id' => $order->id,
            'eligible_amount' => 31250,
            'commission_amount' => 2500,
        ]);

        $this->actingAs($partner)
            ->post(route('affiliate.withdrawals.store'), [
                'amount' => 2500,
                'payment_method' => 'flooz',
                'payment_number' => '+228 90000000',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'partner_id' => $partner->id,
            'amount' => 2500,
            'status' => 'pending',
        ]);

        $this->actingAs($partner)
            ->post(route('affiliate.withdrawals.store'), [
                'amount' => 2501,
                'payment_method' => 'tmoney',
                'payment_number' => '+228 91111111',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('affiliate_withdrawal_requests', 1);
    }

    public function test_only_one_courier_can_claim_a_delivery(): void
    {
        $customer = User::factory()->create();
        $firstCourier = User::factory()->create(['account_type' => 'courier']);
        $secondCourier = User::factory()->create(['account_type' => 'courier']);
        $order = Order::create([
            'reference' => 'HS-DELIVERY-1',
            'user_id' => $customer->id,
            'type' => 'stock',
            'mode_livraison' => 'domicile',
            'sous_total' => 5000,
            'reduction' => 0,
            'frais_livraison' => 500,
            'total' => 5500,
            'statut_paiement' => 'paye',
            'statut' => 'confirmee',
        ]);

        $this->actingAs($firstCourier)
            ->post(route('courier.orders.claim', $order))
            ->assertSessionHas('success');
        $this->actingAs($secondCourier)
            ->post(route('courier.orders.claim', $order))
            ->assertSessionHasErrors('delivery');

        $this->assertSame($firstCourier->id, $order->fresh()->courier_id);
    }

    public function test_partner_courier_and_admin_dashboards_render_for_their_roles(): void
    {
        $partner = User::factory()->create([
            'account_type' => 'partner',
            'affiliate_code' => 'PARTNER789',
        ]);
        $courier = User::factory()->create(['account_type' => 'courier']);
        $customer = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($partner)
            ->get(route('affiliate.dashboard'))
            ->assertOk()
            ->assertSee('Liens à partager');

        $this->actingAs($courier)
            ->get(route('courier.dashboard'))
            ->assertOk()
            ->assertSee('Livraisons disponibles');

        $this->actingAs($customer)
            ->get(route('affiliate.dashboard'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.affiliate-withdrawals.index'))
            ->assertOk()
            ->assertSee('Retraits partenaires');
    }
}
