@extends('layouts.app')

@section('title', 'Mes listes — HerveShop')
@section('meta_description', 'Retrouvez vos favoris et les produits HerveShop que vous souhaitez acheter plus tard.')
@section('meta_robots', 'noindex,follow')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:24px;">
        <div>
            <p style="color:var(--vert-fonce); font-weight:700; margin:0;">MON COMPTE</p>
            <h1 style="margin:4px 0 0;">Mes listes</h1>
        </div>
        <a href="{{ route('products.index') }}" class="btn outline">Continuer mes achats</a>
    </div>

    <section style="margin-bottom:36px;">
        <h2>Mes favoris</h2>
        <div class="grid">
            @forelse($favorites as $item)
                <div class="wishlist-item">
                    <x-product-card :product="$item->product" />
                    <div style="display:flex; gap:8px; margin-top:10px;">
                        <form action="{{ route('wishlist.update', $item) }}" method="POST" style="flex:1;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="list_type" value="later">
                            <button type="submit" class="btn outline" style="width:100%;">Acheter plus tard</button>
                        </form>
                        <form action="{{ route('wishlist.destroy', $item) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn outline" aria-label="Retirer des favoris" title="Retirer des favoris">×</button>
                        </form>
                    </div>
                </div>
            @empty
                <p>Aucun favori pour le moment.</p>
            @endforelse
        </div>
    </section>

    <section>
        <h2>À acheter plus tard</h2>
        <div class="grid">
            @forelse($later as $item)
                <div class="wishlist-item">
                    <x-product-card :product="$item->product" />
                    <div style="display:flex; gap:8px; margin-top:10px;">
                        <form action="{{ route('wishlist.update', $item) }}" method="POST" style="flex:1;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="list_type" value="favorite">
                            <button type="submit" class="btn outline" style="width:100%;">Ajouter aux favoris</button>
                        </form>
                        <form action="{{ route('wishlist.destroy', $item) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn outline" aria-label="Retirer de la liste" title="Retirer de la liste">×</button>
                        </form>
                    </div>
                </div>
            @empty
                <p>Aucun produit enregistré pour plus tard.</p>
            @endforelse
        </div>
    </section>
@endsection