@extends('layouts.app')

@section('title', 'Catalogue — HerveShop')
@section('meta_description', 'Parcourez le catalogue HerveShop : produits en stock, précommandes et offres disponibles en Afrique de l’Ouest.')
@section('canonical', route('products.index'))
@if(request()->query())
    @section('meta_robots', 'noindex,follow')
@endif

@section('content')
    <section class="shop-banner"><div><span class="shop-kicker">Boutique</span><h1>Découvrez notre sélection<br>de produits de qualité</h1><p>Des milliers d’articles, les meilleures marques et des prix imbattables !</p></div><div class="shop-banner-products">@forelse($featuredProducts as $product)<a href="{{ route('products.show', $product->slug) }}"><img src="{{ $product->images->first() ? asset('storage/'.$product->images->first()->path) : asset('images/logo.png') }}" alt="{{ $product->name }}"></a>@empty<span>Aucun produit disponible</span>@endforelse</div><div class="shop-banner-promises"><span><x-icon name="truck"/> Livraison rapide<br><small>partout au Togo</small></span><span><x-icon name="shield"/> Paiement sécurisé<br><small>Mobile Money & autres</small></span><span><x-icon name="headset"/> Service client<br><small>disponible</small></span></div></section>

    <div class="shop-layout">
        <aside class="shop-sidebar">
            <div class="shop-filter-title"><x-icon name="grid"/> <strong>Catégories</strong></div>
            <div class="shop-category-list">@foreach($categories as $category)<a href="{{ route('products.index', ['categorie' => $category->slug]) }}" class="{{ request('categorie') === $category->slug ? 'active' : '' }}"><span>{{ $category->name }}</span><small>{{ $category->products_count }}</small></a>@endforeach</div>
            <form method="GET" action="{{ route('products.index') }}">
                <input type="hidden" name="q" value="{{ request('q') }}"><input type="hidden" name="categorie" value="{{ request('categorie') }}"><input type="hidden" name="disponibilite" value="{{ request('disponibilite') }}">
                <div class="shop-filter-block"><strong>Prix</strong><div class="price-fields"><input type="number" name="prix_min" min="0" value="{{ request('prix_min') }}" placeholder="0 FCFA"><input type="number" name="prix_max" min="0" value="{{ request('prix_max') }}" placeholder="500 000 FCFA"></div></div>
                <div class="shop-filter-block"><strong>Disponibilité</strong><label><input type="radio" name="disponibilite" value="" @checked(!request('disponibilite'))> Toutes</label><label><input type="radio" name="disponibilite" value="stock" @checked(request('disponibilite') === 'stock')> En stock</label><label><input type="radio" name="disponibilite" value="precommande" @checked(request('disponibilite') === 'precommande')> Précommande</label></div>
                <button class="btn" type="submit">Appliquer les filtres</button><a class="shop-reset" href="{{ route('products.index') }}">Réinitialiser</a>
            </form>
        </aside>

        <section class="shop-results"><div class="shop-results-head"><div><h2>Tous les produits</h2><p>Découvrez notre large gamme de produits</p></div><form method="GET" action="{{ route('products.index') }}" class="shop-sort"><input type="hidden" name="q" value="{{ request('q') }}"><input type="hidden" name="categorie" value="{{ request('categorie') }}"><select name="tri" onchange="this.form.submit()"><option value="recent" @selected(request('tri', 'recent') === 'recent')>Trier par : Plus récents</option><option value="prix_asc" @selected(request('tri') === 'prix_asc')>Prix croissant</option><option value="prix_desc" @selected(request('tri') === 'prix_desc')>Prix décroissant</option></select></form></div><div class="shop-product-grid">@forelse($products as $product)<x-product-card :product="$product" />@empty<div class="card"><p>Aucun produit trouvé avec ces filtres.</p></div>@endforelse</div><div class="shop-pagination">{{ $products->links() }}</div></section>
    </div>
@endsection
