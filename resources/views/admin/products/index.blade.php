@extends('admin.layout')

@section('title', 'Produits — Admin')

@section('admin-content')
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Produits</h1>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('admin.products.index', ['stock_faible' => 1]) }}" class="btn outline">Stocks faibles</a>
            <a href="{{ route('admin.products.create') }}" class="btn">+ Nouveau produit</a>
        </div>
    </div>

    @foreach($products as $product)
        <div class="card" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div>
                <strong>{{ $product->name }}</strong> — {{ $product->category->name }}<br>
                {{ number_format($product->price, 0, ',', ' ') }} FCFA — Stock : {{ $product->stock }}
                @if($product->type === 'stock' && $product->stock <= 3)
                    <span class="alert-error" style="display:inline-block; padding:2px 6px;">Stock faible</span>
                @endif
                @if($product->estEnPrecommande())
                    <span class="badge-precommande">Précommande</span>
                @endif
            </div>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('admin.products.edit', $product) }}" class="btn outline">Modifier</a>
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Supprimer ce produit ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn outline">Supprimer</button>
                </form>
            </div>
        </div>
    @endforeach

    {{ $products->links() }}
@endsection
