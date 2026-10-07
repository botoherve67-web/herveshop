<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Tests\TestCase;

class AdminDeleteUserTest extends TestCase
{
    public function test_super_admin_can_delete_a_customer_and_their_orders(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'admin_role' => 'super_admin']);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = Order::create([
            'reference' => 'TEST-DELETE-USER-001',
            'user_id' => $customer->id,
            'mode_livraison' => 'domicile',
            'sous_total' => 1000,
            'total' => 1000,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $customer))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_super_admin_cannot_delete_an_administrator(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'admin_role' => 'super_admin']);
        $otherAdmin = User::factory()->create(['is_admin' => true, 'admin_role' => 'orders']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $otherAdmin))
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_non_super_admin_cannot_delete_customers(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'admin_role' => 'orders']);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $customer))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }
}
