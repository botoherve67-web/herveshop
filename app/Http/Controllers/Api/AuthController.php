<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService;
use App\Services\FirebaseIdTokenVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [mb_strtolower(trim($credentials['email']))])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects. Veuillez vérifier votre adresse e-mail ou mot de passe.',
            ], 422);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Ce compte a été désactivé.',
            ], 403);
        }

        $token = ApiTokenService::createToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'whatsapp' => 'nullable|string|max:30|unique:users,whatsapp',
            'address' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'whatsapp' => $data['whatsapp'] ?? null,
            'address' => $data['address'] ?? null,
            'zone' => $data['zone'] ?? 'lome_centre',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $token = ApiTokenService::createToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Votre compte a été créé avec succès.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function firebaseAuth(Request $request, FirebaseIdTokenVerifier $verifier): JsonResponse
    {
        $data = $request->validate([
            'id_token' => 'required|string',
            'whatsapp' => 'nullable|string|max:30',
            'name' => 'nullable|string|max:255',
            'account_type' => 'sometimes|in:customer,courier,partner',
        ]);

        try {
            $claims = $verifier->verify($data['id_token']);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jeton Firebase invalide ou expiré.',
            ], 401);
        }

        $firebaseUid = $claims['sub'];
        $email = mb_strtolower(trim($claims['email'] ?? ''));
        $emailVerified = ($claims['email_verified'] ?? false) === true;

        if (DB::table('deleted_firebase_uids')->where('firebase_uid', $firebaseUid)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce compte Firebase a été supprimé. Contactez notre équipe.',
            ], 403);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();

        if (! $user && $email !== '') {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user && ! $emailVerified) {
                return response()->json([
                    'success' => false,
                    'code' => 'email_verification_required',
                    'message' => 'Vérifiez votre adresse e-mail pour rattacher ce compte existant.',
                ], 409);
            }

            if ($user && $user->firebase_uid !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette adresse e-mail est déjà associée à un autre compte Firebase.',
                ], 409);
            }
        }

        if (! $user) {
            $name = trim((string) ($data['name'] ?? $claims['name'] ?? ''));
            $accountType = $data['account_type'] ?? 'customer';

            if (in_array($accountType, ['courier', 'partner'], true) && empty($data['whatsapp'])) {
                throw ValidationException::withMessages([
                    'whatsapp' => 'Un numéro WhatsApp est obligatoire pour un compte livreur ou partenaire.',
                ]);
            }

            if (! empty($data['whatsapp']) && User::where('whatsapp', $data['whatsapp'])->exists()) {
                throw ValidationException::withMessages([
                    'whatsapp' => 'Ce numéro WhatsApp est déjà associé à un compte.',
                ]);
            }

            $user = User::create([
                'name' => $name !== '' ? mb_substr($name, 0, 255) : Str::before($email, '@'),
                'email' => $email,
                'whatsapp' => $data['whatsapp'] ?? null,
                'password' => Hash::make(Str::random(32)),
                'firebase_uid' => $firebaseUid,
                'account_type' => $accountType,
                'affiliate_code' => $accountType === 'partner'
                    ? $this->newAffiliateCode()
                    : null,
                'email_verified_at' => $emailVerified ? now() : null,
                'is_active' => true,
            ]);
        } else {
            if (! $user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte est désactivé.',
                ], 403);
            }

            if ($user->firebase_uid === null) {
                $user->forceFill([
                    'firebase_uid' => $firebaseUid,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        }

        $token = ApiTokenService::createToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Connexion Firebase réussie.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => $this->formatUser($user),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'whatsapp' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:100',
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour.',
            'user' => $this->formatUser($user),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp' => $user->whatsapp,
            'address' => $user->address,
            'zone' => $user->zone,
            'is_admin' => (bool) $user->is_admin,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    private function newAffiliateCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (User::where('affiliate_code', $code)->exists());

        return $code;
    }
}
