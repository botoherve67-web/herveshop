<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\AdminOrderNotification;
use App\Notifications\OrderUpdateNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderCustomerActionsNotificationTest extends TestCase
{
    public function test_submitting_payment_proof_notifies_admin_and_confirms_receipt_to_customer(): void
    {
        Notification::fake();
        Storage::fake('private');

        $customer = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::create([
            'reference' => 'HS-TEST1234',
            'user_id' => $customer->id,
            'type' => 'stock',
            'mode_livraison' => 'domicile',
            'frais_livraison' => 1000,
            'sous_total' => 10000,
            'total' => 11000,
            'moyen_paiement' => 'flooz',
            'statut_paiement' => 'en_attente',
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($customer)->post(route('orders.payment-proof', $order), [
            'transaction_id' => 'TXN-12345',
            'preuve_paiement' => UploadedFile::fake()->createWithContent(
                'preuve.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
            ),
        ]);

        $response->assertSessionHas('success');
        $this->assertNotNull($order->fresh()->preuve_paiement_path);
        $this->assertSame('TXN-12345', $order->fresh()->transaction_id);

        Notification::assertSentTo(
            $admin,
            AdminOrderNotification::class,
            fn (AdminOrderNotification $notification): bool => $notification->event === 'preuve_paiement'
                && $notification->order->is($order)
        );
        Notification::assertSentTo(
            $customer,
            OrderUpdateNotification::class,
            fn (OrderUpdateNotification $notification): bool => $notification->event === 'preuve_paiement'
                && $notification->order->is($order)
        );
    }
}
