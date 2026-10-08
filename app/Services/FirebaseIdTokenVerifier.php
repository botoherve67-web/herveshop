<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;
use DomainException;
use InvalidArgumentException;

class FirebaseIdTokenVerifier
{
    private const CERTIFICATES_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    /**
     * @return array<string, mixed>
     */
    public function verify(string $idToken): array
    {
        $projectId = config('services.firebase.project_id');

        if (! is_string($projectId) || $projectId === '') {
            throw new \RuntimeException('FIREBASE_PROJECT_ID is not configured.');
        }

        $certificates = Cache::remember(
            'firebase_auth_public_certificates',
            now()->addHour(),
            fn (): array => $this->fetchCertificates(),
        );
        $keys = [];

        foreach ($certificates as $keyId => $certificate) {
            if (is_string($keyId) && is_string($certificate)) {
                $keys[$keyId] = new Key($certificate, 'RS256');
            }
        }

        if ($keys === []) {
            throw new \RuntimeException('Firebase public signing certificates are unavailable.');
        }

        try {
            $claims = (array) JWT::decode($idToken, $keys);
        } catch (UnexpectedValueException|DomainException|InvalidArgumentException) {
            throw new AuthenticationException('Le jeton Firebase est invalide.');
        }

        $now = time();
        $uid = $claims['sub'] ?? null;
        $issuedAt = $claims['iat'] ?? null;
        $authTime = $claims['auth_time'] ?? null;

        if (
            ($claims['aud'] ?? null) !== $projectId
            || ($claims['iss'] ?? null) !== "https://securetoken.google.com/{$projectId}"
            || ! is_string($uid)
            || $uid === ''
            || strlen($uid) > 128
            || ! is_numeric($issuedAt)
            || (int) $issuedAt > $now + 60
            || ! is_numeric($authTime)
            || (int) $authTime > $now + 60
            || ! is_string($claims['email'] ?? null)
            || filter_var($claims['email'], FILTER_VALIDATE_EMAIL) === false
        ) {
            throw new AuthenticationException('Le jeton Firebase est invalide.');
        }

        return $claims;
    }

    /**
     * @return array<string, string>
     */
    private function fetchCertificates(): array
    {
        $certificates = Http::acceptJson()
            ->timeout(5)
            ->get(self::CERTIFICATES_URL)
            ->throw()
            ->json();

        if (! is_array($certificates)) {
            throw new \RuntimeException('Firebase public signing certificates could not be loaded.');
        }

        return $certificates;
    }
}
