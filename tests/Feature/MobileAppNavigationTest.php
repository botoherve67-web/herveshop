<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileAppNavigationTest extends TestCase
{
    public function test_storefront_layout_renders_accessible_mobile_app_navigation(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<nav class="mobile-app-nav" aria-label="Navigation principale">', false)
            ->assertSee('href="' . route('home') . '"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee(route('products.index'), false)
            ->assertSee(route('categories.index'), false)
            ->assertSee(route('login'), false)
            ->assertSee(route('cart.index'), false)
            ->assertSee('<span>Accueil</span>', false)
            ->assertSee('<span>Boutique</span>', false)
            ->assertSee('<span>Catégories</span>', false)
            ->assertSee('<span>Compte</span>', false)
            ->assertSee('<span>Panier</span>', false);
    }
}
