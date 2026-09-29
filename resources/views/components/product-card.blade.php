@props(['product'])

<article class="card product-card">
    <a href="{{ route('products.show', $product->slug) }}">
        @if($product->images->first())
            <img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}" class="product-card-image" style="width:100%; height:150px; object-fit:cover; border-radius:6px;">
        @else
            <div style="width:100%; height:150px; background:#f0f0f0; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#999;">Pas d'image</div>
        @endif
        <h3 style="margin:10px 0 4px; font-size:1rem;">{{ $product->name }}</h3>
    </a>
    <p class="prix">{{ number_format($product->price, 0, ',', ' ') }} FCFA</p>
    @if($product->estEnPrecommande())
        <span class="badge-precommande">Précommande</span>
    @elseif($product->stock > 0)
        <span class="badge-stock">En stock</span>
    @else
        <span class="badge-rupture">Rupture</span>
    @endif
    @php($isCompared = in_array($product->id, session('compare', [])))
    <form action="{{ $isCompared ? route('compare.destroy', $product) : route('compare.store', $product) }}" method="POST" style="margin-top:8px;">
        @csrf
        @if($isCompared) @method('DELETE') @endif
        <button type="submit" class="btn outline" style="width:100%;">{{ $isCompared ? 'Retirer de la comparaison' : 'Comparer' }}</button>
    </form>
    <form action="{{ route('cart.add', $product) }}" method="POST" style="margin-top:8px;">
        @csrf
        <input type="hidden" name="quantity" value="1">
        <button type="submit" class="btn" style="width:100%;">Ajouter au panier</button>
    </form>
</article>
