<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Auth;

class PasswordResetRequestController extends Controller
{
    public function index()
    {
        $requests = PasswordResetRequest::with('user')->latest()->paginate(20);

        return view('admin.password-requests.index', compact('requests'));
    }

    public function approve(PasswordResetRequest $passwordResetRequest)
    {
        $passwordResetRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        return back()->with('success', 'Demande approuvee pour 24 heures.');
    }

    public function reject(PasswordResetRequest $passwordResetRequest)
    {
        $passwordResetRequest->update(['status' => 'rejected']);

        return back()->with('success', 'Demande refusee.');
    }
}
