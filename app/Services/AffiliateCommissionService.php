<?php

namespace App\Services;

use App\Models\AffiliateCommission;
use App\Models\Order;

class AffiliateCommissionService
{
    public function syncForOrder(Order $order): void
    {
        $commission = AffiliateCommission::where('order_id', $order->id)->lockForUpdate()->first();

        if ($order->statut_paiement !== 'paye' || $order->statut === 'annulee') {
            if ($commission && $commission->status !== 'reversed') {
                $commission->update([
                    'status' => 'reversed',
                    'reversed_at' => now(),
                ]);
            }

            return;
        }

        if ($commission) {
            if ($commission->status === 'reversed') {
                $commission->update([
                    'status' => 'credited',
                    'reversed_at' => null,
                ]);
            }

            return;
        }

        if (! $order->affiliate_partner_id) {
            return;
        }

        $eligibleAmount = max(0, (int) $order->sous_total - (int) $order->reduction);
        $commissionAmount = intdiv($eligibleAmount * 8, 100);

        if ($commissionAmount === 0) {
            return;
        }

        AffiliateCommission::create([
            'partner_id' => $order->affiliate_partner_id,
            'order_id' => $order->id,
            'eligible_amount' => $eligibleAmount,
            'commission_amount' => $commissionAmount,
        ]);
    }
}
