<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            PasswordResetRequest::where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'cancelled']);
            PasswordResetRequest::create(['user_id' => $user->id]);
        }

        return back()->with('success', 'Demande envoyée. Attendez validation admin.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function showManualResetForm(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->email, 'manual' => true]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        return $this->completeApprovedReset($request);
    }

    public function resetManually(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        return $this->completeApprovedReset($request);
    }

    private function completeApprovedReset(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $approved = $user?->passwordResetRequests()
            ->where('status', 'approved')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($approved) {
            $user->forceFill(['password' => Hash::make($request->password), 'remember_token' => null])->save();
            $approved->update(['used_at' => now()]);

            return redirect()->route('login')->with('success', 'Mot de passe modifie. Connectez-vous.');
        }

        return back()->withErrors(['email' => 'Demande non approuvee ou expiree.'])->onlyInput('email');
    }
}
