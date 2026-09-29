@extends('admin.layout')

@section('title', 'Commandes — Admin')

@section('admin-content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <h1>Commandes</h1>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn">Exporter CSV</a>
    </div>

    <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; align-items:end;">
        <div><label>Recherche</label><input type="search" name="q" value="{{ request('q') }}" placeholder="Référence, nom, email"></div>
        <div><label>Statut commande</label><select name="statut">
            <option value="">Tous</option>
            @foreach(['en_attente','confirmee','en_preparation','expediee','livree','annulee'] as $s)
                <option value="{{ $s }}" @selected(request('statut') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select></div>
        <div><label>Statut paiement</label><select name="statut_paiement">
            <option value="">Tous</option>
            @foreach(['en_attente','acompte_paye','paye','echec'] as $s)
                <option value="{{ $s }}" @selected(request('statut_paiement') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select></div>
        <div><label>Type</label><select name="type">
            <option value="">Tous</option>
            <option value="stock" @selected(request('type') === 'stock')>Stock</option>
            <option value="precommande" @selected(request('type') === 'precommande')>Précommande</option>
        </select></div>
        <div><label>Du</label><input type="date" name="date_debut" value="{{ request('date_debut') }}"></div>
        <div><label>Au</label><input type="date" name="date_fin" value="{{ request('date_fin') }}"></div>
        <button type="submit" class="btn">Filtrer</button>
        <a href="{{ route('admin.orders.index') }}" class="btn outline">Réinitialiser</a>
    </form>

    @foreach($orders as $order)
        <a href="{{ route('admin.orders.show', $order) }}" class="card" style="display:block; margin-bottom:8px;">
            {{ $order->reference }} — {{ $order->user->name }} — {{ number_format($order->total, 0, ',', ' ') }} FCFA
            — {{ ucfirst(str_replace('_',' ',$order->statut)) }}
            @if($order->type === 'precommande')
                <span class="badge-precommande">Précommande</span>
            @endif
        </a>
    @endforeach

    {{ $orders->links() }}
@endsection
