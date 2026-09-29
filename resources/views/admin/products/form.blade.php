@extends('admin.layout')

@section('title', $product->exists ? 'Modifier produit' : 'Nouveau produit')

@section('admin-content')
    <h1>{{ $product->exists ? 'Modifier' : 'Nouveau' }} produit</h1>

    <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          method="POST" enctype="multipart/form-data" class="card">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <label>Nom</label>
        <input type="text" name="name" value="{{ old('name', $product->name) }}" required>

        <label>Catégorie</label>
        @if($categories->isEmpty())
            <div class="alert-error">
                Aucune catégorie n'est disponible.
                <a href="{{ route('admin.categories.index') }}" style="text-decoration:underline;">Créer une catégorie</a>
            </div>
        @else
            <select name="category_id" required>
                <option value="">Choisir une catégorie</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        @endif

        <label>Description</label>
        <textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea>

        <label>Prix (FCFA)</label>
        <input type="number" name="price" value="{{ old('price', $product->price) }}" required>

        <label>Stock</label>
        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required>

        <label>Type</label>
        <select name="type" id="type" onchange="togglePrecommande()">
            <option value="stock" @selected(old('type', $product->type) === 'stock')>Stock</option>
            <option value="precommande" @selected(old('type', $product->type) === 'precommande')>Précommande</option>
        </select>

        <div id="champs_precommande">
            <label>Acompte (%)</label>
            <input type="number" name="acompte_pourcent" value="{{ old('acompte_pourcent', $product->acompte_pourcent ?? 70) }}">

            <label>Date de clôture des précommandes</label>
            <input type="date" name="date_cloture_precommande" value="{{ old('date_cloture_precommande', $product->date_cloture_precommande?->format('Y-m-d')) }}">

            <label>Date d'expédition prévue</label>
            <input type="date" name="date_expedition_prevue" value="{{ old('date_expedition_prevue', $product->date_expedition_prevue?->format('Y-m-d')) }}">

            <label>Date d'arrivage estimée</label>
            <input type="date" name="date_arrivage_estimee" value="{{ old('date_arrivage_estimee', $product->date_arrivage_estimee?->format('Y-m-d')) }}">
        </div>

        <label style="font-weight:normal;">
            <input type="checkbox" name="bascule_auto_precommande" value="1" style="width:auto;"
                @checked(old('bascule_auto_precommande', $product->exists ? $product->bascule_auto_precommande : false))>
            Bascule automatique en précommande quand stock épuisé
        </label>

        <label style="font-weight:normal;">
            <input type="checkbox" name="is_active" value="1" style="width:auto;"
                @checked(old('is_active', $product->is_active ?? true))>
            Produit actif (visible sur le site)
        </label>

        <label>Images (plusieurs possibles)</label>
        <input type="file" name="images[]" multiple accept="image/*">

        @if($product->exists && $product->images->isNotEmpty())
            <h3>Galerie actuelle</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px; margin-bottom:16px;">
                @foreach($product->images as $image)
                    <div class="card" style="padding:8px;">
                        <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $product->name }}" style="width:100%; height:120px; object-fit:cover; border-radius:6px;">
                        @if($image->is_primary)
                            <strong style="display:block; margin:6px 0;">Image principale</strong>
                        @else
                            <form action="{{ route('admin.products.images.primary', [$product, $image]) }}" method="POST" style="margin-top:6px;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn outline" style="width:100%;">Choisir principale</button>
                            </form>
                        @endif
                        <form action="{{ route('admin.products.images.destroy', [$product, $image]) }}" method="POST" onsubmit="return confirm('Supprimer cette image ?');" style="margin-top:6px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn outline" style="width:100%;">Supprimer</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <button type="submit" class="btn">Enregistrer</button>
    </form>

    <script>
        function togglePrecommande() {
            // Champs toujours visibles : utile même en stock si bascule auto active
        }
    </script>
@endsection
