@extends('admin.layout')

@section('title', 'Administration — HerveShop')

@section('admin-content')
    <div class="admin-page-heading">
        <div><h1>Tableau de bord</h1><p>Vue rapide activité boutique et actions prioritaires.</p></div>
        <div class="admin-quick-actions">
            @if(Auth::user()->hasAdminRole('products'))<a class="btn" href="{{ route('admin.products.create') }}">Nouveau produit</a>@endif
            @if(Auth::user()->hasAdminRole('orders'))<a class="btn outline" href="{{ route('admin.orders.index') }}">Voir commandes</a>@endif
        </div>
    </div>

    <div class="grid" style="margin-bottom:24px;">
        <div class="card"><strong>Commandes en attente</strong><p style="font-size:1.6rem;">{{ $stats['commandes_en_attente'] }}</p></div>
        <div class="card"><strong>Produits stock bas</strong><p style="font-size:1.6rem;">{{ $stats['produits_stock_bas'] }}</p></div>
        <div class="card"><strong>Chiffre d'affaires</strong><p style="font-size:1.6rem;">{{ number_format($stats['ca_total'], 0, ',', ' ') }} FCFA</p></div>
        <div class="card"><strong>Commandes du jour</strong><p style="font-size:1.6rem;">{{ $stats['commandes_du_jour'] }}</p><small>{{ number_format($stats['ca_du_jour'], 0, ',', ' ') }} FCFA encaissés</small></div>
        <div class="card"><strong>Paiements en attente</strong><p style="font-size:1.6rem;">{{ $stats['paiements_en_attente'] }}</p><small>{{ number_format($stats['montant_paiements_en_attente'], 0, ',', ' ') }} FCFA</small></div>
        <div class="card"><strong>Précommandes actives</strong><p style="font-size:1.6rem;">{{ $stats['precommandes_actives'] }}</p></div>
        <div class="card"><strong>Clients inscrits</strong><p style="font-size:1.6rem;">{{ $stats['clients_inscrits'] }}</p></div>
        <div class="card"><strong>Preuves à vérifier</strong><p style="font-size:1.6rem;">{{ $stats['preuves_en_attente'] }}</p></div>
        <div class="card"><strong>Avis à modérer</strong><p style="font-size:1.6rem;">{{ $stats['avis_a_moderer'] }}</p></div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:24px; margin-bottom:24px;">
        <section>
            <h2>Meilleures ventes</h2>
            @forelse($meilleuresVentes as $vente)
                <div class="card" style="display:flex; justify-content:space-between; gap:16px; margin-bottom:8px;">
                    <span><strong>{{ $vente->product_name }}</strong><br><small>{{ number_format($vente->chiffre_affaires, 0, ',', ' ') }} FCFA</small></span>
                    <strong>{{ $vente->quantite_vendue }} vendu{{ $vente->quantite_vendue > 1 ? 's' : '' }}</strong>
                </div>
            @empty
                <div class="card">Aucune vente confirmée pour le moment.</div>
            @endforelse
        </section>

        <section>
            <h2>À traiter</h2>
            <a href="{{ route('admin.orders.index', ['statut' => 'en_attente']) }}" class="card" style="display:block; margin-bottom:8px;">{{ $stats['commandes_en_attente'] }} commande{{ $stats['commandes_en_attente'] > 1 ? 's' : '' }} en attente</a>
            <a href="{{ route('admin.orders.index') }}" class="card" style="display:block;">{{ $stats['preuves_en_attente'] }} preuve{{ $stats['preuves_en_attente'] > 1 ? 's' : '' }} à vérifier</a>
        </section>
    </div>

    <h2>Dernières commandes</h2>
    @foreach($dernieresCommandes as $order)
        <a href="{{ route('admin.orders.show', $order) }}" class="card" style="display:block; margin-bottom:8px;">
            {{ $order->reference }} — {{ $order->user->name }} — {{ number_format($order->total, 0, ',', ' ') }} FCFA
            — {{ ucfirst(str_replace('_', ' ', $order->statut)) }}
        </a>
    @endforeach
@endsection
