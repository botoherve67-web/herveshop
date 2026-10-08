@extends('layouts.app')

@section('title', 'Commande — HerveShop')

@section('content')
    <style>
        .checkout-page { display:grid; gap:12px; }
        .checkout-item { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:13px 15px; border:1px solid #e7edf5; border-radius:14px; background:#fff; color:#294765; font-size:.8rem; }
        .checkout-item strong { flex:0 0 auto; color:#0969ed; }
    </style>
    <div class="checkout-page">
    <h1>Finaliser la commande</h1>

    @foreach($items as $item)
        <div class="checkout-item">
            <span>{{ $item['product']->name }} × {{ $item['quantity'] }}</span>
            <strong>{{ number_format($item['lineTotal'], 0, ',', ' ') }} FCFA</strong>
        </div>
    @endforeach

    @if($estPrecommande)
        <div class="alert-success">
            Commande avec précommande : un acompte est demandé maintenant, le solde sous 48h après arrivage.
        </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST" class="card checkout-form" style="max-width:500px; margin-top:20px;">
        @csrf

        <label>Mode de livraison</label>
        <select name="mode_livraison" id="mode_livraison" onchange="toggleLivraison()">
            <option value="domicile">Livraison à domicile</option>
            <option value="point_retrait">Point de retrait</option>
        </select>

        <div id="champ_domicile">
            <label>Zone de livraison</label>
            <select name="zone_livraison">
                @foreach($zones as $zone => $frais)
                    <option value="{{ $zone }}">{{ ucfirst(str_replace('_', ' ', $zone)) }} — {{ number_format($frais, 0, ',', ' ') }} FCFA</option>
                @endforeach
            </select>
            <label>Adresse de livraison</label>
            <input type="text" name="adresse_livraison" placeholder="Adresse complète">
        </div>

        <div id="champ_retrait" style="display:none;">
            <label>Point de retrait</label>
            <input type="text" name="point_retrait" placeholder="Ex : Boutique Tsévié centre">
        </div>

        <label>Code promo (optionnel)</label>
        <input type="text" name="code_promo" placeholder="Code promo">

        <label>Moyen de paiement</label>
        <select name="moyen_paiement">
            <option value="flooz">Flooz (Moov Money)</option>
            <option value="tmoney">TMoney (Togocom)</option>
        </select>

        <div class="alert-success">
            Après validation, effectuez le paiement puis envoyez la capture et l'identifiant de transaction depuis le détail de votre commande.<br>
            Flooz : <strong>+228 96 29 20 39</strong> — TMoney : <strong>+228 93 84 33 57</strong>
        </div>

        <p style="text-align:right; font-size:1.2rem;"><strong>Total : {{ number_format($total, 0, ',', ' ') }} FCFA</strong></p>

        <button type="submit" class="btn" style="width:100%;">Confirmer la commande</button>
    </form>

    <script>
        function toggleLivraison() {
            const mode = document.getElementById('mode_livraison').value;
            document.getElementById('champ_domicile').style.display = mode === 'domicile' ? 'block' : 'none';
            document.getElementById('champ_retrait').style.display = mode === 'point_retrait' ? 'block' : 'none';
        }
    </script>
    </div>
@endsection
