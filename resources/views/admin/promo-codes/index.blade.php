@extends('admin.layout')

@section('title', 'Codes promo — Admin')

@section('admin-content')
    <h1>Codes promo</h1>

    <form action="{{ route('admin.promo-codes.store') }}" method="POST" class="card" style="max-width:400px; margin-bottom:20px;">
        @csrf
        <label>Code</label>
        <input type="text" name="code" required>
        <label>Type</label>
        <select name="type">
            <option value="pourcentage">Pourcentage</option>
            <option value="montant">Montant fixe (FCFA)</option>
        </select>
        <label>Valeur</label>
        <input type="number" name="valeur" required>
        <label>Utilisation max (optionnel)</label>
        <input type="number" name="usage_max">
        <label>Valable à partir du (optionnel)</label>
        <input type="date" name="commence_le">
        <label>Expire le (optionnel)</label>
        <input type="date" name="expire_le">
        <label>Catégories concernées (optionnel)</label>
        <select name="category_ids[]" multiple size="4">
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <small>Laisser vide pour appliquer le coupon à toutes les catégories.</small>
        <button type="submit" class="btn">Créer</button>
    </form>

    @foreach($promoCodes as $promo)
        <div class="card" style="display:flex; justify-content:space-between; margin-bottom:8px;">
            <span>
                <strong>{{ $promo->code }}</strong> —
                {{ $promo->type === 'pourcentage' ? $promo->valeur.'%' : number_format($promo->valeur,0,',',' ').' FCFA' }}
                — Utilisé {{ $promo->usage_actuel }}/{{ $promo->usage_max ?? '∞' }}
                <br><small>
                    Valable : {{ $promo->commence_le?->format('d/m/Y') ?? 'immédiate' }}
                    au {{ $promo->expire_le?->format('d/m/Y') ?? 'sans expiration' }}
                    — {{ $promo->categories->isEmpty() ? 'Toutes les catégories' : $promo->categories->pluck('name')->join(', ') }}
                </small>
            </span>
            <form action="{{ route('admin.promo-codes.destroy', $promo) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn outline">Supprimer</button>
            </form>
        </div>
    @endforeach
@endsection
