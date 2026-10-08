@extends('layouts.app')

@section('title', 'Comparer les produits — HerveShop')
@section('meta_description', 'Comparez les produits HerveShop selon leur prix, disponibilité, catégorie et mode de commande.')
@section('meta_robots', 'noindex,follow')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:22px;">
        <div>
            <p style="color:var(--vert-fonce); font-weight:700; margin:0;">CHOISIR ENSEMBLE</p>
            <h1 style="margin:4px 0 0;">Comparer les produits</h1>
        </div>
        @if($products->isNotEmpty())
            <form action="{{ route('compare.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn outline">Vider la comparaison</button>
            </form>
        @endif
    </div>

    @if($products->isEmpty())
        <div class="card" style="text-align:center; padding:36px 20px;">
            <h2>Votre comparaison est vide</h2>
            <p>Sélectionnez jusqu’à 4 produits depuis le catalogue pour les comparer.</p>
            <a href="{{ route('products.index') }}" class="btn">Voir le catalogue</a>
        </div>
    @else
        <div style="overflow-x:auto; border:1px solid var(--bordure); border-radius:8px; background:var(--blanc); box-shadow:var(--ombre);">
            <table style="width:100%; min-width:620px; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="width:150px; padding:14px; text-align:left; background:var(--vert-fonce); color:white;">Critère</th>
                        @foreach($products as $product)
                            <th style="min-width:180px; padding:14px; text-align:left; background:var(--vert-fonce); color:white; vertical-align:top;">
                                <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
                                <form action="{{ route('compare.destroy', $product) }}" method="POST" style="margin-top:8px;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn outline" style="padding:5px 8px; min-height:0; font-size:.8rem;">Retirer</button>
                                </form>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="padding:12px; text-align:left;">Image</th>
                        @foreach($products as $product)
                            <td style="padding:12px; border-top:1px solid var(--bordure);"><a href="{{ route('products.show', $product->slug) }}">@if($product->images->first())<img src="{{ $product->images->first()->url() }}" alt="{{ $product->imageAltText() }}" loading="lazy" style="width:100%; height:130px; object-fit:cover; border-radius:6px;">@else<span>Pas d’image</span>@endif</a></td>
                        @endforeach
                    </tr>
                    @foreach([
                        'Prix' => fn ($product) => number_format($product->price, 0, ',', ' ').' FCFA',
                        'Catégorie' => fn ($product) => $product->category?->name ?? 'Non classé',
                        'Disponibilité' => fn ($product) => $product->estEnPrecommande() ? 'Précommande' : ($product->stock > 0 ? $product->stock.' en stock' : 'Rupture'),
                    ] as $label => $value)
                        <tr>
                            <th style="padding:12px; text-align:left; border-top:1px solid var(--bordure);">{{ $label }}</th>
                            @foreach($products as $product)<td style="padding:12px; border-top:1px solid var(--bordure);">{{ $value($product) }}</td>@endforeach
                        </tr>
                    @endforeach
                    <tr>
                        <th style="padding:12px; text-align:left; border-top:1px solid var(--bordure);">Action</th>
                        @foreach($products as $product)
                            <td style="padding:12px; border-top:1px solid var(--bordure);"><form action="{{ route('cart.add', $product) }}" method="POST">@csrf<input type="hidden" name="quantity" value="1"><button type="submit" class="btn">Ajouter au panier</button></form></td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
@endsection