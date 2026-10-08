@extends('admin.layout')

@section('title', $product->exists ? 'Modifier produit' : 'Nouveau produit')

@section('admin-content')
    <h1>{{ $product->exists ? 'Modifier' : 'Nouveau' }} produit</h1>

    @php($selectedType = old('type', $product->type ?: 'stock'))

    <form id="product-form" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
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
        <small>Décrivez les caractéristiques exactes, l’usage, les dimensions et le contenu du colis, si ces informations sont connues.</small>

        <details style="margin:14px 0;">
            <summary>Référencement Google (facultatif)</summary>
            <label for="seo_title">Titre SEO (70 caractères maximum)</label>
            <input id="seo_title" type="text" name="seo_title" maxlength="70" value="{{ old('seo_title', $product->seo_title) }}" placeholder="{{ $product->name ?: 'Nom précis du produit' }} — HerveShop">
            <small>Laissez vide pour générer le titre à partir du nom du produit.</small>
            <label for="seo_description">Description pour les résultats Google (320 caractères maximum)</label>
            <textarea id="seo_description" name="seo_description" rows="3" maxlength="320" placeholder="Résumé factuel et unique du produit, avec son principal avantage.">{{ old('seo_description', $product->seo_description) }}</textarea>
            <small>Laissez vide pour reprendre la description du produit. Évitez les listes de mots-clés et les promesses non vérifiées.</small>
        </details>

        <label>Prix (FCFA)</label>
        <input type="number" name="price" value="{{ old('price', $product->price) }}" required>

        <label>Mode de vente</label>
        <select name="type" id="type" onchange="toggleProductMode()" required>
            <option value="stock" @selected($selectedType === 'stock')>Stock disponible</option>
            <option value="precommande" @selected($selectedType === 'precommande')>Précommande</option>
        </select>

        <div id="champs_stock">
            <label>Quantité disponible</label>
            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="1" data-stock-field>
            <small>Quantité vendable immédiatement.</small>
        </div>

        <div id="champs_precommande">
            <label>Acompte (%)</label>
            <input type="number" name="acompte_pourcent" value="{{ old('acompte_pourcent', $product->acompte_pourcent ?? 70) }}" min="0" max="100" data-precommande-field>

            <label>Date de clôture des précommandes</label>
            <input type="date" name="date_cloture_precommande" value="{{ old('date_cloture_precommande', $product->date_cloture_precommande?->format('Y-m-d')) }}" data-precommande-field>

            <label>Date d'expédition prévue</label>
            <input type="date" name="date_expedition_prevue" value="{{ old('date_expedition_prevue', $product->date_expedition_prevue?->format('Y-m-d')) }}" data-precommande-field>

            <label>Date d'arrivage estimée</label>
            <input type="date" name="date_arrivage_estimee" value="{{ old('date_arrivage_estimee', $product->date_arrivage_estimee?->format('Y-m-d')) }}" data-precommande-field>
        </div>

        <label style="font-weight:normal;">
            <input id="bascule_auto_precommande" type="checkbox" name="bascule_auto_precommande" value="1" style="width:auto;"
                @checked(old('bascule_auto_precommande', $product->exists ? $product->bascule_auto_precommande : false))>
            Bascule automatique en précommande quand stock épuisé
        </label>

        <label style="font-weight:normal;">
            <input type="checkbox" name="is_active" value="1" style="width:auto;"
                @checked(old('is_active', $product->is_active ?? true))>
            Produit actif (visible sur le site)
        </label>

        <label style="font-weight:normal;">
            <input type="checkbox" name="is_featured" value="1" style="width:auto;"
                @checked(old('is_featured', $product->is_featured ?? false))>
            Mettre en avant sur l'accueil
        </label>

        <label>Photos du produit (plusieurs possibles)</label>
        <input type="file" name="images[]" multiple accept="image/*">
        <small>Utilisez des photos nettes, fidèles au produit, bien éclairées et cadrées. Choisissez une image principale représentative; évitez les visuels trompeurs et le texte ajouté sur l’image.</small>

        <button type="submit" class="btn">Enregistrer</button>
    </form>

        @if($product->exists && $product->images->isNotEmpty())
            <h3>Galerie actuelle</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px; margin-bottom:16px;">
                @foreach($product->images as $image)
                    <div class="card" style="padding:8px;">
                        <img src="{{ $image->url() }}" alt="{{ $product->imageAltText() }}" style="width:100%; height:120px; object-fit:cover; border-radius:6px;">
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

    <script>
        function toggleProductMode() {
            const isPrecommande = document.getElementById('type').value === 'precommande';
            document.getElementById('champs_stock').hidden = isPrecommande;
            document.getElementById('champs_precommande').hidden = !isPrecommande;
            document.querySelectorAll('[data-stock-field]').forEach((field) => {
                field.disabled = isPrecommande;
            });
            document.querySelectorAll('[data-precommande-field]').forEach((field) => {
                field.disabled = !isPrecommande;
            });

            const autoSwitch = document.getElementById('bascule_auto_precommande');
            autoSwitch.disabled = isPrecommande;
            if (isPrecommande) autoSwitch.checked = false;
        }

        document.addEventListener('DOMContentLoaded', toggleProductMode);
        document.getElementById('type').addEventListener('change', toggleProductMode);

        function togglePrecommande() {
            // Champs toujours visibles : utile même en stock si bascule auto active
        }
    </script>
@endsection
