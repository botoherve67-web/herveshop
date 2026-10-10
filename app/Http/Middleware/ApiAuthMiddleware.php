<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\ApiTokenService;
use App\Services\FirebaseIdTokenVerifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Jeton d\'authentification manquant.',
            ], 401);
        }

        $token = substr($header, 7);

        // 1. Essayer avec le service de jeton d'API HerveShop
        $user = ApiTokenService::validateToken($token);

        // 2. Si non reconnu, essayer comme jeton d'ID Firebase
        if (! $user) {
            try {
                $verifier = app(FirebaseIdTokenVerifier::class);
                $claims = $verifier->verify($token);
                $firebaseUid = $claims['sub'] ?? null;
                if ($firebaseUid) {
                    $user = User::where('firebase_uid', $firebaseUid)->where('is_active', true)->first();
                    if (! $user && ($claims['email_verified'] ?? false) === true && ! empty($claims['email'])) {
                        $user = User::where('email', mb_strtolower(trim($claims['email'])))->where('is_active', true)->first();
                    }
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Session expirée ou jeton d\'authentification invalide.',
            ], 401);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
