<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeCustomerAccountsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_previews_customer_deletion_without_deleting_accounts(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->artisan('users:purge-customers')
            ->expectsOutput('Dry run: 1 non-admin account(s) would be deleted. Admin accounts will be preserved.')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_command_deletes_customers_and_cascaded_records_but_preserves_admins(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::create([
            'reference' => 'TEST-PURGE-001',
            'user_id' => $customer->id,
            'mode_livraison' => 'domicile',
            'sous_total' => 1000,
            'total' => 1000,
        ]);

        $this->artisan('users:purge-customers', ['--force' => true])
            ->expectsOutput('Deleted 1 non-admin account(s). Admin accounts were preserved.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
