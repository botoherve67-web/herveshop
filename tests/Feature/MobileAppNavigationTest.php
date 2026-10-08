<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class MobileAppNavigationTest extends TestCase
{
    public function test_storefront_layout_renders_accessible_mobile_app_navigation(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<nav class="mobile-app-nav" aria-label="Navigation principale">', false)
            ->assertSee('<summary>Aide et informations</summary>', false)
            ->assertSee(route('delivery'), false)
            ->assertSee(route('privacy'), false)
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

    public function test_authenticated_customer_keeps_account_access_in_bottom_navigation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('href="' . route('account.dashboard') . '"', false)
            ->assertSee('<span>Compte</span>', false);
    }

    public function test_admin_pages_use_role_aware_mobile_app_navigation(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Navigation administration"', false)
            ->assertSee('<span>Produits</span>', false)
            ->assertSee('<span>Commandes</span>', false)
            ->assertSee('<span>Clients</span>', false)
            ->assertSee('<span>Boutique</span>', false);
    }

    public function test_catalog_exposes_collapsible_filters_without_removing_filter_controls(): void
    {
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('<details class="shop-filter-details">', false)
            ->assertSee('Prix et disponibilité')
            ->assertSee('name="prix_min"', false)
            ->assertSee('name="prix_max"', false)
            ->assertSee('name="disponibilite"', false)
            ->assertSee('Appliquer les filtres');
    }
}
