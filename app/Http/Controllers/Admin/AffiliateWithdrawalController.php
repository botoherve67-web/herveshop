<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdrawalRequest;
use App\Models\User;
use App\Services\AffiliateBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AffiliateWithdrawalController extends Controller
{
    public function index()
    {
        $requests = AffiliateWithdrawalRequest::with('partner')
            ->latest()
            ->paginate(25);
        $pendingCount = AffiliateWithdrawalRequest::where('status', 'pending')->count();

        return view('admin.affiliate-withdrawals.index', compact('requests', 'pendingCount'));
    }

    public function review(Request $request, AffiliateWithdrawalRequest $withdrawal, AffiliateBalanceService $balanceService)
    {
        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $withdrawal, $balanceService): void {
            $lockedWithdrawal = AffiliateWithdrawalRequest::whereKey($withdrawal->id)
                ->lockForUpdate()
                ->firstOrFail();
            $partner = User::whereKey($lockedWithdrawal->partner_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedWithdrawal->status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'Cette demande a déjà été traitée.',
                ]);
            }

            if ($data['decision'] === 'approve') {
                $availableExcludingThisRequest = $balanceService->totals($partner)['available']
                    + (int) $lockedWithdrawal->amount;

                if ($lockedWithdrawal->amount > $availableExcludingThisRequest) {
                    throw ValidationException::withMessages([
                        'decision' => 'Le solde disponible ne couvre plus cette demande. Vérifiez le compte partenaire.',
                    ]);
                }
            }

            $lockedWithdrawal->update([
                'status' => $data['decision'] === 'approve' ? 'approved' : 'rejected',
                'reviewed_by' => Auth::id(),
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'La demande de retrait a été traitée.');
    }
}
