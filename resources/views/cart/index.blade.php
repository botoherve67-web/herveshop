@extends('layouts.app')

@section('title', 'Mon panier — HerveShop')
@section('meta_description', 'Vérifiez vos articles et passez votre commande sur HerveShop.')

@section('content')
    @php
        $itemCount = collect($items)->sum('quantity');
    @endphp
    <style>
        .cart-page { display:grid; gap:18px; }
        .cart-hero { min-height:102px; display:flex; align-items:center; gap:18px; padding:20px 30px; overflow:hidden; position:relative; border-radius:9px; color:#fff; background:linear-gradient(105deg,#0755d8,#087df1 65%,#0b62d8); }
        .cart-hero:after { content:''; position:absolute; right:13%; bottom:-52px; width:190px; height:145px; border-radius:42% 42% 0 0; background:rgba(255,255,255,.1); transform:skewX(-14deg); box-shadow:50px 0 0 rgba(255,255,255,.06); }
        .cart-hero-icon { position:relative; z-index:1; display:grid; place-items:center; width:50px; height:50px; border-radius:50%; color:#0969ed; background:#fff; }
        .cart-hero h1 { position:relative; z-index:1; margin:0 0 2px; font-size:1.45rem; }.cart-hero p { position:relative; z-index:1; margin:0; color:#e5f1ff; font-size:.72rem; }
        .cart-layout { display:grid; grid-template-columns:minmax(0,1.75fr) minmax(280px,.95fr); gap:18px; align-items:start; }
        .cart-list, .cart-summary { border:1px solid #dfeaf6; border-radius:10px; background:#fff; box-shadow:0 8px 24px rgba(28,78,128,.05); }
        .cart-list-head { display:flex; align-items:center; justify-content:space-between; padding:15px 18px; border-bottom:1px solid #e6eef7; color:#23456f; font-size:.68rem; }
        .cart-select { display:flex; align-items:center; gap:9px; font-weight:700; }.cart-select input, .cart-check { width:16px; height:16px; margin:0; accent-color:#0969ed; }
        .cart-clear { display:inline-flex; align-items:center; gap:5px; color:#7188a7; font-size:.62rem; background:none; border:0; cursor:pointer; }
        .cart-item { display:grid; grid-template-columns:22px 88px minmax(0,1fr) 84px 90px 24px; gap:12px; align-items:center; padding:14px 18px; border-bottom:1px solid #e8eff6; }.cart-item:last-child { border-bottom:0; }
        .cart-product-image { display:grid; place-items:center; width:88px; height:78px; overflow:hidden; border-radius:9px; background:#f1f6fb; }.cart-product-image img { width:100%; height:100%; object-fit:contain; }.cart-product-image svg { color:#0969ed; }
        .cart-product-info { min-width:0; }.cart-product-info strong { display:block; overflow:hidden; color:#173f5f; font-size:.76rem; text-overflow:ellipsis; white-space:nowrap; }.cart-product-info small { display:block; margin-top:4px; color:#7188a7; font-size:.61rem; }.cart-stock { display:inline-flex; margin-top:7px; padding:3px 7px; border-radius:9px; color:#15965c; background:#e4f8ed; font-size:.55rem; font-weight:700; }.cart-stock.preorder { color:#b56b10; background:#fff1d9; }
        .cart-quantity { display:flex; align-items:center; justify-content:space-between; width:84px; height:30px; border:1px solid #d6e5f5; border-radius:8px; color:#173f5f; }.cart-quantity button { width:27px; height:28px; border:0; color:#0969ed; background:transparent; cursor:pointer; font-size:1rem; }.cart-quantity span { font-size:.7rem; font-weight:700; }.cart-price { color:#173f5f; font-size:.7rem; font-weight:800; white-space:nowrap; }.cart-remove { display:grid; place-items:center; width:24px; height:24px; border:0; color:#ef5261; background:transparent; cursor:pointer; }
        .cart-continue { display:inline-flex; align-items:center; gap:7px; margin:12px 18px 16px; color:#0969ed; font-size:.65rem; font-weight:700; }.cart-summary { padding:18px 20px; }.cart-summary h2 { display:flex; align-items:center; gap:8px; margin:0 0 20px; color:#173f5f; font-size:.92rem; }.cart-summary h2 svg { color:#0969ed; }.cart-summary-row { display:flex; justify-content:space-between; gap:12px; padding:9px 0; color:#526d8e; font-size:.72rem; }.cart-summary-row strong { color:#173f5f; }.cart-summary-row.total { margin-top:7px; padding-top:15px; border-top:1px solid #dfeaf6; color:#173f5f; font-size:.95rem; font-weight:800; }.cart-summary-row.total strong { color:#0969ed; font-size:1.12rem; }.cart-free { display:flex; align-items:center; gap:10px; margin:12px 0 18px; padding:13px; border-radius:9px; color:#0969ed; background:#eaf4ff; }.cart-free svg { flex:0 0 auto; }.cart-free strong,.cart-free small { display:block; }.cart-free strong { font-size:.65rem; }.cart-free small { margin-top:2px; color:#6681a4; font-size:.57rem; }.cart-checkout { display:flex; align-items:center; justify-content:center; gap:7px; width:100%; padding:12px; border-radius:8px; color:#fff; background:#0969ed; font-size:.72rem; font-weight:800; }.cart-checkout:hover { background:#062b52; }.cart-back { display:flex; align-items:center; justify-content:center; gap:6px; min-height:38px; margin-top:10px; border:1px solid #0969ed; border-radius:8px; color:#0969ed; font-size:.67rem; font-weight:700; }.cart-secure { display:flex; align-items:center; gap:10px; margin-top:20px; padding-top:15px; border-top:1px solid #e5edf6; }.cart-secure svg { color:#0969ed; }.cart-secure strong,.cart-secure small { display:block; }.cart-secure strong { color:#25466f; font-size:.62rem; }.cart-secure small { color:#8aa0bb; font-size:.55rem; }
        .cart-benefits { display:grid; grid-template-columns:repeat(4,1fr); gap:0; padding:13px 18px; border-radius:9px; background:#eff7ff; }.cart-benefit { display:flex; align-items:center; justify-content:center; gap:9px; padding:5px 14px; border-right:1px solid #d9e8f8; color:#0969ed; }.cart-benefit:last-child { border-right:0; }.cart-benefit strong,.cart-benefit small { display:block; }.cart-benefit strong { color:#25466f; font-size:.63rem; }.cart-benefit small { color:#7890ad; font-size:.55rem; }
        .cart-empty { padding:48px 20px; text-align:center; }.cart-empty-icon { display:grid; place-items:center; width:62px; height:62px; margin:0 auto 12px; border-radius:50%; color:#0969ed; background:#eaf4ff; }.cart-empty h2 { margin:0 0 6px; color:#173f5f; font-size:1rem; }.cart-empty p { margin:0 0 18px; color:#7188a7; font-size:.72rem; }.cart-empty a { display:inline-flex; padding:10px 16px; border-radius:20px; color:#fff; background:#0969ed; font-size:.68rem; font-weight:700; }
        @media(max-width:800px){.cart-layout{grid-template-columns:1fr}.cart-summary{order:-1}.cart-item{grid-template-columns:22px 72px minmax(0,1fr) 24px;gap:9px;padding:12px}.cart-product-image{width:72px;height:68px}.cart-quantity{grid-column:3;width:76px}.cart-price{grid-column:3;grid-row:2}.cart-remove{grid-column:4;grid-row:1}.cart-benefits{grid-template-columns:1fr 1fr;gap:8px}.cart-benefit{justify-content:flex-start;border-right:0}.cart-hero{padding:18px 20px}}
        @media(max-width:480px){.cart-hero h1{font-size:1.15rem}.cart-hero p{font-size:.62rem}.cart-hero-icon{width:42px;height:42px}.cart-list-head{padding:12px}.cart-item{grid-template-columns:18px 62px minmax(0,1fr) 22px}.cart-product-image{width:62px;height:62px}.cart-product-info strong{font-size:.68rem}.cart-benefits{grid-template-columns:1fr}.cart-benefit{padding:5px 0}}
    </style>

    <div class="cart-page">
        <section class="cart-hero"><span class="cart-hero-icon"><x-icon name="cart" size="28"/></span><div><h1>Mon panier</h1><p>Vérifiez vos articles avant de passer commande</p></div></section>

        @if(count($items))
            <div class="cart-layout">
                <section class="cart-list" aria-label="Articles du panier">
                    <div class="cart-list-head"><label class="cart-select"><input id="cart-select-all" type="checkbox" checked> Tout sélectionner ({{ $itemCount }})</label><button class="cart-clear" type="button" onclick="document.querySelectorAll('.cart-check').forEach((checkbox) => checkbox.checked = false); document.getElementById('cart-select-all').checked = false;"><x-icon name="trash" size="14"/> Supprimer la sélection</button></div>
                    @foreach($items as $item)
                        @php $product = $item['product']; $image = $product->images->first(); @endphp
                        <div class="cart-item">
                            <input class="cart-check" type="checkbox" checked aria-label="Sélectionner {{ $product->name }}">
                            <span class="cart-product-image">@if($image)<img src="{{ asset('storage/'.$image->path) }}" alt="{{ $product->name }}">@else<x-icon name="box" size="26"/>@endif</span>
                            <div class="cart-product-info"><strong>{{ $product->name }}</strong><small>{{ $product->category?->name ?: 'Produit HerveShop' }}</small><span class="cart-stock {{ $product->estEnPrecommande() ? 'preorder' : '' }}">{{ $product->estEnPrecommande() ? 'Précommande' : 'En stock' }}</span></div>
                            <form class="cart-quantity" action="{{ route('cart.update', $product) }}" method="POST">@csrf @method('PATCH')<button type="submit" name="quantity" value="{{ max(0, $item['quantity'] - 1) }}" aria-label="Diminuer la quantité">−</button><span>{{ $item['quantity'] }}</span><button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" aria-label="Augmenter la quantité">+</button></form>
                            <strong class="cart-price">{{ number_format($item['lineTotal'], 0, ',', ' ') }} FCFA</strong>
                            <form action="{{ route('cart.remove', $product) }}" method="POST">@csrf @method('DELETE')<button class="cart-remove" type="submit" aria-label="Retirer {{ $product->name }}"><x-icon name="trash" size="16"/></button></form>
                        </div>
                    @endforeach
                    <a class="cart-continue" href="{{ route('products.index') }}"><x-icon name="arrow-left" size="13"/> Continuer mes achats</a>
                </section>
 
                <aside class="cart-summary">
                    <h2><x-icon name="tag" size="20"/> Récapitulatif de la commande</h2>
                    <div class="cart-summary-row"><span>Sous-total ({{ $itemCount }} article{{ $itemCount > 1 ? 's' : '' }})</span><strong>{{ number_format($total, 0, ',', ' ') }} FCFA</strong></div>
                    <div class="cart-summary-row"><span>Frais de livraison</span><strong style="color:#12a968">Gratuit</strong></div>
                    <div class="cart-summary-row total"><span>Total</span><strong>{{ number_format($total, 0, ',', ' ') }} FCFA</strong></div>
                    <div class="cart-free"><x-icon name="truck" size="22"/><div><strong>Livraison rapide</strong><small>Partout au Togo et en Afrique de l'Ouest</small></div><span style="margin-left:auto">→</span></div>
                    <a class="cart-checkout" href="{{ route('checkout.index') }}"><x-icon name="lock" size="14"/> Passer la commande →</a>
                    <a class="cart-back" href="{{ route('products.index') }}"><x-icon name="arrow-left" size="13"/> Continuer mes achats</a>
                    <div class="cart-secure"><x-icon name="shield" size="24"/><div><strong>Paiement sécurisé</strong><small>Vos informations sont protégées</small></div></div>
                </aside>
            </div>
    @else
            <section class="cart-list cart-empty"><span class="cart-empty-icon"><x-icon name="cart" size="30"/></span><h2>Votre panier est vide</h2><p>Découvrez nos produits et ajoutez vos favoris à votre panier.</p><a href="{{ route('products.index') }}">Découvrir la boutique</a></section>
    @endif
 
    <section class="cart-benefits"><div class="cart-benefit"><x-icon name="truck" size="25"/><div><strong>Livraison rapide</strong><small>Partout au Togo</small></div></div><div class="cart-benefit"><x-icon name="shield" size="25"/><div><strong>Paiement sécurisé</strong><small>Mobile Money & autres</small></div></div><div class="cart-benefit"><x-icon name="headset" size="25"/><div><strong>Service client</strong><small>Disponible 7j/7</small></div></div><div class="cart-benefit"><x-icon name="star" size="25"/><div><strong>Produits de qualité</strong><small>Garantie et satisfaction</small></div></div></section>
    </div>
    <script>
    document.getElementById('cart-select-all')?.addEventListener('change', (event) => document.querySelectorAll('.cart-check').forEach((checkbox) => checkbox.checked = event.target.checked));
    </script>
@endsection
