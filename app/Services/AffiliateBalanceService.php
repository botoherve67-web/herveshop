<?php

namespace App\Services;

use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawalRequest;
use App\Models\User;

class AffiliateBalanceService
{
    public function totals(User $partner): array
    {
        $earned = AffiliateCommission::where('partner_id', $partner->id)
            ->where('status', 'credited')
            ->sum('commission_amount');

        $approved = AffiliateWithdrawalRequest::where('partner_id', $partner->id)
            ->where('status', 'approved')
            ->sum('amount');

        $reserved = AffiliateWithdrawalRequest::where('partner_id', $partner->id)
            ->where('status', 'pending')
            ->sum('amount');

        return [
            'earned' => (int) $earned,
            'withdrawn' => (int) $approved,
            'reserved' => (int) $reserved,
            'available' => max(0, (int) $earned - (int) $approved - (int) $reserved),
        ];
    }

    public function availableForApproval(User $partner): int
    {
        $earned = AffiliateCommission::where('partner_id', $partner->id)
            ->where('status', 'credited')
            ->sum('commission_amount');

        $approved = AffiliateWithdrawalRequest::where('partner_id', $partner->id)
            ->where('status', 'approved')
            ->sum('amount');

        return max(0, (int) $earned - (int) $approved);
    }
}
