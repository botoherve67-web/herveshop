<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Config;

class ApiTokenService
{
    private static function getSecret(): string
    {
        $key = Config::get('app.key');

        if (is_string($key) && str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7), true);
        }

        if (! is_string($key) || strlen($key) < 32) {
            throw new \RuntimeException('APP_KEY must contain at least 32 bytes to sign API tokens.');
        }

        return $key;
    }

    public static function createToken(User $user, int $lifetimeDays = 30): string
    {
        $payload = [
            'iss' => Config::get('app.url', 'http://localhost:8000'),
            'sub' => $user->id,
            'email' => $user->email,
            'is_admin' => (bool) $user->is_admin,
            'iat' => time(),
            'exp' => time() + ($lifetimeDays * 86400),
        ];

        return JWT::encode($payload, self::getSecret(), 'HS256');
    }

    public static function validateToken(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecret(), 'HS256'));
            if (! isset($decoded->sub)) {
                return null;
            }
            return User::where('id', $decoded->sub)->where('is_active', true)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
