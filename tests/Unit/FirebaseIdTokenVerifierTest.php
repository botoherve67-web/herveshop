<?php

namespace Tests\Unit;

use App\Services\FirebaseIdTokenVerifier;
use Firebase\JWT\JWT;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FirebaseIdTokenVerifierTest extends TestCase
{
    private const CERTIFICATES_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    private const TEST_PUBLIC_KEY = <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAn2qhC7iYK7/72uJAKBdf
uCWyzNsJFwJSuCa434AOHoWEw/ZE5Uxo04zQOgGx9YLh8FxXi1sdVUerjrY0cGUV
cQHSe+l5Jr8EgbNi5QrgUYUo/lcOmGZO/GjoYGHgnV+rJDaaITL5fhR2T4K1mRkq
Z/63Og6iAOMqwpHaehjJ3XMpqLbb35dvhDcJi/XGB835FnxxbYTAwIi8MCuveHAX
+J8Fu8M9aWVPonaOlbykxvtGydSevje2XNaLZ3MUGujO5bM+cdH9hIFD6wOEVJrZ
GfqgVxaH2zOLcX4Mk+GdD3WMNJZCHogN33G62+h/cZBZwxfWYiPQevuaijsQ8NAr
UwIDAQAB
-----END PUBLIC KEY-----
PEM;

    private const TEST_PRIVATE_KEY = <<<'PEM'
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCfaqELuJgrv/va
4kAoF1+4JbLM2wkXAlK4JrjfgA4ehYTD9kTlTGjTjNA6AbH1guHwXFeLWx1VR6uO
tjRwZRVxAdJ76XkmvwSBs2LlCuBRhSj+Vw6YZk78aOhgYeCdX6skNpohMvl+FHZP
grWZGSpn/rc6DqIA4yrCkdp6GMndcymottvfl2+ENwmL9cYHzfkWfHFthMDAiLww
K694cBf4nwW7wz1pZU+ido6VvKTG+0bJ1J6+N7Zc1otncxQa6M7lsz5x0f2EgUPr
A4RUmtkZ+qBXFofbM4txfgyT4Z0PdYw0lkIeiA3fcbrb6H9xkFnDF9ZiI9B6+5qK
OxDw0CtTAgMBAAECggEAQi+pnG2wFB35yXc9DsghkBlqwj3AaOKoiFdfUz/d3NMv
e5LAKPlP3mEsxKCWi6mi98HHAQ87vv/qHO7OF8oIHGqWwqZ9C9ar3tOlIBYjawUf
r3VilGiJq6c8r2ODt6MUMY1P8a+xwSRquHk6v00g+5tX5E1V+otWYgDfVej7yqpR
VABJOv4/jb/9zTatxGn7sDd1ZLnHhnd94TlQotCE+9tZmN6Q8Xq6s4UwuCRrFPu1
bzPJNtnbY1+a+z1P4JmA6fPz1rsrIq7b+yIiz01ozqjHpF/mJc/eNCDBOZFEiTzQ
3h3Bf1eSqZsO524lZJvS8bzp/XvxheKh93csidWWIQKBgQDQCYPjj6xm3SZ3qcYV
U4uQqrzJjQ0DdpqaPqvqRrjHQoMGce/Nb/Gv2OI4Vrj3RRKDIH2gI3h5RMSIMZy3
QJgYzDTmk5rS7duv0kA+MV+yJYx3aVc5SU2MMJNen/6cJgIIVfeAZKdrthQl6TdW
7bnlGSr/TeFhr10YV0eayNCcYQKBgQDEK34YMK/V0FutYL1rdPTxNXl2eJ65zPZA
6MorTZ7C0/WWaPY1TEqsDIzNm79r/hDD5cS2Wcy0YvxFWnY4l+WxyBB8sh9iCx59
ciWd26/amYK6itJLAvNXxfZgKQymMzSn9NsdoveYRFLPHIC5npuFQBX3mIZG+vYP
gj1Z2v+EMwKBgDa2ie1LV/glqXxHNkVdl5MQlF4drpJ+muJ+IRCYUPh20abcSEkr
a9DnpXdTt4mwrNG3tdJsAb9DCr0W7zRy1I2RB0itAUcAL4rqLOMucRCVN4AgQERc
tvxruhZk1b2TcW1nzpQB5NY7KMlfsKI4G2/ZUqmaffAHAuDn83kN32+BAoGAGMc9
mCSeMS2uRsoPYwFU5xrQCszVj7Z57Fz7HFkjkoxfWu5LGxRV4kF7j4T6utNOns/o
9veEycwu/Tud7ywQkVIp8vY0zJeG9GV0punW3o/BWXqrcVogDpgstJy6wkt5fTWK
b8Xj7FHE+/Anukp0bnJX0/xqCZEtf2v9/9mPqM0CgYEAuaS+4nCv3ChXnWsXRXRy
PQFWE8iWOMM/gky34SZveBcgOUmnJz4YtQATIbvnBAw3Voil5/Ivsa6opYNuVjNW
0NmAo05kywJEmKfwp/Wd8ACFUKuhVleNcfTl/LAFohj6JeBPnfERPBGcpN47AyGp
XGJDWC6SGAVsRTU+KdfbdaY=
-----END PRIVATE KEY-----
PEM;

    public function test_it_accepts_a_valid_firebase_id_token(): void
    {
        $this->fakeSigningCertificate(self::TEST_PUBLIC_KEY);

        $claims = app(FirebaseIdTokenVerifier::class)->verify($this->makeToken());

        $this->assertSame('firebase-test-user', $claims['sub']);
        $this->assertTrue($claims['email_verified']);
    }

    public function test_it_rejects_a_token_for_a_different_firebase_project(): void
    {
        $this->fakeSigningCertificate(self::TEST_PUBLIC_KEY);

        try {
            app(FirebaseIdTokenVerifier::class)->verify($this->makeToken([
                'aud' => 'another-project',
                'iss' => 'https://securetoken.google.com/another-project',
            ]));
            $this->fail('The token should have been rejected.');
        } catch (AuthenticationException) {
            $this->assertTrue(true);
        }
    }

    private function fakeSigningCertificate(string $publicKey): void
    {
        config(['services.firebase.project_id' => 'elledji']);
        Cache::forget('firebase_auth_public_certificates');
        Http::fake([
            self::CERTIFICATES_URL => Http::response(['test-key-id' => $publicKey]),
        ]);
    }

    private function makeToken(array $overrides = []): string
    {
        $now = time();
        $claims = array_merge([
            'iss' => 'https://securetoken.google.com/elledji',
            'aud' => 'elledji',
            'sub' => 'firebase-test-user',
            'iat' => $now - 10,
            'auth_time' => $now - 10,
            'exp' => $now + 3600,
            'email' => 'firebase@example.com',
            'email_verified' => true,
        ], $overrides);

        return JWT::encode($claims, self::TEST_PRIVATE_KEY, 'RS256', 'test-key-id');
    }
}
