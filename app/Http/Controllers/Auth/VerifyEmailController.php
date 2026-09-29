<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account.dashboard');
        }

        $request->fulfill();

        return redirect()->route('account.dashboard')->with('success', 'Votre adresse e-mail est confirmee.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.dashboard');
        }

        $lockedUntil = $user->email_verification_otp_locked_until;

        if ($lockedUntil && now()->lessThan($lockedUntil)) {
            return back()
                ->withErrors(['otp' => 'Trop de codes incorrects. Reessayez dans '.ceil(now()->diffInSeconds($lockedUntil) / 60).' minute(s).'])
                ->onlyInput('otp');
        }

        $expiresAt = $user->email_verification_otp_expires_at;
        $isExpired = ! $expiresAt || now()->greaterThan($expiresAt);

        if ($isExpired) {
            return back()
                ->withErrors(['otp' => 'Code expire. Demandez un nouveau code.'])
                ->onlyInput('otp');
        }

        $isInvalid = ! $user->email_verification_otp || ! Hash::check($request->otp, $user->email_verification_otp);

        if ($isInvalid) {
            $attempts = (int) $user->email_verification_otp_attempts + 1;
            $lockedUntil = $attempts >= 5 ? now()->addMinutes(15) : null;

            $user->forceFill([
                'email_verification_otp_attempts' => $attempts,
                'email_verification_otp_locked_until' => $lockedUntil,
            ])->save();

            if ($lockedUntil) {
                return back()
                    ->withErrors(['otp' => '5 codes incorrects. Verification bloquee pendant 15 minutes.'])
                    ->onlyInput('otp');
            }

            return back()
                ->withErrors(['otp' => 'Code incorrect. Il reste '.(5 - $attempts).' essai(s).'])
                ->onlyInput('otp');
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_otp' => null,
            'email_verification_otp_expires_at' => null,
            'email_verification_otp_sent_at' => null,
            'email_verification_otp_attempts' => 0,
            'email_verification_otp_locked_until' => null,
        ])->save();

        try {
            $user->notify(new WelcomeNotification);
        } catch (\Throwable $exception) {
            Log::error('Email de bienvenue non envoye.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('account.dashboard')->with('success', 'Votre adresse e-mail est confirmee.');
    }

    public function resendOtp(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account.dashboard');
        }

        $user = $request->user();
        $lockedUntil = $user->email_verification_otp_locked_until;

        if ($lockedUntil && now()->lessThan($lockedUntil)) {
            return back()->withErrors(['otp' => 'Verification bloquee. Reessayez dans '.ceil(now()->diffInSeconds($lockedUntil) / 60).' minute(s).']);
        }

        $availableAt = $user->email_verification_otp_sent_at?->copy()->addSeconds(60);

        if ($availableAt && now()->lessThan($availableAt)) {
            $seconds = now()->diffInSeconds($availableAt);

            return back()->withErrors(['otp' => 'Patientez '.$seconds.' secondes avant renvoi.']);
        }

        AuthController::sendEmailOtp($user);

        return back()->with('success', 'Nouveau code envoye par mail.');
    }

    public function updateEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.dashboard');
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->forceFill([
            'email' => $request->email,
            'email_verified_at' => null,
        ])->save();

        AuthController::sendEmailOtp($user);

        return back()->with('success', 'Adresse e-mail modifiee. Nouveau code envoye.');
    }
}
