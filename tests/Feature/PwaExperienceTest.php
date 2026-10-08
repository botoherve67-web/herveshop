<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaExperienceTest extends TestCase
{
    public function test_manifest_has_installable_metadata_and_existing_icons(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertStringStartsWith($manifest['scope'], $manifest['start_url']);
        $this->assertContains('shopping', $manifest['categories']);

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_install_prompt_and_offline_help_are_available_to_visitors(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="pwa-install"', false)
            ->assertSee('aria-controls="pwa-install-help"', false)
            ->assertSee('<dialog id="pwa-install-help"', false)
            ->assertSee('dans Safari', false)
            ->assertSee('menu', false);

        $offlinePage = file_get_contents(public_path('offline.html'));
        $this->assertStringContainsString('Connexion interrompue', $offlinePage);
        $this->assertStringContainsString('jamais enregistrés dans le cache hors ligne', $offlinePage);
        $this->assertStringContainsString('Réessayer', $offlinePage);
    }

    public function test_service_worker_falls_back_offline_without_caching_sensitive_pages(): void
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));

        foreach (['admin', 'panier', 'commandes', 'mon-compte', 'mes-listes', 'comparer', 'auth', 'firebase'] as $path) {
            $this->assertStringContainsString($path, $serviceWorker);
        }

        $this->assertStringContainsString("request.mode === 'navigate'", $serviceWorker);
        $this->assertStringContainsString('fetch(request).catch(() => caches.match(OFFLINE_URL))', $serviceWorker);
        $this->assertStringContainsString("key.startsWith('hervershop-static-')", $serviceWorker);
        $this->assertStringContainsString("key.startsWith('hervershop-img-')", $serviceWorker);
        $this->assertStringNotContainsString(
            'keys.filter((key) => ![STATIC_CACHE, IMAGE_CACHE].includes(key))',
            $serviceWorker
        );
    }
}
