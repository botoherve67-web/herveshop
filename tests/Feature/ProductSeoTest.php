<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Tests\TestCase;

class ProductSeoTest extends TestCase
{
    public function test_product_page_renders_search_metadata_and_structured_product_data(): void
    {
        $category = Category::create(['name' => 'Téléphones', 'slug' => 'telephones']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Téléphone Exemple',
            'slug' => 'telephone-exemple',
            'description' => 'Téléphone neuf avec écran haute résolution.',
            'price' => 85000,
            'stock' => 3,
            'type' => 'stock',
            'is_active' => true,
        ]);
        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/telephone-exemple.jpg',
            'position' => 0,
            'is_primary' => true,
        ]);

        $response = $this->get(route('products.show', $product->slug))->assertOk();

        $response->assertSee('<title>Téléphone Exemple — HerveShop</title>', false)
            ->assertSee('name="description" content="Téléphone neuf avec écran haute résolution."', false)
            ->assertSee('property="og:type" content="product"', false)
            ->assertSee('alt="Téléphone Exemple — Téléphones"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('application/ld+json', false);

        preg_match_all(
            '/<script type="application\/ld\+json">\s*(.*?)\s*<\/script>/s',
            $response->getContent(),
            $matches
        );
        $schemas = array_map(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
        $productSchema = collect($schemas)->first(fn (array $schema) => ($schema['@type'] ?? null) === 'Product');

        $this->assertNotNull($productSchema);
        $this->assertSame('Téléphone Exemple', $productSchema['name']);
        $this->assertSame('XOF', $productSchema['offers']['priceCurrency']);
        $this->assertSame('85000', $productSchema['offers']['price']);
        $this->assertSame('https://schema.org/InStock', $productSchema['offers']['availability']);
        $this->assertNotEmpty($productSchema['image']);
        $this->assertSame('OnlineStore', collect($schemas)->firstWhere('@type', 'OnlineStore')['@type']);
    }

    public function test_product_seo_fields_are_used_and_limited_to_search_result_lengths(): void
    {
        $category = Category::create(['name' => 'Audio', 'slug' => 'audio']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Casque Exemple',
            'slug' => 'casque-exemple',
            'description' => 'Description de base.',
            'seo_title' => str_repeat('Titre ', 20),
            'seo_description' => 'Description SEO dédiée.',
            'price' => 15000,
            'stock' => 0,
            'type' => 'precommande',
            'is_active' => true,
        ]);

        $this->assertSame(70, mb_strlen($product->seoTitle()));
        $this->assertSame('Description SEO dédiée.', $product->seoDescription());
    }

    public function test_sitemap_lists_product_pages_and_their_images_for_search_engines(): void
    {
        $category = Category::create(['name' => 'Accessoires', 'slug' => 'accessoires']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sac Exemple',
            'slug' => 'sac-exemple',
            'description' => 'Sac de démonstration.',
            'price' => 12000,
            'stock' => 2,
            'type' => 'stock',
            'is_active' => true,
        ]);
        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/sac-exemple.jpg',
            'position' => 0,
            'is_primary' => true,
        ]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('products.show', $product->slug), false)
            ->assertSee(url('/storage/products/sac-exemple.jpg'), false)
            ->assertSee(route('categories.index'), false);
    }
}
