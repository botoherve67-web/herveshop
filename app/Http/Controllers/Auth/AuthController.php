<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyEmailOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->identifiant, FILTER_VALIDATE_EMAIL) ? 'email' : 'whatsapp';

        if (Auth::attempt([$field => $request->identifiant, 'password' => $request->password], $request->boolean('remember'))) {
            if (! Auth::user()->is_active) {
                Auth::logout();

                return back()->withErrors(['identifiant' => 'Ce compte a ete desactive. Contactez notre equipe.'])->onlyInput('identifiant');
            }

            $request->session()->regenerate();

            return redirect()->intended(Auth::user()->is_admin ? route('admin.dashboard') : route('account.dashboard'));
        }

        return back()->withErrors(['identifiant' => 'Identifiants incorrects.'])->onlyInput('identifiant');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'whatsapp' => 'nullable|string|max:30|unique:users,whatsapp',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);
        $this->sendEmailOtp($user);

        return redirect()->route('verification.notice')->with('success', 'Compte cree. Entrez le code recu par mail.');
    }

    public static function sendEmailOtp(User $user): void
    {
        $otp = (string) random_int(100000, 999999);

        $user->forceFill([
            'email_verification_otp' => Hash::make($otp),
            'email_verification_otp_expires_at' => now()->addMinutes(10),
            'email_verification_otp_sent_at' => now(),
            'email_verification_otp_attempts' => 0,
            'email_verification_otp_locked_until' => null,
        ])->save();

        try {
            $user->notify(new VerifyEmailOtpNotification($otp));
        } catch (\Throwable $exception) {
            Log::error('Code OTP email non envoye.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
