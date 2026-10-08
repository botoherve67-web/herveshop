<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawalRequest;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AffiliateWithdrawalNotification;
use App\Services\AffiliateBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AffiliateController extends Controller
{
    public function dashboard(AffiliateBalanceService $balanceService)
    {
        $partner = Auth::user();
        abort_unless($partner->account_type === 'partner', 403);

        $balances = $balanceService->totals($partner);
        $products = Product::with('images')
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->where('stock', '>', 0)
                    ->orWhere('type', 'precommande')
                    ->orWhere('bascule_auto_precommande', true);
            })
            ->latest()
            ->paginate(20);
        $commissions = AffiliateCommission::with('order')
            ->where('partner_id', $partner->id)
            ->latest()
            ->take(10)
            ->get();
        $withdrawals = AffiliateWithdrawalRequest::where('partner_id', $partner->id)
            ->latest()
            ->take(10)
            ->get();

        return view('affiliate.dashboard', compact('partner', 'balances', 'products', 'commissions', 'withdrawals'));
    }

    public function requestWithdrawal(Request $request, AffiliateBalanceService $balanceService)
    {
        $partner = Auth::user();
        abort_unless($partner->account_type === 'partner', 403);

        $data = $request->validate([
            'amount' => 'required|integer|min:2500',
            'payment_method' => 'required|in:flooz,tmoney',
            'payment_number' => 'required|string|max:30',
        ]);

        $withdrawal = DB::transaction(function () use ($partner, $data, $balanceService): AffiliateWithdrawalRequest {
            $lockedPartner = User::whereKey($partner->id)->lockForUpdate()->firstOrFail();
            $available = $balanceService->totals($lockedPartner)['available'];

            if ($data['amount'] > $available) {
                throw ValidationException::withMessages([
                    'amount' => 'Le montant demandé dépasse votre solde disponible de '.number_format($available, 0, ',', ' ').' FCFA.',
                ]);
            }

            return AffiliateWithdrawalRequest::create([
                'partner_id' => $lockedPartner->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_number' => trim($data['payment_number']),
            ]);
        });

        User::where('is_admin', true)->each(function (User $admin) use ($withdrawal): void {
            try {
                $admin->notify(new AffiliateWithdrawalNotification($withdrawal));
            } catch (\Throwable $exception) {
                Log::error('Notification de retrait partenaire non envoyée.', [
                    'withdrawal_id' => $withdrawal->id,
                    'admin_id' => $admin->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        });

        return back()->with('success', 'Votre demande de retrait a été envoyée à l’administration.');
    }
}
