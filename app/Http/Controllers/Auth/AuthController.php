<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseIdTokenVerifier;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function firebaseSession(Request $request, FirebaseIdTokenVerifier $verifier)
    {
        $data = $request->validate([
            'id_token' => ['required', 'string', 'max:10000'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
        ]);

        try {
            $claims = $verifier->verify($data['id_token']);
        } catch (AuthenticationException) {
            return response()->json([
                'message' => 'Le jeton Firebase est invalide ou a expiré. Reconnectez-vous.',
            ], 401);
        }

        $firebaseUid = $claims['sub'];
        $email = mb_strtolower(trim($claims['email']));

        if (DB::table('deleted_firebase_uids')->where('firebase_uid', $firebaseUid)->exists()) {
            return response()->json([
                'message' => 'Ce compte Firebase a été supprimé. Contactez notre équipe.',
            ], 403);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();
        $isNewUser = false;

        if (! $user) {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            $emailVerified = ($claims['email_verified'] ?? false) === true;

            if ($user && ! $emailVerified) {
                return response()->json([
                    'code' => 'email_verification_required',
                    'message' => 'Vérifiez votre adresse e-mail pour rattacher ce compte existant.',
                ], 409);
            }

            if ($user && $user->firebase_uid !== null) {
                return response()->json([
                    'message' => 'Cette adresse e-mail est déjà associée à un autre compte Firebase.',
                ], 409);
            }

            if (! $user) {
                if (! empty($data['whatsapp']) && User::where('whatsapp', $data['whatsapp'])->exists()) {
                    return response()->json([
                        'message' => 'Ce numéro WhatsApp est déjà associé à un compte.',
                    ], 422);
                }

                $name = trim((string) ($claims['name'] ?? ''));
                $user = User::create([
                    'name' => $name !== '' ? mb_substr($name, 0, 255) : Str::before($email, '@'),
                    'email' => $email,
                    'whatsapp' => $data['whatsapp'] ?? null,
                    'password' => Str::random(64),
                    'firebase_uid' => $firebaseUid,
                    'email_verified_at' => $emailVerified ? now() : null,
                    'is_active' => true,
                ]);
                $isNewUser = true;
            } else {
                $user->forceFill([
                    'firebase_uid' => $firebaseUid,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Ce compte a été désactivé. Contactez notre équipe.',
            ], 403);
        }

        if (($claims['email_verified'] ?? false) === true && $user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($isNewUser) {
            try {
                $user->notify(new WelcomeNotification);
            } catch (\Throwable $exception) {
                Log::error('E-mail de bienvenue non envoyé.', [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'redirect' => route($user->is_admin ? 'admin.dashboard' : 'account.dashboard'),
        ]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
