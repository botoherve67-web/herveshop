<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use App\Notifications\WelcomeNotification;
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

            if (! Auth::user()->email_verified_at) {
                $request->session()->put('pending_email', Auth::user()->email);
                Auth::logout();

                return redirect()->route('email.verify')->with('status', 'Confirmez votre adresse e-mail avec le code reçu. Vous pouvez en demander un nouveau si nécessaire.');
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

        $request->session()->put('pending_email', $user->email);

        try {
            $this->sendEmailVerificationOtp($user);
        } catch (\Throwable $exception) {
            Log::error('Code OTP d’inscription non envoyé.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('email.verify')->withErrors([
                'code' => 'Votre compte est créé, mais le code n’a pas pu être envoyé. Réessayez avec le bouton de renvoi.',
            ]);
        }

        return redirect()->route('email.verify')->with('status', 'Un code de vérification a été envoyé à votre adresse e-mail.');
    }

    public function showEmailVerification(Request $request)
    {
        $user = $this->pendingVerificationUser($request);

        if (! $user) {
            return redirect()->route('register')->withErrors([
                'email' => 'Créez un compte pour vérifier votre adresse e-mail.',
            ]);
        }

        return view('auth.verify-email', compact('user'));
    }

    public function verifyEmail(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = $this->pendingVerificationUser($request);

        if (! $user) {
            return redirect()->route('register')->withErrors([
                'email' => 'Créez un compte pour vérifier votre adresse e-mail.',
            ]);
        }

        if ($user->email_verification_otp_locked_until?->isFuture()) {
            return back()->withErrors(['code' => 'Trop de tentatives. Demandez un nouveau code après le délai de sécurité.']);
        }

        if (! $user->email_verification_otp
            || ! $user->email_verification_otp_expires_at
            || $user->email_verification_otp_expires_at->isPast()) {
            return back()->withErrors(['code' => 'Ce code est expiré ou indisponible. Demandez-en un nouveau.']);
        }

        if (! Hash::check($request->code, $user->email_verification_otp)) {
            $attempts = (int) $user->email_verification_otp_attempts + 1;
            $user->forceFill([
                'email_verification_otp_attempts' => $attempts,
                'email_verification_otp_locked_until' => $attempts >= 5 ? now()->addMinutes(15) : null,
            ])->save();

            return back()->withErrors([
                'code' => $attempts >= 5
                    ? 'Trop de tentatives. Vous pourrez demander un nouveau code dans 15 minutes.'
                    : 'Le code saisi est incorrect.',
            ]);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_otp' => null,
            'email_verification_otp_expires_at' => null,
            'email_verification_otp_sent_at' => null,
            'email_verification_otp_attempts' => 0,
            'email_verification_otp_locked_until' => null,
        ])->save();

        $request->session()->forget('pending_email');
        Auth::login($user);
        $request->session()->regenerate();

        try {
            $user->notify(new WelcomeNotification);
        } catch (\Throwable $exception) {
            Log::error('E-mail de bienvenue non envoyé.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()->intended(route('account.dashboard'))->with('success', 'Votre adresse e-mail est vérifiée. Bienvenue sur HerveShop.');
    }

    public function resendEmailVerification(Request $request)
    {
        $user = $this->pendingVerificationUser($request);

        if (! $user) {
            return redirect()->route('register')->withErrors([
                'email' => 'Créez un compte pour vérifier votre adresse e-mail.',
            ]);
        }

        if ($user->email_verification_otp_locked_until?->isFuture()) {
            return back()->withErrors(['code' => 'Trop de tentatives. Patientez avant de demander un nouveau code.']);
        }

        if ($user->email_verification_otp_sent_at?->gt(now()->subSeconds(60))) {
            return back()->withErrors(['code' => 'Veuillez patienter une minute avant de demander un autre code.']);
        }

        try {
            $this->sendEmailVerificationOtp($user);
        } catch (\Throwable $exception) {
            Log::error('Renvoi du code OTP non envoyé.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors(['code' => 'Le code n’a pas pu être envoyé. Réessayez plus tard.']);
        }

        return back()->with('status', 'Un nouveau code de vérification a été envoyé.');
    }

    private function pendingVerificationUser(Request $request): ?User
    {
        $email = $request->session()->get('pending_email');

        if (! $email) {
            return null;
        }

        $user = User::where('email', $email)->first();

        if (! $user || $user->email_verified_at) {
            $request->session()->forget('pending_email');

            return null;
        }

        return $user;
    }

    private function sendEmailVerificationOtp(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'email_verification_otp' => Hash::make($code),
            'email_verification_otp_expires_at' => now()->addMinutes(10),
            'email_verification_otp_sent_at' => now(),
            'email_verification_otp_attempts' => 0,
            'email_verification_otp_locked_until' => null,
        ])->save();

        try {
            $user->notify(new EmailVerificationOtpNotification($code));
        } catch (\Throwable $exception) {
            $user->forceFill([
                'email_verification_otp' => null,
                'email_verification_otp_expires_at' => null,
                'email_verification_otp_sent_at' => null,
            ])->save();

            throw $exception;
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
