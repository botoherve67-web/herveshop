<?php

namespace Tests\Feature;

use Tests\TestCase;

class InformationPagesTest extends TestCase
{
    public function test_trust_pages_are_public_and_linked_from_the_footer(): void
    {
        $pages = [
            'delivery' => ['Livraison et retrait', 'Lomé centre : 1 000 FCFA'],
            'returns' => ['Retours et remboursements', 'ne sont pas encore définis'],
            'privacy' => ['Confidentialité et données personnelles', 'durées de conservation'],
            'terms' => ['Conditions de vente', 'Elle est provisoire'],
        ];

        foreach ($pages as $route => [$heading, $content]) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($heading)
                ->assertSee($content);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('delivery'))
            ->assertSee(route('returns'))
            ->assertSee(route('privacy'))
            ->assertSee(route('terms'))
            ->assertSee(route('contact.index'));
    }
}
